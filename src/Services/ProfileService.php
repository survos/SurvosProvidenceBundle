<?php

namespace Survos\Providence\Services;

use Survos\Providence\Entity\Obj;

//use Survos\Providence\Entity\Profile as EntityProfile;

use Survos\Providence\Model\Field;
use Survos\Providence\Model\StorageLocation;
use Survos\Providence\Model\CaTable;


use Survos\Providence\Model\Core;
use Survos\Providence\Model\FieldDisplayType;
use Survos\Providence\Model\SystemList;
use Survos\Providence\Model\FieldType;

use Survos\Providence\XmlModel\XmlLabelsInterface;
use Survos\Providence\XmlModel\XmlProfile;
use Survos\Providence\XmlModel\ProfileItem;
use Survos\Providence\XmlModel\ProfileItems;
use Survos\Providence\XmlModel\ProfileLabel;
use Survos\Providence\XmlModel\ProfileLabels;
use Survos\Providence\XmlModel\ProfileList;
use Survos\Providence\XmlModel\ProfileLists;
use Survos\Providence\Repository\ProfileRepository;
use Survos\Providence\XmlModel\ProfileMetaDataElement;
use Survos\Providence\XmlModel\ProfilePlacement;
use Survos\Providence\XmlModel\ProfileRelationshipTableType;
use Survos\Providence\XmlModel\ProfileScreen;
use Survos\Providence\XmlModel\ProfileUserInterface;
use Survos\Providence\XmlModel\ProfileUserInterfaces;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sabre\Xml\Reader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

// for class reflection

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use function Symfony\Component\String\u;

// copied from BaseModel.php
# ------------------------------------------------------------------------------------
# --- Field type constants
# ------------------------------------------------------------------------------------
define("FT_NUMBER", 0);
define("FT_TEXT", 1);
define("FT_TIMESTAMP", 2);
define("FT_DATETIME", 3);
define("FT_HISTORIC_DATETIME", 4);
define("FT_DATERANGE", 5);
define("FT_HISTORIC_DATERANGE", 6);
define("FT_BIT", 7);
define("FT_FILE", 8);
define("FT_MEDIA", 9);
define("FT_PASSWORD", 10);
define("FT_VARS", 11);
define("FT_TIMECODE", 12);
define("FT_DATE", 13);
define("FT_HISTORIC_DATE", 14);
define("FT_TIME", 15);
define("FT_TIMERANGE", 16);

# ------------------------------------------------------------------------------------
# --- Display type constants
# ------------------------------------------------------------------------------------
define("DT_SELECT", 0);
define("DT_LIST", 1);
define("DT_LIST_MULTIPLE", 2);
define("DT_CHECKBOXES", 3);
define("DT_RADIO_BUTTONS", 4);
define("DT_FIELD", 5);
define("DT_HIDDEN", 6);
define("DT_OMIT", 7);
define("DT_TEXT", 8);
define("DT_PASSWORD", 9);
define("DT_COLORPICKER", 10);
define("DT_TIMECODE", 12);
define("DT_COUNTRY_LIST", 13);
define("DT_STATEPROV_LIST", 14);
define("DT_LOOKUP", 15);
define("DT_FILE_BROWSER", 16);
define("DT_INTERVAL", 17);

# ------------------------------------------------------------------------------------
# --- Access mode constants
# ------------------------------------------------------------------------------------
define("ACCESS_READ", 0);
define("ACCESS_WRITE", 1);

# ------------------------------------------------------------------------------------
# --- Hierarchy type constants
# ------------------------------------------------------------------------------------
define("__CA_HIER_TYPE_SIMPLE_MONO__", 1);
define("__CA_HIER_TYPE_MULTI_MONO__", 2);
define("__CA_HIER_TYPE_ADHOC_MONO__", 3);
define("__CA_HIER_TYPE_MULTI_POLY__", 4);

# ------------------------------------------------------------------------------------
# --- Media icon constants
# ------------------------------------------------------------------------------------
define("__CA_MEDIA_QUEUED_ICON__", 'queued');

class ProfileService
{
    final public const ONE_TO_MANY = 99;

    final public const MANY_TO_ONE = 98;

    private ArrayCollection $coreTypes;

    private array $relationshipTables;

    final const CATEGORY_UI = 'ui';
    final const CATEGORY_OBJ = 'obj';
    final const CATEGORY_META = 'meta';
    final const CATEGORY_NON_OBJ = 'non_obj'; // relational?
    final const CATEGORY_SYSTEM = 'system';
    final const CATEGORIES = [self::CATEGORY_OBJ, self::CATEGORY_NON_OBJ, self::CATEGORY_UI, self::CATEGORY_META, self::CATEGORY_SYSTEM];

    final public const OBJ_TABLES = ['objects', 'object_representations', 'object_lots', 'representation_annotations', 'representation_transcriptions',
    ];
    final public const META_TABLES = [

        'metadata_elements',
        'metadata_type_restrictions',
        'metadata_dictionary_entries',
        'metadata_type_restrictions',
        'metadata_alert_rules',
        'attributes',
    ];
    final public const NON_OBJ_TABLES = [
        'attributes',
        'entities', 'org', 'person', 'lists',
        'places', 'occurrences', 'storage_locations',
        'entities',

//        'vocabulary_terms',

        'collections',
        'sets', 'set_items',
        'loans', 'movements',

        'list_items',

        'relationship_types', 'tours', 'tour_stops', 'metadata_elements',
        'representation_annotations',
//        'users',  // ??
    ];

    final public const SYSTEM_TABLES = [
        'acl',
        'lists',
        'list_items',
//        'users',
    ];

    final public const UI_TABLES = [

        'bundle_displays',
        'bundle_display_placements',
        'editor_ui_bundle_placements',
        'editor_uis',
        'editor_ui_screens',

        'notifications',
        'notification_subjects',

        'application_vars',
        'bookmarks',
        'bookmark_folders',

        'search_forms',
        'search_form_placements',

        'user_representation_annotations',


    ];

    private function getAllTables(): array
    {
        return array_merge(self::OBJ_TABLES, self::NON_OBJ_TABLES,
            self::SYSTEM_TABLES,
            self::UI_TABLES, self::META_TABLES);
    }

    public function __construct(
        private string                          $xmlDir,
        private string                          $confPath,
        private string                          $docPath,
        private string                          $fieldConfigPath,
        private bool                            $loadFromFiles,
        private bool                            $persist,
        private readonly NormalizerInterface    $normalizer,
        private readonly ValidatorInterface     $validator,
        private readonly ParameterBagInterface  $bag,
        private readonly LoggerInterface        $logger,
        private readonly Environment            $twig,
        private readonly EntityManagerInterface $entityManager)
    {

        $this->coreTypes = new ArrayCollection();

        $this->relationshipTables = [];
        return; // do we really need this?
        if ($this->loadFromFiles) {
            $this->setup(true);
        }
    }

    public function getConstants(): array
    {
        $map = [];
        $constants = get_defined_constants(true)['user'];
        foreach ($constants as $constant => $value) {
            if (preg_match('/(DT|FT)_(.*)/', (string)$constant, $m)) {
                [$all, $prefix, $name] = $m;
                $map[$prefix][$value] = $name;
            }
        }
        return $map;
    }

    public function setup($fromFiles = false)
    {
        $this->logger->warning("loading cores...");
        $this->coreTypes = $this->loadCoreTypes($fromFiles);
        $this->setupSystemLists();
        $this->addRelatedFields();

        /** @var Core $core */
        foreach ($this->coreTypes as $core) {
            $this->setFields($core);
            assert($core->getFields()->count(), "No fields for " . $core->getEntityName());
        }

//        $core = $this->getCore('Place');
//        // now that we have the system lists loaded, we should be able to generate the correct relationships.
//        $this->setFields($core);

        // now populate core with relationships
//        foreach ($relationships as $core_table => $relationship)
//        {
//            $plural[$core_table]->setRelationships($relationship);
//        }
//
        // AFTER loading all the cores, set the fields, including related fields./
    }

    /** @return Core[] */
    public function getCoreTypes()
    {
        if (!$this->coreTypes->count()) {
//            $this->coreTypes = $this->loadCoreTypes();
            $this->setup(fromFiles: $this->loadFromFiles);
            assert($this->getCore('Coll'));
        }
        return $this->coreTypes;
    }

    public function getCoreTypeCodes()
    {
        return $this->getCoreTypes()->map(
            fn(Core $core) => $core->getEntityName()
        )->toArray();
    }


    public function getCore($name, $throwError = true): ?Core
    {

        // hack, strip fqcl
        $name = str_replace('ca_', '', (string)$name);
        if (in_array($name, ['users'])) {
            return null;
        }
        $name = str_replace('App\\Entity\\', '', $name);
//        assert(in_array($name, $this->getCoreTypeCodes()), "Missing core $name " . join(',', $this->getCoreTypeCodes()));
        $core = $this->getCoreTypes()->filter(
            fn(Core $core) => in_array($name, [$core->getCaTable(), $core->getEntityName()])
        )->first();
        if ($throwError) {
            assert($core, $name);
        }
        return $core ?: null;
    }

    public function parseXml($input, $profileClassName = XmlProfile::class): XmlProfile
    {
        // <article xmlns="http://example.org/">
        if (empty($input)) {
            $input = <<<XML_WRAP
<?xml version="1.0" encoding="utf-8"?>
<profile xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="profile.xsd" useForConfiguration="1" base="base">
  <elementSets>
    <metadataElement code="nationality" datatype="List" list="nationality_types">
      <labels>
        <label locale="en_US">
          <name>Artist's Nationality</name>
          <description>Enter nationality of artist.</description>
        </label>
      </labels>
      <typeRestrictions>
        <restriction code="ca_entities">
          <table>ca_entities</table>
          <settings>
            <setting name="minAttributesPerRow" value="-1">-2</setting>
            <setting name="maxAttributesPerRow">1</setting>
            <setting name="minimumAttributeBundlesToDisplay">1</setting>
          </settings>
        </restriction>
      </typeRestrictions>
    </metadataElement>
    </elementSets>
</profile>
XML_WRAP;
        }

        // hack for _value in settings
        //                 <setting name="label" locale="en_US" _value="100 Main Entry--Personal Name">100 Main Entry--Personal Name</setting>
//        $input = preg_replace('|>([^<]+)</setting>|', " v=\"$1\"></setting>", $input);
//        $input = preg_replace('|>([^<]+)</setting>|', " v=\"$1\">$1</setting", $input);
        //dd($input);
        $service = new XmlParser();
        $service->namespaceMap['http://www.w3.org/2001/XMLSchema-instance'] = 'profile';
        $service->mapValueObject('{}profile', $profileClassName);
        /** @var XmlProfile $profile */
        $profile = $service->parse($input);
//        dd($profile->elementSets->metadataElement[0]->typeRestrictions->restriction[0]->settings->setting[0]);
        return $profile;

//        $service->elementMap['{}profile'] = function(Reader $reader) {
////            $o = $this->addAttributes(new Profile(), $reader);
//            $p =  \Sabre\Xml\Deserializer\valueObject($reader, Profile::class, '{}');
//
//            dd($p);
//            // Tell the reader we are done with this element
//            $reader->next();
//            return $o;
//        };
//        $service->mapValueObject('{}lists', ProfileLists::class);

//        $service->elementMap['{}list'] = function($reader) {
//            $o = new ProfileList();
//            $o = $this->addAttributes($o, $reader);
//            $oo =  \Sabre\Xml\Deserializer\valueObject($reader, get_class($o), '{}');
//            // Tell the reader we are done with this element
//            dd($o, $oo);
//            $reader->next();
//            return $o;
//        };

//        $service->mapValueObject('{}list', ProfileList::class);
//
//        $service->mapValueObject('{}items', ProfileItems::class);
//        $service->mapValueObject('{}item', ProfileItem::class);
//
//        $service->mapValueObject('{}userIntefaces', ProfileUserInterfaces::class);
//        $service->mapValueObject('{}userInteface', ProfileUserInterface::class);
//
//        $service->mapValueObject('{}labels', ProfileLabels::class);
//        $service->mapValueObject('{}label', ProfileLabel::class);
//
//        $service->mapValueObject('{}lists', ProfileLists::class);
//        $service->mapValueObject('{}list', ProfileList::class);
//
//        $profile = $service->parse($input);
//
//        return $profile;
//        foreach ($profile->lists->list as $list) {
//            dump($list->code);
//            dd($profile, $list, $list->items);
//        }
//        dd($profile, $profile->lists);
//
//
//        $service->elementMap = [
//            '{http://example.org/}profile' => 'Sabre\Xml\Element\KeyValue',
//        ];
    }

    public function addAttributes($o, Reader $reader)
    {
        foreach ($reader->parseAttributes() as $key => $value) {
            $o->$key = $value;
            if (isset($o->{$key})) {
                $o->$key = $value;
            }
        }
        return $o;
    }

    public function createEmbeddables(array $options = [])
    {
        $core = null;
        $entityName = null;
        $code = null;
        return; // no longer generated.
        $core = $this->getCore('NestedListItem');

        $options = (new OptionsResolver())
            ->setDefaults([
                'type' => null,
                'path' => false,
                'debug' => false
            ])->resolve($options);

        $fields = $core->getFields()->filter(fn(Field $field) => $field->getDbType()
            && !in_array($field->getName(), [''])
            && !in_array($field->getCaFieldType(), [self::ONE_TO_MANY, self::MANY_TO_ONE]));
        foreach ($fields as $field) {
            $field->setNullable(true);
        }
//        array_walk($fields, fn(Field $field) => $field->setNullable(true));
        // first, make the embeddable.
        $code = $this->twig->render(
            "php/EmbeddableTypeClass.php.twig",
            [
                'entityName' => $entityName = 'EmbeddableTypeClass',
//            'tableName' => $tableName,
//                'core' => $core, // ???
                'fields' => $fields,
                'ns' => self::class,
            ]
        );
        if ($path = $options['path']) {
            file_put_contents($fn = "$path/src/Entity/$entityName.php", $code);
//            dump($code);
        }
        return $code;
    }

    public function createIntrinsicTypes(SystemList $sysList, array $options = []): array
    {
        $options = (new OptionsResolver())
            ->setDefaults([
                'type' => null,
                'path' => false,
                'debug' => false
            ])->resolve($options);


        if ($options['path'] === true) {
            $options['path'] = $this->bag->get('ac_path');
        }


        $name = $sysList->getCode();
        if (preg_match('/status/', $name)) {
//            return [];
        }

        if (preg_match('/type$/', $name)) {
            assert(false);
            dd($sysList);
//            return [];
        }

        //        $core = $this->getCore('NestedListItem'); // we probably don't need this anymore!
        $entityName = $sysList->getEntityName();


        if (!$sysList->getUsedBy()) {
//            dd("not used by anything: " . $sysList->getEntityName());
//            return []   ; // @todo: handle statuses and sources
        }
//        assert($sysList->getUsedBy(), "Missing used by " . $entityName);

        $code = $this->twig->render(
            "php/ListItemEntity.php.twig",
            $renderVars = [
                'filename' => "/src/Entity/$entityName",
                'core' => (new Core())->setEntityName($entityName), // hack to get plural.
                'entityName' => $entityName,
                'systemList' => $sysList,
                'ns' => self::class,
                'usedBy' => $sysList->getUsedBy()
            ]
        );

        // get the related class.  What a mess.
        $map = $this->getMapping()['systemLists'][str_replace('ca_', '', $sysList->getCode())];
//        dd($map);
        // the name of this now needs to map to a new entity type, same fields as NestedListItems
        $repoFilename = "/src/Repository/${entityName}Repository.php";

        $repoCode = $this->twig->render("php/repo.php.twig", [
            'filename' => $repoFilename,
            'entityName' => $entityName]);

        $core = $sysList->getUsedBy();
//            assert($core, $entityName . " is not used by anything");

        if (!empty($core)) {
            $traitCode = $this->twig->render("php/trait.php.twig", $entityVars = [
                'core' => $core, // the used by
                'filename' => sprintf('/src/Traits/Project%s.php', $core->getEntityName()),
                'entityName' => $entityName,
                'privateVar' => u($entityName . 's')->camel() // type, source, label
//            // hack!
//            'core' => $core = (new Core())
//                ->setPlural($name)
//                ->setSingular($name)
//            ->setEntityName($entityName),
//            'entityName' => $entityName]
            ]);
//    dd($core, $sysList, $sysList->getUsedBy(), $traitCode);
        } else {
            $traitCode = null;
        }
        //
//        dd($traitCode);
        // Project.php needs the related fields.  For now.  Add it to a trait?


        if ($path = $options['path']) {
            if (!empty($traitCode)) file_put_contents($fn = sprintf($path . $entityVars['filename']), $traitCode);
            file_put_contents($fn = $path . $repoFilename, $repoCode);
        }
        $sysList
            ->setTraitCode($traitCode)
            ->setRepoCode($repoCode)
            ->setEntityCode($code);
        if ($core) {
            $this->extras($core);
        }


//        $this->twig->render("php/Entity.php.twig", [
//            'core' => $NestedListItem,
//        ])
        return [];
    }

    public function getSystemList($code): SystemList
    {
        return $this->getSystemLists()[$code];
    }

    /*
     * @return SystemList[]
     */
    public function getSystemLists(): array
    {
        static $systemLists = null;
        // since this is linked from ca2, which doesn't have this entity.
//        if (!class_exists('App\Entity\SystemList')) {
//            return $systemLists;
//        }
        if ($systemLists) {
            return $systemLists;
        }

        if (!file_exists($fn = "/tmp/vocab.html")) {
            try {
                $html = file_get_contents('https://docs.collectiveaccess.org/wiki/List_and_Vocabulary_Management');
                file_put_contents($fn, $html);
            } catch (\Exception) {
                // doh!  maybe just check this in?
                $html = '';
            }
        } else {
            $html = file_get_contents($fn);

        }

        $crawler = new Crawler($html);
        $table = $crawler->filter('table')->filter('tr')->each(fn($tr, $i) => $tr->filter('td')->each(fn($td, $i): string => trim((string)$td->text())));
        array_shift($table);
        $lists = [];
        foreach ($table as [$key, $description]) {
            $lists[$key] = $description;
        }
        // missing from vocabulary list
//        array_push($table, ['object_representation_types',"A value from the object_representation_types list indicating the type of the record. Stored as an internally generated numeric item_id. When setting this value in a data import or via an API call the item identifier may be used."]);
//        array_push($table, ['loan_types',"type of the loan. eg. incoming loan, outgoing loan, etc"]);
//        array_push($table, ['movement_types',"type of the movement, eg. administrative, exhibition, etc."]);
//        array_push($table, ['tour_types',"type of the tour, eg. indoor, outdoor, virtual"]);
//        array_push($table, ['tour_stop_types',"type of the tour stop, "]);
//        array_push($table, ['list_item_sources',"source of the list item, "]);
//        array_push($table, ['object_lot_sources',"source of the lot, "]);
//        array_push($table, ['storage_location_sources',"source of the lot, "]);
//        array_push($table, ['object_representation_sources',"source of the lot, "]);
//        array_push($table, ['loan_sources',"source of the lot, "]);
//        array_push($table, ['movement_sources',"source of the lot, "]);


        // while this is helpful for the descriptions, too often the lists are missing.  Better to get the lists from the models
        foreach ($this->getRawModelData() as $caTableName => $datum) {
            foreach ($datum['FIELDS'] as $field) {
                if (!empty($field['LIST_CODE'])) {
                    $key = $field['LIST_CODE'];
                    if (empty($lists[$key])) {
                        $lists[$key] = "Added because it's used by " . $caTableName;
                    }
                }
            }
        }
//        $lists['custom_list'] = "custom project lists ";
        $lists['custom_list_items'] = "custom project list items ";

//        dd(array_keys($lists));
        foreach (['list_item_labels', 'list_item_label_types'] as $requiredList) {
//            assert(array_key_exists($requiredList, $lists), "Missing $requiredList");
        }

//        assert(array_key_exists('list_item_label_types', $lists), "Missing list_item_label_types " . join(",", array_keys($lists)));

        foreach ($lists as $key => $description) {
//            [$key, $description] = $x;

            $list = (new SystemList())
                ->setCode($key)
                ->setDescription($description)
                ->setIsOneAndOnlyOne(preg_match('/and only one/', (string)$description))
                ->setIsTree(preg_match('/hierarchically/', (string)$description))
                ->setIsLabel(preg_match('/label/', $key));
            $name = $key;
            // hacks
            $name = str_replace('ies', 'y', $name);
            $name = str_replace('atuses', 'atus_code', $name);
            $name = preg_replace('/s$/', '', $name);

            $list->setEntityName(ucfirst((string)u($name)->camel()));

            $systemLists[$key] = $list;

            // break down key to figure out what it does.
            if (preg_match('/(.*?)_label_types$/', $key, $m)) {
                $core = $m[1];
            }
        }
        foreach (['list_item_labels', 'list_item_label_types'] as $requiredList) {
//            assert(array_key_exists($requiredList, $systemLists), "Missing $requiredList");
        }
        ksort($systemLists);
        return $systemLists;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getRelationshipFields(ArrayCollection $coreTypes): array
    {
        $rTable = [];
        foreach ($coreTypes as $core) {
            // create the superset for relationships
            foreach ($core->getRelationships() as $key => $r) {
                foreach ($r['FIELDS'] as $key => $field) {
                    $rTable[$key] = $field;
                }
            }
        }
        return $rTable;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getAggregateLabelFields(ArrayCollection $coreTypes): array
    {
        $rTable = [];
        /** @var Core $core */
        foreach ($coreTypes as $core) {
            // create the superset for relationships
            if (array_key_exists('labels', $core->getTableDefinition())) {
                foreach ($core->getTableDefinition()['labels']['FIELDS'] as $key => $r) {
                    $rTable[$key] = $r;
                }
            } else {
//                dump($core->getTableDefinition());
//                dd("No labels for " . $core->getCaTable());
            }
        }
        return $rTable;
    }

    public function getAggregateFields(ArrayCollection $coreTypes)
    {
        $rTable = [];
        return $rTable;
        /** @var Core $core */
        foreach ($coreTypes as $core) {
            if ($core->getHasIdno()) {
                dd($core->getTableDefinition());
                foreach ($core->getTableDefinition()['FIELDS'] as $key => $r) {
                    if (!array_key_exists($key, $rTable)) {
                        $rTable[$key] = 0;
                    }
                    $rTable[$key]++;
                }
            } else {
//                dd($core->getTableDefinition()['FIELDS']);
            }
        }
        return $rTable;
    }

    public function getRawModelData(): array
    {
        assert(file_exists($this->fieldConfigPath), $this->fieldConfigPath . " does not exist");
        return json_decode(str_replace('"ca_', '"', file_get_contents($this->fieldConfigPath)), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getModelDataByTable(string $caTable): array
    {
        return $this->getRawModelData()[$caTable];
    }

    public function createMapping(): array
    {
        $map = [];
        // instead of mapping, what we really need to do is properly and completely load core, save it and import as fixture in ac.

        // we can have some utilities that map the lists, maybe CAService if profiles only.
        $systemLists = $this->getSystemLists();
        $caTableData = $this->getCaBaseModelData();

        $coreTypes = $this->getCoreTypes();
        dd($coreTypes, $caTableData);
        $map = [];

        foreach ($coreTypes as $core) {
            $map['core'][$core->getEntityName()] = [
                'table' => $core->getCaTable()
            ]; // should be a helper class
        }

        /**
         * @var SystemList $systemList
         */
        foreach ($systemLists as $code => $systemList) {
            if ($code == 'collection_types') {
//                dd($systemList);
            }
            if ($usedBy = $systemList->getUsedBy()) {
                $map['core'][$usedBy->getEntityName()]['usedBy'] =
                    $usedBy->getEntityName();
            }

            $map['systemLists'][$code] =
                $systemList->getEntityName();
        }

        return $map;

//        /** @var Core $core */
//        foreach ($this->getCoreTypes() as $core) {
//            $map['ca_tables'][$core->getCaTable()] =
//                [
//                    'entityName' => $core->getEntityName(),
//                    'typesClass' => $core->getTypeClass(),
//                    'typesProperty' => $core->getProperty('types')
//                ];
//
//        }
        return $map;
    }

    public function getMapping(): array
    {
        return json_decode(file_get_contents($this->bag->get('ca_mapping_path')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getConf($code): array
    {
        $fn = $this->confPath . "/$code.conf";

        $new = [];
        assert(file_exists($fn), $fn);
        foreach (file($fn) as $line) {
            $line = trim((string)$line);
            if (empty($line)) {
                continue;
            }
//            $line = str_replace("[", '{', $line);
            $line = str_replace("\t", '', $line);
            if (preg_match('/^#/', $line)) {
                continue;
            }
            if (preg_match($pattern = '/^(\w+) +=(.*?)$/', $line, $m)) {
                [$all, $key, $value] = $m;
                $value = trim((string)$value);
                if ($value === '{') {
                    $line = sprintf('"%s" : {', $key);
                } else {
                    if (is_integer($value)) {
                        dd($value);
                        $line = sprintf('"%s" : %s', $key, $value);
                    } else {
                        $line = sprintf('"%s" : %s', $key, $value);
//                        dd($line);
                    }
                }
            } else {
//                dd($line, $pattern);
            }
            $line = str_replace("\n", '', $line);
            $line = str_replace("}", '},', $line);
//            $line = str_replace("]", '},', $line);
            $line = str_replace('=', ':', $line);

            // if it's just a comma-delimited list, it needs to be quoted
            if (preg_match('/^([a-zA-Z_]+)(,?)$/', $line, $m)) {
                $line = sprintf('"%s"%s', $m[1], $m[2], $line);
            }
            array_push($new, $line);
        }

        $contents = '{' . join("\n", $new) . '}';
        $contents = str_replace('},}', '}}', $contents);
//        file_put_contents($fn = $this->bag->get('kernel.project_dir') . '/test.json', $contents);
        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        return $data;
    }

    private array $tableNumCache = []; // hackish
    private array $tableNameCache = []; // name to tableNum

    public function getCaTable(?int $tableNum = null): array|\App\Entity\CaTable
    {
        return $tableNum ? $this->tableNumCache[$tableNum] : $this->tableNumCache;
    }

    public function getCaTableNumByName(?string $tableName = null): ?int
    {
        return $tableName ? $this->tableNameCache[$tableName] : $this->tableNameCache;
    }

    private function loadCoreTypes($fromFiles = false): ArrayCollection
    {
        static $alreadyLoaded = false;

        assert(!$alreadyLoaded, "Cores already loaded.");

        $conf = $this->getConf('datamodel');
        // this could be static, or indexed, whatever.
        $coreTypes = new ArrayCollection();
        if ($fromFiles) {
            $this->logger->warning("Loading from files");
            $coreTypes = $this->loadCoreTypesFromFiles();
        } else {
            $this->logger->warning("Loading from cache");
            foreach ($this->entityManager->getRepository(Core::class)->findAll() as $core) {
                $coreTypes->add($core);
            }
        }

        assert(array_key_exists('tables', $conf), "Missing tables in conf");

        foreach ($conf['tables'] as $name => $tableNum) {
            $caTable = (new CaTable())
                ->setCaTableNum($tableNum)
                ->setName($name);
//            $this->entityManager->persist($caTable);
            $this->tableNumCache[$tableNum] = $caTable;
            $this->tableNameCache[str_replace('ca_', '', (string)$name)] = $tableNum;

            // the database references table_num in the restrictions...
            // relationships? Tie to core?  To import?
        }
        $alreadyLoaded = true;


        return $coreTypes;
    }


    /** @return Core[] */
    public function getCoresByCategory(string $categoryCode): ArrayCollection
    {
        return $this->getCoreTypes()->filter(fn(Core $core) => $core->getCategoryCode() === $categoryCode);
    }

    public function asYaml(XmlProfile $xmlProfile): string
    {
        // goal:
        $data = $this->normalizer->normalize($xmlProfile, 'array',
            ['groups' => ['profile', 'ui', 'lists', 'relationship', 'rt', 'attributes',
//                'labels'
            ]]);


//        $retArray =array_filter($array, fn($array) =>
//            if(!empty($array)) {
//                array_filter($array);
//            }
//        );
//
//        dump($data, $retArray);
//        array_walk_recursive($data, fn($a, $b) => dd($a, $b, func_get_args()));

        // https://stackoverflow.com/questions/7696548/php-how-to-remove-empty-entries-of-an-array-recursively
        $yaml = Yaml::dump($data, 5, 3, Yaml::DUMP_OBJECT);
        dd($yaml);
        return $yaml;
    }

    // we could also load from classes, using the attributes.
    // fixtures only!
    public function loadCoreTypesFromFiles(): ArrayCollection
    {

//        if (!class_exists('App\\Entity\\Core', false)) {
//            return new ArrayCollection();
//        }

//        $baseData = $this->getFieldData(true);
        $coreTypes = new ArrayCollection();
        return $coreTypes;

        $systemLists = $this->getSystemLists();

        // use the documentation to create the initial cores: https://manual.collectiveaccess.org/dataModelling/primaryTables.html?highlight=primary%20tables
//        $dmPath = $docPath . '/_source/dataModelling';
        $primary = $this->docPath . '/primaryTables.rst';
        assert(file_exists($primary), "missing $primary");
        $rst = file_get_contents($primary);
        if (preg_match_all('/(.*?)\n\^+\n(.*?)\n/', $rst, $mm, PREG_SET_ORDER)) {
            foreach ($mm as [$all, $labels, $description]) {
                if (preg_match('/(.*?)\(ca_(.*?)\)/', (string)$labels, $mmm)) {
                    [$all, $name, $coreTable] = $mmm;
                    $descriptions[$coreTable] = $description;
                }
            }
        }


        // get the config data that's been dumped from BaseObject, 215 tables
        $rawModelData = $this->getRawModelData();

        // gets relationships.
        $fieldData = $this->getCaBaseModelData();

        // fieldType, displayType
        $typeConstants = $this->getConstants();
        array_push($typeConstants['FT'], "nested_list_item");
        array_push($typeConstants['FT'], "relationship");
        array_push($typeConstants['FT'], "textarea");
        // handle onetomany?
        foreach ($typeConstants as $prefix => $values) {
            foreach ($values as $idx => $value) {
                assert(is_int($idx), $idx . json_encode($values, JSON_THROW_ON_ERROR));
                $relatedEntity = (($prefix == 'DT') ? new FieldDisplayType() : new FieldType())
                    ->setCaId($idx)
                    ->setCode(u($value)->lower()->toString())
                    ->setName(u($value)->lower()->title()->toString());

                $errors = $this->validator->validate($relatedEntity);
                assert($errors->count() == 0, "Invalid entity " . $value);

                if ($this->persist) {
//                    $this->entityManager->persist($relatedEntity); // @todo: move to callback or complete out of this bundle
                }
                $related[$prefix][$idx] = $relatedEntity;
            }
        }
        // a nestedListItem can be a field type.  The customFieldType has the pointer to the list, the item itself is in attributes

//        $this->related = $related; /

        $plural = []; // for lookup later
//        $coreTables = self::CORE_TABLES;

//        sort($coreTables);
        // go through each table
        foreach ($fieldData as $coreTableName => $tableData) {

//            $data = $rawModelData[$coreTableName]; // old data, with UPPER
//            dd($fieldData, $coreTableName, $data, $coreTables);
//            $singular = str_replace(' ', '_', $data['NAME_SINGULAR']);
//            $singular = $data['NAME_SINGULAR'];
//            $exceptions = [];

            if (!in_array($coreTableName, $this->getAllTables())) {
                dd($coreTableName, $this->getAllTables());
                continue;
            }


            $singular = preg_replace('/ies$/', 'y', $coreTableName);
            $singular = preg_replace('/s$/', '', $singular);
//            if (!in_array($singular, ['acl', 'user', 'application_var', 'attribute','bookmark_folder','bookmark']))
            $labelTable = $singular . '_labels';
            if (array_key_exists($labelTable, $rawModelData)) {
                $data['labels'] = $rawModelData[$labelTable];
            } else {
                $labelTable = null;
            }
//
//                if (in_array($coreTableName, $tablesToCheck = array_merge(self::NON_OBJ_TABLES, self::OBJ_TABLES))) {
////                    dd($coreTableName, $tablesToCheck);
//                    assert(array_key_exists($labelTable, $rawModelData), "missing '$labelTable' (from $coreTableName) [$singular] ");
//                }


            // singularize, then add _labels.
            if ($coreTableName == 'bundle_displays') {
//                continue;
                $singular = 'list';
                $labelTable = 'bundle_display_labels';
            }
            // hack for mismatch


            $core = (new Core())
                ->setCaLabelsTable($labelTable ? 'ca_' . $labelTable : null)
                ->setCaTable($coreTableName)
                ->setTableDefinition($tableData, $related);

            if (!array_key_exists($labelTable, $rawModelData)) {
                $this->logger->error(sprintf("Missing Labels Table for  $coreTableName $labelTable %s: %s ", $core->getEntityName(), $core->getCaLabelsTable()));
                $core->setCaLabelsTable(null);
            }
            if ($core->getCaLabelsTable() === 'ca_metadata_type_restriction_labels') {
                dd($rawModelData);
            }
            assert($core->getFields()->count(), "no fields");

            if (in_array($coreTableName, self::OBJ_TABLES)) {
                $core->setCategoryCode(self::CATEGORY_OBJ);
            } elseif (in_array($coreTableName, self::NON_OBJ_TABLES)) {
                $core->setCategoryCode(self::CATEGORY_NON_OBJ);

            } elseif (in_array($coreTableName, self::META_TABLES)) {
                $core->setCategoryCode(self::CATEGORY_META);
            } elseif (in_array($coreTableName, self::SYSTEM_TABLES)) {
                $core->setCategoryCode(self::CATEGORY_SYSTEM);

            } elseif (in_array($coreTableName, self::META_TABLES)) {
                $core->setCategoryCode(self::CATEGORY_META);
            } elseif (in_array($coreTableName, self::UI_TABLES)) {
                $core->setCategoryCode(self::CATEGORY_UI);
            } else {
                dd($coreTableName);
                $core->setCategoryCode(self::CATEGORY_SYSTEM);
            }

            $core->setDescription($descriptions[$coreTableName] ?? null);
            if (array_key_exists($coreTableName, $systemLists)) {
                //
                $sysList = $systemLists[$coreTableName];
                dd($sysList, $coreTableName, $tableData);
            } else {
//                dd($coreTableName, $rawModelData[$coreTableName], $systemLists);
            }


            $core->setEntityName(ucfirst($core->getSingular()));
            $coreTypes->add($core);
            $plural[$core->getCaTable()] = $core;
            $singulars[$core->getSingular()] = $core;
            $fields = $tableData['fields'];

            if (array_key_exists('idno', $fields)) {
                $core->setHasIdno(true);
            }
            if (array_key_exists('parent_id', $fields)) {
                $core->setHasParent(true);
            }

            // add the labels and a core type, refactor later.
//            $core = (new Core())
//                ->setTableDefinition(['NAME_SINGULAR' => $labelTable])
//                ->setCaTable($labelTable)
//            ;
//            $core->setEntityName(ucfirst($core->getSingular()));
//            $coreTypes->add($core);
//            $plural[$core->getCaTable()] = $core;
//            $singulars[$core->getSingular()] = $core;
//            if ($core->getCaTable() == 'collections') {
//                dd($core, $fieldData['collections'], $systemLists);
//            }
        }


        // to get the relationships, get the keys that begin with <name>_x_
        foreach (array_filter($rawModelData, fn($key) => str_contains($key, '_x_'), ARRAY_FILTER_USE_KEY) as $tableName => $data) {
            [$core_table, $relation] = explode('_x_', $tableName);
            // ignore 'items', might not be used.
            if (in_array($core_table, ['items'])) {
                continue;
            }
            if (array_key_exists($core_table, $plural)) {
                $core = $plural[$core_table];
                $relationships[$core_table][$relation] = $data;
                $this->relationshipTables[$tableName] = [
                    'core' => $core_table,
                    'relation' => $relation,
                    'data' => $data, // the fields
                ];
//                $coreTypes[$tableName] = $data;
                assert(!empty($core));
            } else {
                $this->logger->info("Missing core table for relationship " . $tableName, [$core_table]);
            }

//                dd($tableName);
//                dd($core_table, $relation);
//                $coreComponents[$core]['relationships'][$relation] = $data;
        }
//        dd($relationships);
//        dd($this->remainingKeys());

        // now figure out how the _id fields relate, and tweak them as necessary.  Esp true for uuid now.


        return $coreTypes;
    }

    public function mermaid(): string
    {
        return <<<END
erDiagram
    CAR ||--o{ NAMED-DRIVER : allows
    CAR {
        string registrationNumber
        string make
        string model
    }
    PERSON ||--o{ NAMED-DRIVER : is
    PERSON {
        string firstName
        string lastName
        int age
    }


END;
    }


    // @return array of tables/fields from the json dump (BaseModel)
    public function getCaBaseModelData($coreOnly = true): array
    {
        $coreComponents = [];
        // get the config data that's been dumped from BaseObject
        $rawModelData = json_decode(str_replace('"ca_', '"', file_get_contents($this->fieldConfigPath)), true, 512, JSON_THROW_ON_ERROR);
//        dd($rawModelData);

        // all core components have a _labels table
//        foreach ($rawModelData as $tableName=>$data)
//        {
//            $singular = $data['NAME_SINGULAR'];
//            if (array_key_exists($labels = $singular . '_labels', $rawModelData))
//            {
//
//                // this is a core table then.
        ////                $core = (new Core())
        ////                    ->setEntityName(ucfirst($singular))
        ////                    ->setTableDefinition($data)
        ////                    ->setCaTable('ca_' . $tableName);
        ////
        ////                $core->setTypesListCode($singular);
        ////                    dd($core, $labels, $data, $rawModelData[$labels]);
//            }
//            if (preg_match('/_labels/', $tableName, $m))
//            {
        ////                dd($data);
//            }
//        }

//        dd(array_keys($coreComponents));
        // @todo: parse https://docs.collectiveaccess.org/wiki/Basic_Tables


        foreach ($rawModelData as $tableName => $data) {
            if (preg_match('/queue|guid|user_role|user_groups|groups|locales|attribute_value_multifiles|attribute_values/', (string)$tableName)) {
                continue;
            }

            // find the "core" data types
            $type = $tableName; // original, for lookup later with ca_entities, etc.
//            $tableName = str_replace('ca_', '', $tableName);
            // appears that these are no longer used.
            if (preg_match('/^item/', (string)$tableName)) {
//                dump($tableName);
                continue;
            }

            if (preg_match('/^metadata_elem/', (string)$tableName)) {
//                dd($tableName, $data);
//                continue;
            }

//            if (in_array($tableName, ['items', 'item_tags', 'item_comments', 'item_labels'])) {
//                continue;
//            }
            $parts = explode('_x_', (string)$tableName);
            if (count($parts) == 2) {
                [$core, $relation] = $parts;
                $this->relationshipTables[$tableName] = [
                    'data' => $data,
                ];
                $coreComponents[$core]['relationships'][$relation] = $data;
                if ($core == 'groups') {
                    dd($tableName);
                }
            } else {
                if (preg_match('/(.*?)_(labels)/', (string)$tableName, $m)) {
                    $core = $m[1] . 's'; // @todo: tags? comments?
//                    $data['list_name'] = $m[0];
                    $coreComponents[$core][$m[2]] = $data;
//                    dump($tableName, $core, array_keys($coreComponents));

//                    dd($coreComponents[$core], $core);
                } else {
                }
                $core = $tableName;
                $coreComponents[$core]['name'] = $data['NAME_SINGULAR'];
                $coreComponents[$core]['plural'] = $data['NAME_PLURAL'];
                $coreComponents[$core]['fields'] = $data['FIELDS'];
                $coreComponents[$core]['type'] = $type;
                $labelsTable = $type . '_labels';
                if (array_key_exists($labelsTable, $rawModelData)) {
                } else {
                }

                //BaseModel::$s_ca_models_definitions['ca_entity_labels']
            }
        }

//        $caTable = new CaTable(tableName: $tableName, fieldData: $coreComponents[$TABLE]);
//        dd($caTable);

        $systemLists = $this->getSystemLists();
        foreach ($this->getAllTables() as $TABLE) {
            $base = preg_replace('/ies$/', 'y', $TABLE);
            $base = preg_replace('/s$/', '', $base);

            if (array_key_exists($types = $base . '_types', $systemLists)) {
                // this is now a SystemList
                $coreComponents[$TABLE]['types_list'] = $systemLists[$types];

//                if (array_key_exists($ca_table = 'ca_' . $types, $rawModelData)) {
//                    // this
//                    $coreComponents[$TABLE]['types_list'] = $rawModelData[$ca_table];
//                    dd($ca_table, $rawModelData[$ca_table]);
//                }
            }

            if (array_key_exists($labels = 'ca_' . $base . '_labels', $rawModelData)) {
                $coreComponents[$TABLE]['labels'] = $rawModelData[$labels];
            }
        }

        // remove anything we're not saving
        foreach ($coreComponents as $tableName => $data) {
            if (!in_array($tableName, $this->getAllTables())) {
//                dd($tableName);
                unset($coreComponents[$tableName]);
            }
        }

//        dd($coreComponents['items']);
        ksort($coreComponents);
//        dd(array_keys($coreComponents), $this->getAllTables());
        foreach (['bundle_displays'] as $requiredTable) {
            assert(array_key_exists($requiredTable, $coreComponents), "Missing $requiredTable");
//            dd($requiredTable, $coreComponents[$requiredTable], );
        }

//        assert(array_key_exists('entities_x_entities', $coreComponents), "Missing entities_x_entities");

        return $coreComponents;
//        dd('const CORE_TABLES=[' . join(',', array_map(fn($key) => "'$key'", array_keys($coreComponents)))) . ']';
    }

    public function getXmlProfile(string $profileId, bool $load = false): XmlProfile
    {
        if (!file_exists($profileId)) {
            $filename = $this->xmlDir . "/$profileId.xml";
        } else {
            $filename = $profileId;
        }
        assert(file_exists($filename), "Missing $filename");
//        $profile->profileId = $profileId;
        if ($load) {
            $profile = $this->loadXml(file_get_contents($filename));
        } else {
            // don't parse, just add the filenames
            $profile = (new XmlProfile());
        }
        $profile
            ->setProfileId($profileId)
            ->setFilename($filename);

        return $profile;
    }


    /** @return XmlProfile[] */
    public function getXmlProfiles(?string $filename = null, bool $load = true): iterable
    {
        $profiles = [];
        $finder = new Finder();
        foreach ($finder->files()->depth('<2')->in($this->xmlDir)->name($filename ? $filename . '.xml' : '*.xml')->notName('base.xml') as $fileInfo) {
            $profileId = $fileInfo->getFilenameWithoutExtension();
            if ($profileId == 'base') {
                continue;
            }
            array_push($profiles, $this->getXmlProfile($profileId, $load));
        }
        return $profiles;
    }

    public function loadXml(string $xml, string $profileClassName=XmlProfile::class): XmlProfile
    {
        $fieldData = $this->getCaBaseModelData();

        // for some reason, settings aren't working as expected.
        $pattern = '|(<setting name="([^"]+)"[^(>$)]*)>([^<]+)</setting>|';
        assert(preg_match($pattern, $xml), "$pattern not found in xml. ");
        // tweak the xml so that settings can be read as attribute (v) and an element.
        $xml = preg_replace_callback($pattern, function ($m) {
            return ($m[2] <> 'display_template') ? sprintf("%s v='%s'>%s</setting>", $m[1], $m[3], $m[3]) : $m[0];
//                    dd($matches);
//                    return strtolower($matches[0]);
        }, $xml);
//            $xml = preg_replace('|(<setting[^(>$)]+)>([^<]+)</setting>|', "$1 v=\"$2\">$2</setting>", $xml);

//            $profile->setXml($xml);

        // when xdebug is on, this takes too long to parse.
        $xmlProfile = $this->parseXml($xml);
        if ($xmlProfile) {
            $xmlProfile
//                    ->setDescription($xmlProfile->profileDescription)
                ->setUiCount(is_countable($xmlProfile->getUserInterfaces()) ? count($xmlProfile->getUserInterfaces()) : 0)
                ->setListCount(is_countable($xmlProfile->getLists()) ? count($xmlProfile->getLists()) : 0)
                ->setDisplayCount(is_countable($xmlProfile->getDisplays()) ? count($xmlProfile->getDisplays()) : 0)
                ->setMdeCount(is_countable($xmlProfile->getElements()) ? count($xmlProfile->getElements()) : 0)
                ->setInfoUrl($xmlProfile->infoUrl);
            try {
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), [$profile->getFilename()]);
//                continue;
            }
        }

        // hack, use simpleXml for counts, description
//            $this->simpleloadXml($profile);
//        if ($updateDatabase) {
//            $this->logger->info("Saving " . $profile->getFilename());
//            $this->entityManager->flush();
//        }

        // map the placements and mdes to the fields, if appropriate
        /** @var ProfileUserInterface $userInterface */
        foreach ($xmlProfile->getUserInterfaces() as $userInterface) {
            /** @var ProfileScreen $screen */
            foreach ($userInterface->getScreens() as $screen) {
                $screen->userInterface = $userInterface;
                /** @var ProfilePlacement $placement */
                $bundles = [];
                foreach ($screen->getPlacements() as $placement) {
                    if ($bundle = $placement->bundle) {
                        if (empty($bundles[$bundle])) {
                            $bundles[$bundle] = [];
                        }
//                            dd($placement->settings->setting);
                        array_push($bundles[$bundle], $placement->settings->asArray());
                    }

                    $placement->screen = $screen;
                    $type = str_replace('ca_', '', (string)$userInterface->type);
//                        dd($type,  array_keys($fieldData));
//                        if (!array_key_exists($type, $fieldData)) {
//                            dd($type, array_keys($fieldData));
//                        }

//                        if ($type == 'entities_x_entities') {
//                            dd($type, $fieldData);
//                        }

                    // if it's a relationship table,
//                        dd(array_keys($this->relationshipTables));
                    if (array_key_exists($type, $this->relationshipTables)) {
                        continue;
                    } else {
                    }

                    assert(array_key_exists($type, $fieldData), "Missing $type in " . join(',', array_keys($fieldData)));
                    $code = $placement->code;
                    if (in_array($code, ['nonpreferred_labels', 'preferred_labels'])) {
//                            dd($type, $fieldData[$type]);
                        assert(array_key_exists($type, $fieldData), "Missing $type");
//                            assert(array_key_exists('labels', $fieldData[$type]), "Missing labels $type " . dd($type, $fieldData[$type]));
//                            dump($fieldData[$type], $type);
                        // arg, entities doesn't have labels...
                        if (array_key_exists('labels', $fieldData[$type])) {
                            $placement->setField($fieldData[$type]['labels']);
                        }
                        //=                            $placement->setField($fieldData[]);
                    } else {
                        if (array_key_exists($code, $fieldData[$type]['fields'])) {
                            $placement->setField($fieldData[$type]);
//                            dd($placement->getField(), $fieldData[$type], $type);
//                            dd($placement->code, $fieldData[$type], $fieldData[$type]['fields'][$placement->code]);
                        }
                    }
                }
                $screen->setBundles($bundles);
//                    dd($bundles);
            }
        }
//            foreach ($xmlProfile->getElements() as $element) {
//                dd($element);
//            }
        return $xmlProfile;
    }

    private function remainingKeys($x = null): array
    {
        static $keys;
        if ($x) {
            $keys = $x;
        }
        return $keys;
    }

    private function setupSystemLists()
    {
//        if (!class_exists('App\\Entity\\SystemList')) {
//            return ;
//        }
        $systemListCodes = array_keys($this->getSystemLists());

        foreach ($this->getCoreTypes() as $core) {
            $relatedTables = [];
            $map = [];
            $singular = $core->getSingular();
            foreach (['types', 'label_types', 'sources', 'statuses', 'hierarchy'] as $category) {
                $x = $singular . '_' . $category;
                if (in_array($x, $systemListCodes)) {
                    $sysList = $this->getSystemList($x);
                    $sysList->setCategory($category);
                    $relatedTables[$category] = $sysList;
                    $map[$category] = $sysList->getEntityName();
                    $sysList->setUsedBy($core);
                    $core->setRelatedTable($category, $sysList);

                    /**  */ //                $sysList->setUsedBy()
//                dd($sysList, $this->getCoreTypeCodes());

                    unset($systemListCodes[array_search($x, $systemListCodes)]); // remove it, so we can see what's left.
                    $this->remainingKeys($systemListCodes);
//                dd($systemListCodes, $x);
                } else {
//                dd($x, $systemListCodes, in_array($x, $systemListCodes));
                }
            }

            // hack for actually adding the instances!
            $core->setKeyTables($relatedTables);
            $core->setKeyListMap($map);
            if (count($relatedTables)) {
//            dd($core->getSingular(), $relatedTables, $systemLists, $core);
            }

            if ($core->getEntityName() == 'Coll') {
//                dd($core);
            }
        }
//        foreach ($this->getSystemLists() as $code => $sysList) {
//            dump($code . ': ' .  ($sysList->getUsedBy() ? $sysList->getUsedBy()->getEntityName()  : '~') . '.' . $sysList->getCategory() );
//        }
//        dd($systemListCodes); // what's not set.
    }

    // if there's a related table defined in the field, handle it by adding a field to both sides.
    private function addRelatedFields()
    {
        foreach ($this->getCoreTypes() as $core) {
            foreach ($core->getFields() as $field) {
                if ($sysListCode = $field->getTypesList()) {
                    // SystemList does not exist in ac
//                        $sysList = $this->getSystemList($sysListCode);
//                        $relatedEntityName = $sysList->getEntityName();

//                        dd($field, $sysListCode, $sysList);
                }
            }
        }
    }

    // this is only used to generate PHP classes.  Ac can get the relevant information via introspection?
    private function setFields(Core $core, array $singularCore = [])
    {
        if (in_array($core->getEntityName(), ['Set', 'SetItem'])) {
//            return;
        }
        $name = $core->getCaTable();

        // check which tables exist, for mapping with certain field types.
        foreach ($core->getFields() as $field) {
            if (in_array($field->getName(), ['status', 'access'])) {
                $field->setNullable(true);
//                dd($field);
            }

//            if ($core->getEntityName() == 'Obj') {
//                if ($field->getName() == 'list_id') {
//                    dd($field);
//                }
//            }
            $length = false;
            $fieldType = $field->getCaFieldType();
            $type = $fieldType;
            $doctrineType = 'string';
            // if the field name is handled by the trait, ignore it.

            // 'is_enabled', 'is_default',  need these for import.
            if (in_array($field->getCaFieldName(), ['view_count', 'color', 'icon', 'status', 'parent_id', 'deleted', 'is_enabled', 'is_default', 'source_info', 'item_value', 'idno', 'idno_sort', 'rank', 'settings'])) {
                $field->setIsIgnored(true);
                continue;
            }

            // @todo: map object_lot and lot
            if ($field->getCaFieldName() == 'lot_id') {
//                dd($field, $field->getName());
            }

            if ($field->getCaFieldName() == 'lot_status_id') {
//                dd($field, $field->getName());
            }

            switch ($fieldType) {
                case self::MANY_TO_ONE:
                    dd($fieldType, $field);
                    $target = $field->getTarget();
//                    $field->setMappedBy('authList');
                    // @todo: add constructor statements.
                    $field
                        ->setNullable(true)
                        ->setDoctrineType('Collection')//                        ->setName($target . '_' . $field->getName())
                    ;
                    if ($field->getNullable()) {
                        $field->setDefaultValue('null');
                    }

//                    dd($target);
//                        $type = self::MANY_TO_ONE;
//                    $doctrineType = $target;
                    break;
                case self::ONE_TO_MANY:
                    dd($fieldType, $field);


                    // if this is a system list, now point to the proper entity
////                        $type = 'OneToMany';
//                    $name = $field->LABEL;
//                    $doctrineType = '\Doctrine\Common\Collections\Collection'; // $entityName;
                    $doctrineType = 'Collection';
                    $field
//                        ->setNullable(true)
                        ->setDoctrineType($doctrineType);
                    break;
                case FT_NUMBER:
                    $type = 'integer';
                    $doctrineType = 'int';
                    // this is already captured elsewhere!!
                    if (preg_match('/_id$/', $field->getCaFieldName(), $m)) {
                        $field
//                            ->setName('ca_' . $field->getName())
                            ->setNullable(true); // these are the old ca id's
                    }

                    if (preg_match('/(source|status|type)_id/', $field->getCaFieldName(), $m)) {
                        $typeName = $m[1];
                        $field
//                            ->setName('ca_' . $field->getName())
                            ->setNullable(true); // these are the old ca id's
                        break;


                        /** @var SystemList $systemList */
                        $systemLists = $this->getSystemLists();
                        if (array_key_exists($field->getTypesList(), $systemLists)) {
                            // if there's a list code, we need to map it to the new entity.
                            $systemList = $systemLists[$field->getTypesList()];
                            switch ($typeName) {
                                case 'type':
                                    $core->setTypeClass($systemList->getEntityName());
                                    break;
                                case 'source':
                                    $core->setSourceClass($systemList->getEntityName());
                                    break;
                                default: // dump($core, $systemList, $field);
                            }


                            $field
                                ->setTarget($systemList->getEntityName())
                                ->setCaFieldType(self::MANY_TO_ONE)
                                ->setName($systemList->singular())
//                                ->setDefaultValue('null')
//                                ->setNullable(true)
//                                ->setName($typeName)
//                                ->setMappedBy($core->getPluralCamel())
                                ->setInverse($core->getPluralCamel())
                                ->setSystemList($systemList);
                            // the system list needs the reverse.
                            $systemList->setUsedBy($core);
                            $doctrineType = $systemList->getEntityName(); // the new intrinsic type
                            assert($field->getTarget() <> 'Lst', $field->getTarget());
                        } elseif ($core->getCaTable() == 'relationship_types') {
                            // it's just the primary key of the relationship_types table, @todo: handle this
                        } else {
                            assert(false);
//                            ($core, $field->getTypesList(), $field, array_keys($systemLists));
                        }


                        // this links to the
                    } elseif (preg_match('/(.*?)_type_id/', $field->getName(), $m)) {
                        if (in_array($field->getName(), ['acquisition_type_id', 'hier_type_id', 'deaccession_type_id'])) {
                            break; // eh.
                        }
                        if (in_array($field->getName(), ['item_status_id'])) {
                            // not sure how to best handle status now.
                            break; // eh
                        }
                        assert(false);
//                        ($field, $m, $core);

                        // this is a system list, which is now its own entity
                    } elseif (preg_match('/(.*?)_status$/', $field->getName(), $m)) {
                        assert(false);
//                        ($field, $m);
                        // this is a system list, which is now its own entity
                    } elseif (preg_match('/(.*?)_id/', $field->getName(), $m)) {
                        // see if it's a primary key to another table.  If so, link it.
                        $singular = $m[1];
                        if ($singular == 'type') {
                            assert(false);
                            // ($core, $field);
                        }
                        if ($singular == 'item') {
                            $singular = 'list_item';
                            $field->setName($singular . '_id');
                        }

//                        if ($singular == 'list') { continue; }
//                        if ($singular != 'object_lot') { continue; }
                        // @todo: handle lot_id is really object_lot_id, home_location_id / storage, etc.

                        assert(false);
//                        ($singular, $core->getSingular(), $field->getName());

                        if ($singular == 'lot_status_id') {
                            $singular = 'object_lot_status_id';
                        }
                        if ($singular == 'lot_id') {
                            $singular = 'object_lot_id';
                        }

                        if ($core->getSingular() == $singular) {
                            // this table, so rename to ca_ to make it obvious that this is the old internal id
                            $field
                                ->setName($singular = 'ca_' . $field->getName())
                                ->setNullable(true)
                                ->setCaFieldName($field->getName());
                        }

//                        ($core, $singular, $field);

                        if (array_key_exists($singular, $singularCore)) {
                            /** @var Core $relatedEntity */
                            $relatedEntity = $singularCore[$singular];
                            $doctrineType = $relatedEntity->getEntityName();

                            $field
                                ->setCaFieldType(self::MANY_TO_ONE)
                                ->setNullable(true)
                                ->setTarget($relatedEntity->getEntityName());
                            $field->setInverse($core->getPluralCamel()); // ???
                            $field->setMappedBy(u($core->getPlural())->camel()); // ???

                            // List/NestedListItems is tricky.
//                            if ($core->getEntityName() <> 'Lst')
                            {
                                $relatedField = (new Field());
                                $relatedEntity->addField($relatedField);
                                $relatedField
//                                    ->setMappedBy($relatedEntity->getSingular())
//                                    ->setDoctrineType('AuthList')
                                    ->setTarget($core->getEntityName())
                                    ->setMappedBy($relatedEntity->getSingular())
                                    ->setNullable(false) // because the collection itself is initialized
//                                    ->setInverse($relatedEntity->getSingular())
                                    ->setName($core->getPlural())
                                    ->setCaFieldType(self::ONE_TO_MANY);
                                if ($relatedField->getNullable()) {
                                    $relatedField->setDefaultValue('null');
                                }
//                                $field->setDefaultValue('NULL!!');
                                //
                                if ($relatedEntity->getEntityName() == 'Lst') {
                                    $relatedField->setNullable(true); // because now NestedListItem's can have a list_id OR a type_id, etc.

//                                    if ($relatedField->getName() == '')

//                                    $this->logger->warning($core->getCaTable() . "." . $field->getName() .
//                                        " Related List: " . $relatedField->getName(), $core->getKeyTables());
//                                    dump($relatedEntity, $relatedField, $field);
//                                    $relatedEntity->addField($relatedField);
                                }

                            }

//                            $new['inverse'] = $newName;
                            $field->setName(u($singular)->camel());
                            // add it to the related entity??
                        }
                    }

                    // for now...
                    $field
                        ->setNullable(true);


                    // this might be a OneToMany, e.g. NestedListItem to list
                    break;
                case FT_VARS:
//                    $type = 'array';
//                    $doctrineType = 'array';
//                    break;
                case FT_TEXT:
                    $type = 'string';
//                    assert(!empty($field->BOUNDS_LENGTH[1]), json_encode($field));
                    $length = $field->BOUNDS_LENGTH[1] ?? 255;
                    $doctrineType = 'string'; // or text?
                    $field->setMaxLength((int)$length);
                    // really, almost everything is nullable, especially strings.
                    $field
                        ->setNullable(true);

                    break;
                case FT_TIMESTAMP:
                case FT_DATETIME:
                case FT_DATE:
                case FT_TIME:
                    $type = 'date';
                    $doctrineType = \DateTime::class;
                    break;

                case FT_MEDIA:
                case FT_FILE:
                    // @todo: handle media...
                    $type = 'string';
                    $doctrineType = 'string';
                    $field->setNullable(true);
                    break;

                case FT_TIMERANGE:
                case FT_HISTORIC_DATE:
                case FT_HISTORIC_DATETIME:

                case FT_PASSWORD:
                case FT_TIMECODE:
                    $type = 'string';
                    $doctrineType = 'string';
//                    $type = false;
                    break;

                case FT_DATERANGE:
                    $type = 'string';
                    $doctrineType = 'string';
                    // https://blog.4xxi.com/using-custom-types-in-symfony-doctrine-f865c7072757
                    break;
                case FT_HISTORIC_DATERANGE:
                    $type = 'string';
                    $doctrineType = 'string';
//                    $vars .= "// @todo: fix type FT_HISTORIC_DATERANGE for field $name\n\n";

                    break;
                case FT_BIT:
                    $type = 'boolean';
                    $doctrineType = 'bool';
                    $field->setNullable(true);
                    break;

                default:
                    throw new \Exception("needs to handle " . $fieldType . ' for ' . $name);
            }
            $field->setDoctrineType($doctrineType);
            if (!empty($doctrineType)) {
            }
            assert(!empty($field->getDoctrineType()), $field->getCaFieldType());
            if (!$type) { // @todo: daterange, etc.
                continue;
            }
            $field->setDbType($type);
        }
    }

    public function fieldsToEntityMaker(Core $core, array $options = []): ?string
    {
//        assert(false, $core->getEntityName());
        $options = (new OptionsResolver())
            ->setDefaults(['path' => false, 'debug' => false])->resolve($options);
        $commands = [];

        // core tables
        $tableName = $core->getCaTable();
        $entityName = $core->getEntityName();
//            if ($entityName <> 'NestedListItem') { continue; }
//        if ($entityName == 'AuthList') { return 'skipping'; }

        $vars = sprintf("/** Properties from the %s fields table. */\n\n", $core->getCaTable()); // for creating the class dynamically.

        $repo = $entityName . 'Repository';
        $entityCode = $this->twig->render("php/Entity.php.twig", $enityVars = [
            'repo' => $repo,
            'entityName' => $entityName,
            'tableName' => $tableName,
            'filename' => sprintf('/src/Traits/Project%s.php', $core->getEntityName()),
            'core' => $core,
            'privateVar' => u($entityName . 's')->camel(), // type, source, label, this doesn't seem quite right.
            'fields' => $core->getFields(),
            'ns' => self::class, 'vars' => $vars]);
        $repoCode = $this->twig->render("php/repo.php.twig", ['entityName' => $entityName]);

        // the trait should be Project<Core> and have all the key tables and helpers

        $traitCode = $this->twig->render("php/trait.php.twig", $enityVars);

        if ($options['debug']) {

//            dd($code, $repoCode);
        }

        $core->setEntityCode($entityCode)
            ->setRepoCode($repoCode);

        if ($path = $options['path']) {
            $classFilename = "$path/src/Entity/$entityName.php";
            file_put_contents($classFilename, $entityCode);
            $repoFilename = $path . $enityVars['filename'];
            file_put_contents($repoFilename, $repoCode);

            $traitFilename = sprintf("$path/src/Traits/Project%s.php", $core->getEntityName());
            file_put_contents($traitFilename, $traitCode);
            $this->extras($core);
        }

        return null;
    }

    public function extras(Core $core)
    {
        $path = $this->bag->get('ac_path');

        $fn = $path . '/src/Entity/Project.php';
        $projectPhp = file_get_contents($fn);
        $trait = 'Project' . $core->getEntityName();

        // if the trait is missing, add it.  Use phpReflection, or is that overkill?
        $useStatement = sprintf('use Traits\%s;', $trait);
        if (!str_contains($projectPhp, $useStatement)) {
            $projectPhp = str_replace('// generated-traits', "// generated-traits\n$useStatement", $projectPhp);
            file_put_contents($fn, $projectPhp);
//                dd('missing ' . $useStatement, $projectPhp, $useStatement);
        }

        // we may need to loop over the key table for this.
        /**
         * @var string $table
         * @var SystemList $systemList
         */
        foreach ($core->getKeyTables() as $table => $systemList) {
            $initStatement = sprintf('$this->%s = new ArrayCollection();', u($systemList->getEntityName())->camel() . 's');
            if (!str_contains($projectPhp, $initStatement)) {
                $projectPhp = str_replace('// generated-inits', "// generated-inits\n    $initStatement", $projectPhp);
                file_put_contents($fn, $projectPhp);
            }
        }
        $projectPhp = str_replace('use Traits\ProjectListItem;', "// use Traits\ProjectListItem;", $projectPhp);
        file_put_contents($fn, $projectPhp);

        // hack, something is out-of-sync with ProjectList and NestedList traits


        //            dd($trait, $projectPhp, $entityName);
//        }
    }

    public function singularize(string $x): string
    {
        $x = preg_replace('/ies$/', 'y', $x);
//        $x = preg_replace('/ses$/', 's', $x);
        $x = preg_replace('/s$/', '', $x);
        $x = preg_replace('/_clas$/', '_class', $x);
        $x = preg_replace('/_statuse$/', '_status', $x);
        return $x;
    }


    private $translationLabels = [];

    private function addLabel(XmlLabelsInterface $el, string $key, ?string $value, string $language, string $locale)
    {
        if (!$value) {
            return;
        }
        // if it already exists, and it's different than the lang, create a special override in the lang_COUNTRY file.
        if ($existing = $this->translationLabels[$language][$key] ?? false) {
            if ($existing <> $value) {
                $this->translationLabels[$locale][$key] = $value;
            }
        }
        $this->translationLabels[$language][$key] = $value;
    }

    /** @param ProfileLabel[] */
    private function addLabels(XmlLabelsInterface $el)
    {
        foreach ($el->getLabels() as $label) {
            $locale = $label->locale;
            [$language, $countryCode] = explode('_', $locale);
            // relationship Types don't have a name, just typename and typename_reverse
            if ($label->name) {
                $this->addLabel($el, $el->_label(), $label->name, $language, $locale);
            }

            if ($label->description) {
                $this->addLabel($el, $el->_description(), $label->description, $language, $locale);
            }

            // if it's a relationshipType.
            if ($el->_typename()) {
                $this->addLabel($el, $el->_typename(), $label->typename, $language, $locale);
                $this->addLabel($el, $el->_typename_reverse(), $label->typename_reverse, $language, $locale);
            }
//            if ($el->_typename_reverse()) {
//                $this->addLabel($el, $el->_typename_reverse(), $label->typename_reverse, $language, $locale);
//            }

        }
    }

    public function loadLabelsFromXml(XmlProfile $profile): array
    {
        $locales = [];
        // really we should implement a LabelsInterface, then just get the right classes.s
        foreach ($profile->getLocales() as $locale) {
//        $localMap = ['en' => 'en_US', 'fr' => 'fr_FR'];
            $localeCode = $locale->lang . '_' . $locale->country; // 'en_US'; // @todo: get from xml
            /** @var ProfileUserInterface $ui */
            foreach ($profile->getUserInterfaces() as $ui) {
                $this->addLabels($ui); //  $ui->code, 'ui');
                /** @var ProfileScreen $screen */
                foreach ($ui->getScreens() as $screen) {
                    $this->addLabels($screen); //  $ui->code, 'ui');
                }
            }

            /** @var ProfileLists $list */
            foreach ($profile->getLists() as $list) {
                $this->addLabels($list);
                foreach ($list->getItems() as $item) {
                    $this->addLabels($item);
                }
            }

            foreach ($profile->getRelationshipTables() as $table) {
                /** @var ProfileRelationshipTableType $element */
                foreach ($table->getTypes() as $element) {
                    $this->addLabels($element);
                }
            }

            foreach (['getElements'] as $method) {
                /** @var ProfileMetaDataElement $element */
                foreach ($profile->{$method}() as $element) {
                    $this->addLabels($element);
                    // it might be nested.  @todo: check for recursive.
                    if (!empty($element->elements)) {
                        foreach ($element->getElements() as $nestedElement) {
                            $this->addLabels($nestedElement);
                        }
                    }
                }
            }
        }
        return $this->translationLabels;
    }

    public function createDemoMap(): array
    {
        // should be a service, of course.
        $crawler = new Crawler(file_get_contents($this->bag->get('kernel.project_dir') . '/demo_homepage.html'), null);

        $map = [];
        foreach ($crawler->filter('.sf-menu a')->links() as $link) {
            $text = $link->getNode()->textContent;
            $text = str_replace('»', '', $text);
            $text = trim($text);
            $text = str_replace("\n", " ", $text);
            $text = preg_replace("/ +/", " ", $text);
            $map[$text] = $link->getUri();
        }
        return $map;
    }

    public function createTranslations()
    {
        $trans = [];
        // translations
        foreach ($this->getCoreTypes() as $core) {
            $trans[$core->getEntityName()] = [
                'description' => $core->getDescription()
            ];
        }
        $yaml = Yaml::dump($trans);
        $fn = $this->bag->get('ac_path') . '/translations/core+intl-icu.en.yaml';
        file_put_contents($fn, $yaml);
        return $yaml;
    }

    public function extractSystemTranslations($resource, $symfonyLocale, $domain = 'messages')
    {
        $localMap = ['en' => 'en_US', 'fr' => 'fr_FR'];
        $locale = $localMap[$symfonyLocale];
        // this is for testing only, move to a database for deployment
        $profiles = $this->loadXml();
        $translations = [];
        /** @var \App\Entity\Profile $p */
        foreach ($profiles as $p) {
            $profile = $p->getXmlProfile();
            /** @var ProfileUserInterface $ui */
            foreach ($profile->getUserInterfaces() as $ui) {
                $labels = array_filter($ui->getLabels(), fn(ProfileLabel $label) => $label->locale == 'en_US');
//                dump($labels);
                if ($l = array_pop($labels)) {
                    $translations[$ui->_label()] = $l->name;
                    $translations[$ui->_description()] = $l->description ?: " ";
                }

                /** @var ProfileScreen $screen */
                foreach ($ui->getScreens() as $screen) {
                    $labels = array_filter($screen->getLabels(), fn(ProfileLabel $label) => $label->locale == 'en_US');

                    if ($l = array_pop($labels)) {
                        $translations[$screen->_label()] = $l->name;
                        $translations[$screen->_description()] = $l->description ?: " ";
                    }
//                    dd($l, $translations);
                }
            }
            dd($translations);

            /** @var ProfileLists $list */
            foreach ($profile->getLists() as $list) {
                $labels = array_filter($list->getLabels(), fn(ProfileLabel $label) => $label->locale == $locale);
                if ($l = array_pop($labels)) {
                    $translations[$list->_label()] = html_entity_decode((string)$l->name);
                    $translations[$list->_description()] = html_entity_decode((string)$l->description) ?: " ";
                }
//                dump($list);
                foreach ($list->getItems() as $item) {
                    $labels = array_filter($item->getLabels(), fn(ProfileLabel $label) => $label->locale == $locale);
                    if ($l = array_pop($labels)) {
                        $translations[$item->_t($list)] = $l->name ?: $l->name_singular;
//                        $translations[$item->_label()] = $l->name;
                        assert(empty($l->description), "Item has description");
//                        $translations[$item->_description()] = $l->description;
                    }
                }
            }

            foreach ($profile->getRelationshipTables() as $table) {
                /** @var ProfileRelationshipTableType $element */
                foreach ($table->getTypes() as $element) {
                    $labels = array_filter($element->getLabels(), fn(ProfileLabel $label) => $label->locale == $locale);
                    if ($l = array_pop($labels)) {
                        $translations[$element->_label()] = $l->name;
                        $translations[$element->_description()] = $l->description;
                        $translations[$element->_typename()] = $l->typename;
                        $translations[$element->_typename_reverse()] = $l->typename_reverse;
                    }
                }
            }

            foreach (['getElements'] as $method) {
                /** @var ProfileMetaDataElement $element */
                foreach ($profile->{$method}() as $element) {
                    $labels = array_filter($element->getLabels(), fn(ProfileLabel $label) => $label->locale == $locale);
                    if ($l = array_pop($labels)) {
                        $translations[$element->_label()] = $l->name;
                        $translations[$element->_description()] = $l->description ?: ' ';
                    }

                    // it might be nested
                    if (!empty($element->elements)) {
                        foreach ($element->getElements() as $nestedElement) {
                            $labels = array_filter($nestedElement->getLabels(), fn(ProfileLabel $label) => $label->locale == $locale);
                            if ($l = array_pop($labels)) {
                                $translations[$element->_label()] = $l->name;
                                $translations[$element->_description()] = $l->description ?: ' ';
                            }
                        }
                    }


//                    {% for e in mde.elements %}
//                    <li>{{ e.value.code }} <i>{{ e.value.datatype }} {{ e.value._label|trans }}</i></li>
//{{ dump(e.value) }}
//                        {% endfor %}
                }
            }
            // maybe use xpath to get all the Labels?
        }
//        $messages = $this->getRepository()->findByDomainAndLocale($domain, $locale);
//        $values = array_map(static function (EntityInterface $entity) {
//            return $entity->getTranslation();
//        }, $messages);
        $catalogue = new MessageCatalogue(
            $symfonyLocale,
            [
                $domain => $translations
            ]
        );
        return $catalogue;
    }

//    public function getCore($shortEntityName) {
//        return $this->getCoreTypes()
//    }
}
