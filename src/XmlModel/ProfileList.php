<?php

namespace Survos\Providence\XmlModel;

class ProfileList
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public $code;
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

    public function _label() { return sprintf("%s.%s", 'list', $this->getCode()); }


}