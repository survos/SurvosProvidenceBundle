<?php

namespace Survos\Providence\XmlModel;


trait XmlLabelsTrait
{
    public ProfileLabels $labels;
    public function getLabels() { return $this->labels->label; }

    public function _label() { return sprintf("%s.%s", 'label', $this->getCode()); }
    public function _description() { return $this->_label() . '.description'; }

    private $hasDescription = false;
    // override if idno or something else.
    public function getCode()
    {
        return $this->code;
    }

    public function hasDescription(): bool
    {
        return $this->hasDescription;
    }

    public function setHasDescription(bool $hasDescription): self
    {
        $this->hasDescription = $hasDescription;
        return $this;
    }






//    public function setLabels(array $labels)
//    {
//        $this->labels = $labels;
//    }


}
