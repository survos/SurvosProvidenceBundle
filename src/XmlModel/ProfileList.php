<?php

namespace Survos\Providence\XmlModel;

class ProfileList implements XmlLabelsInterface
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public $hierarchical;
    public $system;
    public $vocabulary;

//    public ProfileLabels $labels;

    public ?ProfileItems $items=null;

    public function getItems(): ?array
    {
        return $this->items ? $this->items->item: [];
    }

//    public function setItems(array $items): self
//    {
//        $this->items = $items;
//        return $this;
//    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function _label(): string { return sprintf("%s.%s", 'list', $this->getCode()); }


}
