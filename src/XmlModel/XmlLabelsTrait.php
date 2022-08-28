<?php

namespace Survos\Providence\XmlModel;


use Symfony\Component\Serializer\Annotation\Groups;

trait XmlLabelsTrait
{
    public ProfileLabels $labels;
    #[Groups(['labels'])]
    public function getLabels() { return $this->labels->label; }

    public function _label(): string { return sprintf("%s.%s", 'label', $this->getCode()); }
    public function _description(): string { return $this->_label() . '.description'; }
    public function _typename(): ?string { return null; }
    public function _typename_reverse(): ?string { return null; }


    private bool $hasDescription = false;
    // override if idno or something else.
    #[Groups(['profile'])]
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
