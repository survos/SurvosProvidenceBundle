<?php

namespace Survos\Providence\XmlModel;


class ProfileRelationshipTableType
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public $code;
    public $default;
    public $subTypeLeft;
    public $subTypeRight;

    public function __toString(): string {
        return (string)$this->code;
    }

    public function _label() { return sprintf("%s.%s", 'rt', $this->getCode()); }

    public function _typename() { return sprintf("%s.%s.typename", 'rel', $this->getCode()); }
    public function _typename_reverse() { return sprintf("%s.%s.typename_reverse", 'rel', $this->getCode()); }



//    public string $table;
//    public ProfileSettings $settings;
//    public $type;
//
//    public $includeSubtypes;
//    public ProfileBundlePlacements $bundlePlacements;
//
//    /** @var ProfileRestrictions[] */
//    public $restriction = [];
//    public $screen = [];

}
