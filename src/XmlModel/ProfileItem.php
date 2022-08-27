<?php

namespace Survos\Providence\XmlModel;

class ProfileItem implements XmlLabelsInterface
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public $idno;
    public $enabled;
    public $default;
//    public array $items = [];
    public ProfileSettings $settings;
    public ?ProfileItems $items=null;

    public function getItems(): ?array
    {
        return $this->items ? $this->items->item: [];
    }

    public function _t(ProfileList $list): string
    {
        return sprintf("%s.%s.%s", 'items', $list->code, $this->idno);
    }

    public function getCode()
    {
        return $this->idno;
    }


}
