<?php

namespace Survos\Providence\XmlModel;


class ProfileRelationshipTableType implements XmlLabelsInterface, \Stringable
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public $default;
    public $subTypeLeft;
    public $subTypeRight;

    public function __toString(): string {
        return (string)$this->code;
    }

    protected const _TRANSLATION_KEY = 'rt';

    public function _label(): string { return sprintf("%s.%s", 'rt', $this->getCode()); }

    public function _typename(): ?string { return sprintf("%s.%s.typename", 'rel', $this->getCode()); }
    public function _typename_reverse(): ?string { return sprintf("%s.%s.typename_reverse", 'rel', $this->getCode()); }



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
