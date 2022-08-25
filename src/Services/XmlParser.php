<?php

declare(strict_types=1);

namespace Survos\Providence\Services;

use Survos\Providence\XmlModel\ProfileBundlePlacements;
use Survos\Providence\XmlModel\ProfileDisplay;
use Survos\Providence\XmlModel\ProfileDisplays;
use Survos\Providence\XmlModel\ProfileElements;
use Survos\Providence\XmlModel\ProfileElementSets;
use Survos\Providence\XmlModel\ProfileLocale;
use Survos\Providence\XmlModel\ProfileLocales;
use Survos\Providence\XmlModel\XmlProfile;
use Survos\Providence\XmlModel\ProfileGroupAccess;
use Survos\Providence\XmlModel\ProfileItem;
use Survos\Providence\XmlModel\ProfileItems;
use Survos\Providence\XmlModel\ProfileLabel;
use Survos\Providence\XmlModel\ProfileLabels;
use Survos\Providence\XmlModel\ProfileList;
use Survos\Providence\XmlModel\ProfileLists;
use Survos\Providence\XmlModel\ProfileMetaDataElement;
use Survos\Providence\XmlModel\ProfilePermission;
use Survos\Providence\XmlModel\ProfilePlacement;
use Survos\Providence\XmlModel\ProfileRelationshipTable;
use Survos\Providence\XmlModel\ProfileRelationshipTableTypes;
use Survos\Providence\XmlModel\ProfileRelationshipTableType;
use Survos\Providence\XmlModel\ProfileRelationshipTypes;
use Survos\Providence\XmlModel\ProfileRestrictions;
use Survos\Providence\XmlModel\ProfileScreen;
use Survos\Providence\XmlModel\ProfileScreens;
use Survos\Providence\XmlModel\ProfileSetting;
use Survos\Providence\XmlModel\ProfileSettings;
use Survos\Providence\XmlModel\ProfileTypeRestrictions;
use Survos\Providence\XmlModel\ProfileUserInterface;
use Survos\Providence\XmlModel\ProfileUserInterfaces;

use Sabre\Xml\Reader;
use Sabre\Xml\Writer;

/**
 * The Atom Service object gives you quick access to the writer and reader.
 *
 * See \Sabre\Xml\Service for all the utility methods.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/
 */
class XmlParser extends \Sabre\Xml\Service
{
    final public const CA_NS = ''; // http://www.w3.org/2005/Atom';

    final public const CA_DEFAULT_PREFIX = 'profile';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->namespaceMap[self::CA_NS] = self::CA_DEFAULT_PREFIX;

        $ca = '{' . self::CA_NS . '}';

        // The following elements are all simple xml elements, and we can use
        // the VO system for mapping from PHP object to XML element.
        $this->mapValueObject($ca . 'profile', XmlProfile::class);

        $this->mapValueObject($ca . 'lists', ProfileLists::class);
        $this->mapValueObject($ca . 'list', ProfileList::class);

        $this->mapValueObject($ca . 'locales', ProfileLocales::class);
        $this->mapValueObject($ca . 'locale', ProfileLocale::class);

        $this->mapValueObject($ca . 'labels', ProfileLabels::class);
        $this->mapValueObject($ca . 'label', ProfileLabel::class);

        $this->mapValueObject($ca . 'displays', ProfileDisplays::class);
        $this->mapValueObject($ca . 'display', ProfileDisplay::class);

        $this->mapValueObject($ca . 'items', ProfileItems::class);
        $this->mapValueObject($ca . 'item', ProfileItem::class);

        $this->mapValueObject($ca . 'screens', ProfileScreens::class);
        $this->mapValueObject($ca . 'screen', ProfileScreen::class);

        $this->mapValueObject($ca . 'elementSets', ProfileElementSets::class);
        $this->mapValueObject($ca . 'elements', ProfileElements::class);
        $this->mapValueObject($ca . 'metadataElement', ProfileMetaDataElement::class);

        $this->mapValueObject($ca . 'typeRestrictions', ProfileTypeRestrictions::class);
        $this->mapValueObject($ca . 'restriction', ProfileRestrictions::class);

        $this->mapValueObject($ca . 'relationshipTypes', ProfileRelationshipTypes::class);
        $this->mapValueObject($ca . 'relationshipTable', ProfileRelationshipTable::class);

        $this->mapValueObject($ca . 'types', ProfileRelationshipTableTypes::class);
        $this->mapValueObject($ca . 'type', ProfileRelationshipTableType::class);

        $this->mapValueObject($ca . 'userInterfaces', ProfileUserInterfaces::class);
        $this->mapValueObject($ca . 'userInterface', ProfileUserInterface::class);

        $this->mapValueObject($ca . 'bundlePlacements', ProfileBundlePlacements::class);
        $this->mapValueObject($ca . 'placement', ProfilePlacement::class);

        $this->mapValueObject($ca . 'groupAccess', ProfileGroupAccess::class);
        $this->mapValueObject($ca . 'permission', ProfilePermission::class);

        // settings may need its own handler
        $this->mapValueObject($ca . 'settings', ProfileSettings::class);
        $this->mapValueObject($ca . 'setting', ProfileSetting::class);
    }

    // from the parent
    public function mapValueObject(string $elementName, string $className): void
    {
        [$namespace] = self::parseClarkNotation($elementName);

        $this->elementMap[$elementName] = function (Reader $reader) use ($className, $namespace) {
            $properties = [];
//
//            if ($className == ProfileSettings::class) {
//                $o = \Sabre\Xml\Deserializer\valueObject($reader, $className, $namespace);
//            }
            $properties = $reader->parseAttributes();
            $o = \Sabre\Xml\Deserializer\valueObject($reader, $className, $namespace);

//            if ($className == ProfileSetting::class) {
//                if ($reader->name === '#text') {
//                    $_value = $reader->value;
            ////                    dd($o, $properties, $className, $reader, $reader->name, $_value);
//                }
//            }

            if (count($properties))
            {
//                if (array_key_exists('fieldWidth', $properties)) {
//                    dd($properties, $o);
//                }
                $o->setAttributes($properties);
            }

            return $o;
        };
        $this->classMap[$className] = fn(Writer $writer, $valueObject) => \Sabre\Xml\Serializer\valueObject($writer, $valueObject, $namespace);
        $this->valueObjectMap[$className] = $elementName;
    }
}
