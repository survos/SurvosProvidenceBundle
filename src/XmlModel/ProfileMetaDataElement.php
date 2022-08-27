<?php

namespace Survos\Providence\XmlModel;

class ProfileMetaDataElement implements XmlLabelsInterface
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public string $code;
    public string $datatype;
    public ?string $documentationUrl=null;
    public ProfileSettings $settings;
    public ProfileTypeRestrictions $typeRestrictions;
    public ProfileElements $elements;
    public $metadataElement = [];

    public function __construct()
    {
        $this->settings = new ProfileSettings();
    }

    public function _label(): string { return sprintf("%s.%s", 'mde', $this->getCode()); }
    public function getSettings() { return $this->settings->setting; }
    public function getElements() { return $this->elements->metadataElement; }

    /** @return ProfileTypeRestrictions[] */
    public function getTypeRestrictions(): array {
        return $this->typeRestrictions->restriction;
//        return $this->typeRestrictions ? $this->typeRestrictions->restriction: [];
    }


//    public function setCode($str) { dd($str); }
    public function getCode() {
//        assert($this->code, "No code in element");
        return $this->code;
    }

    public function isContainer(): bool
    {
        return $this->datatype == 'Container';
    }

}
