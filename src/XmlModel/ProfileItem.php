<?php

namespace Survos\Providence\XmlModel;

use Symfony\Component\Serializer\Annotation\Groups;

class ProfileItem implements XmlLabelsInterface
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    #[Groups(['lists'])]
    public $idno;
    #[Groups(['lists'])]
    public $enabled;
    #[Groups(['lists'])]
    public $default;
//    public array $items = [];
    public ProfileSettings $settings;
    #[Groups(['lists'])]
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
