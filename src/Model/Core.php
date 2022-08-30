<?php

namespace Survos\Providence\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use function Symfony\Component\String\u;

# [ORM\Entity(repositoryClass: CoreRepository::class)]
class Core implements RouteParametersInterface, \Stringable
{
    use RouteParametersTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    # [ORM\Column(type: 'string', length: 255)]
    private $caTable;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $labelTypesListCode;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $typesListCode;
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $entityName;
    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private array $tableDefinition = [];
    #[ORM\OneToMany(targetEntity: Field::class, mappedBy: 'core', cascade: ['persist'])]
    private $fields;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $typesListDescription;
    #[ORM\Column(type: 'json', options: ['jsonb' => true], nullable: true)]
    private $relationships = [];
//    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $singular;
//    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $plural;
    #[ORM\Column(type: 'text', nullable: true)]
    private $entityCode;
    #[ORM\Column(type: 'text', nullable: true)]
    private $repoCode;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $identity_field_name;
    #[ORM\OneToMany(targetEntity: SystemList::class, mappedBy: 'usedBy')]
    private $systemLists;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $sourceClass;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $typeClass;
    #[ORM\Column(type: 'json', options: ['jsonb' => true], nullable: true)]
    private $keyTables = [];
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $hasIdno;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $hasParent;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $caLabelsTable;
    #[ORM\Column(type: 'text', nullable: true)]
    private $traitCode;
    #[ORM\Column(type: 'array', nullable: true)]
    private $keyListMap = [];
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;
    #[ORM\Column(type: 'string', length: 32)]
    private $categoryCode;
    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->systemLists = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCaTable(): ?string
    {
        return $this->caTable;
    }
    public function setCaTable(string $caTable): self
    {
        $this->caTable = $caTable;

        return $this;
    }
    public function getLabelTypesListCode(): ?string
    {
        return $this->labelTypesListCode;
    }
    public function setLabelTypesListCode(string $labelTypesListCode): self
    {
        $this->labelTypesListCode = $labelTypesListCode;

        return $this;
    }
    public function getTypesListCode(): ?string
    {
        return $this->typesListCode;
    }
    public function setTypesListCode(string $typesListCode): self
    {
        $this->typesListCode = $typesListCode;

        return $this;
    }
    public function getEntityName(): ?string
    {
        return $this->entityName;
    }
    public function getFullEntityName(): string
    {
        return 'App\\Entity\\' . $this->getEntityName();
    }
    public function getFullTypeClass(): ?string
    {
        return 'App\\Entity\\' . $this->getTypeClass();
    }
    public function setEntityName(string $entityName): self
    {
        // we need to map the relations and the related classes, e.g. ObjLot
        $entityName = match ($entityName) {
            'List' => 'Lst',
            'Collection' => 'Coll',
            'Object' => 'Obj',
//            'Entity' => 'Org',
            'User' => 'Account',
            'Occurrence' => 'Event',
            default => ucfirst((string) u($entityName)->camel()),
        };
        $this->entityName = $entityName;

        return $this;
    }
    public function getTableDefinition(): ?array
    {
        return $this->tableDefinition;
    }
    private function createField(array $fieldData, string $name, array $relatedMap, bool $isLabel=false): Field
    {
        $field = (new Field())
            ->setCore($this)
            ->setName($name)
            ->setCaFieldName($name)
            ->setData($fieldData)
            ->setDescription($fieldData['DESCRIPTION'])
            ->setCaFieldType($fieldData['FIELD_TYPE'])
            ->setCaDisplayType($fieldData['DISPLAY_TYPE'])
            ->setNullable($fieldData['IS_NULL'] || preg_match('/^(hier|icon)/', $name))
            ->setLabel($fieldData['LABEL'])
            ->setIsIdentity($fieldData['IDENTITY'] ?? false)
            ->setDefaultValue($fieldData['DEFAULT']??null)
            ->setDisplayHeight($fieldData['DISPLAY_HEIGHT']??null)
            ->setDisplayWidth($fieldData['DISPLAY_WIDTH']??null)
        ;
        $ft = $fieldData['FIELD_TYPE'];
        $dt = $fieldData['DISPLAY_TYPE'];
        $relatedMap['DT'][$dt]->addField($field);
        $relatedMap['FT'][$ft]->addField($field);

        if ($isLabel) {
            $field->setIsLabel(true);
        }

        // userList
        if (array_key_exists('LIST_CODE', $fieldData)) {
            $field->setTypesList($fieldData['LIST_CODE']);
        }
        // global, all fields (access and workflow status)
        if (array_key_exists('LIST', $fieldData)) {
//            $field->setTypesList($fieldData['LIST']);
            $field->setDictionary($fieldData['LIST']);
        }


        return $field;
    }
    public function setTableDefinition(array $data, array $relatedMap): self
    {
        $this
            ->setSingular($data['name'])
            ->setPlural($data['plural']);

        $data = (object) $data;

        // parse this to the individual fields.
//        $this->setTypesListCode($data->type); // ??
        foreach ($data->fields as $name => $fieldData) {
//            assert(is_int($fieldData['DISPLAY_WIDTH']), $fieldData['DISPLAY_WIDTH']);
//            assert($name <> 'list_id', json_encode($fieldData));
            $field = $this->createField($fieldData, $name, $relatedMap);
            // so that other tables that reference this can have the proper type.
            if ($field->getIsIdentity()) {
                $this->setIdentityFieldName($field->getName());
            }
            $this->addField($field);
        }

        // add the label fields, which will eventually be part of the translation behavior
        if (!empty($data->labels)) {
            foreach ($data->labels['FIELDS'] as $name => $fieldData) {
//            $field = $this->createField($fieldData, $name, true);
//            $this->addField($field);
            }
        }


        $this->setRelationships($data->relationships ?? null);
        $this->setTypesListDescription($data->types_list_description??null);
        unset($data->types_list_description);
        unset($data->fields);
        unset($data->relationships);
        // types_list_description
        $this->tableDefinition = (array) $data;
        return $this;
    }
    public function getFieldByName($name) {
        return $this->getFields()->filter(fn(Field $field) => $name === $field->getName())->first();
    }
    /**
     * @return Collection|Field[]
     *
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }
    public function addField(Field $field): self
    {
        if (!$this->fields->contains($field)) {
            $this->fields[] = $field;
            $field->setCore($this);
        }

        if ($this->getEntityName() == 'Obj') {
            assert($field->getName() <> 'listId', "why is this set for?");
            if ($field->getName() == 'listId') {
                dd($field, $this);
            }
        }

        return $this;
    }
    public function removeField(Field $field): self
    {
        if ($this->fields->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getCore() === $this) {
                $field->setCore(null);
            }
        }

        return $this;
    }
    public function getTypesListDescription(): ?string
    {
        return $this->typesListDescription;
    }
    public function setTypesListDescription(?string $typesListDescription): self
    {
        $this->typesListDescription = $typesListDescription;

        return $this;
    }
    public function getRelationships(): array
    {
        return $this->relationships ?: [];
    }
    public function setRelationships(?array $relationships): self
    {
        $this->relationships = $relationships;

        return $this;
    }
    public function getSingular(): ?string
    {
        return $this->singular;
    }
    public function setSingular(?string $singular): self
    {
        $singular = str_replace(' ', '_', $singular);
        // hacks specific to table name changes.
        $this->singular = $singular;


        return $this;
    }
    public function getPluralCamel()
    {
        return u($this->getPluralEntity())->camel();
    }
    public function getPlural(): ?string
    {
        return $this->plural;
    }
    public function setPlural(?string $plural): self
    {
        $plural = str_replace(' ', '_', $plural);
        $this->plural = $plural;

        return $this;
    }
    public function getEntityCode(): ?string
    {
        return $this->entityCode;
    }
    public function setEntityCode(?string $entityCode): self
    {
        $this->entityCode = $entityCode;

        return $this;
    }
    public function getRepoCode(): ?string
    {
        return $this->repoCode;
    }
    public function setRepoCode(?string $repoCode): self
    {
        $this->repoCode = $repoCode;

        return $this;
    }
    public function getIdentityFieldName(): ?string
    {
        return $this->identity_field_name;
    }
    public function setIdentityFieldName(?string $identity_field_name): self
    {
        $this->identity_field_name = $identity_field_name;

        return $this;
    }
    /**
     * @return Collection|SystemList[]
     */
    public function getSystemLists(): Collection
    {
        return $this->systemLists->filter(fn (SystemList $systemList) => $systemList->getUsedBy() === $this);
    }
    public function addSystemList(SystemList $systemList): self
    {
        if (!$this->systemLists->contains($systemList)) {
            $this->systemLists[] = $systemList;
            $systemList->setUsedBy($this);
        }

        return $this;
    }
    public function removeSystemList(SystemList $systemList): self
    {
        if ($this->systemLists->removeElement($systemList)) {
            // set the owning side to null (unless already changed)
            if ($systemList->getUsedBy() === $this) {
                $systemList->setUsedBy(null);
            }
        }

        return $this;
    }
    public function getSourceClass(): ?string
    {
        return $this->sourceClass;
    }
    public function setSourceClass(?string $sourceClass): self
    {
        $this->sourceClass = $sourceClass;

        return $this;
    }
    public function getTypeClass(): ?string
    {
        return $this->typeClass;
    }
    public function getTypesProperty(): ?string
    {
        return $this->getProperty('types');
    }
    public function setTypeClass(?string $typeClass): self
    {
        $this->typeClass = $typeClass;

        return $this;
    }
    public function getKeyTables(): ?array
    {
        return $this->keyTables;
    }
    public function getKeyClass($key): string
    {
        return $this->getKeyListMap()[$key];
    }
    public function setKeyTables(?array $keyTables): self
    {
        $this->keyTables = $keyTables;

        return $this;
    }
    public function getHelperMethod($category)
    {
        $helpers = [
            'types' => 'Type',
            'label_types' => 'LabelType',
            'sources' => 'Source',
            'statuses' => 'Status'
        ];
        return $helpers[$category];
    }
    public function getProperty($category)
    {
        $entityName =  u($this->getEntityName() . '_' . $category)->camel();
        return $entityName . ($category === 'statuses' ? 'es' : 's');
    }
    public function pluralize(string $relatedEntityName): string
    {
        $relatedEntityName = u($relatedEntityName)->camel();
        if (preg_match('/tatus$/', (string) $relatedEntityName)) {
            return $relatedEntityName . 'es';
        } elseif (preg_match('/y$/', (string) $relatedEntityName)) {
            return preg_replace('/y$', 'ies', $relatedEntityName);
        } else {
            return $relatedEntityName . 's';
        }
    }
    // set some properies so that Entity.php.twig can more easily generate helper methods.
    public function setRelatedTable(string $category, SystemList $sysList)
    {
        switch ($category) {
            case 'types':
                $this->setTypeClass($sysList->getEntityName());
                break;

        }
    }
    public function getHasIdno(): ?bool
    {
        return $this->hasIdno;
    }
    public function setHasIdno(?bool $hasIdno): self
    {
        $this->hasIdno = $hasIdno;

        return $this;
    }
    public function getHasParent(): ?bool
    {
        return $this->hasParent;
    }
    public function setHasParent(?bool $hasParent): self
    {
        $this->hasParent = $hasParent;

        return $this;
    }
    public function getUniqueIdentifiers(): array
    {
        return ['coreId' => $this->getEntityName()];
    }
    public function getCaLabelsTable(): ?string
    {
        return $this->caLabelsTable;
    }
    public function setCaLabelsTable(?string $caLabelsTable): self
    {
        $this->caLabelsTable = $caLabelsTable;

        return $this;
    }
    public function getRelatedClasses()
    {
        $classNames =  match ($this->getFullEntityName()) {
            Tour::class => [TourStop::class],
            Obj::class => [ObjectLot::class, ObjectRepresentation::class],
            Set::class => [SetItem::class],
            default => [],
        };
        return $classNames;
    }
    public function getDepenedency()
    {
        return match ($this->getFullEntityName()) {
            TourStop::class => Tour::class,
            SetItem::class => Set::class,
            ListItem::class => NestedList::class,
            EditorUIScreen::class => EditorUI::class,
            default => null,
        };
    }
    public function getPluralEntity()
    {
        // hack.
        return $this->getEntityName() . 's';
    }
    public function getPluralApiShortname(): string
    {
        return u($this->getPluralEntity())->snake();
    }
    public function create()
    {
        return (new $this->getFullEntityName());
    }
    public function getCode()
    {
        return u($this->getEntityName())->snake()->toString();
    }
    public function getTraitCode(): ?string
    {
        return $this->traitCode;
    }
    public function setTraitCode(?string $traitCode): self
    {
        $this->traitCode = $traitCode;

        return $this;
    }
    public function getKeyListMap(): ?array
    {
        return $this->keyListMap;
    }
    public function setKeyListMap(?array $keyListMap): self
    {
        $this->keyListMap = $keyListMap;

        return $this;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
    public function getCategoryCode(): ?string
    {
        return $this->categoryCode;
    }
    public function setCategoryCode(string $categoryCode): self
    {
        $this->categoryCode = $categoryCode;

        return $this;
    }
    public function hasType()
    {
        return !empty($this->getTypeClass());
    }
    public function getCaIdentityFieldName(): ?string
    {
        return u($this->getIdentityFieldName())->snake();
    }

    public function __toString()
    {
        return $this->getEntityName();
    }


}
