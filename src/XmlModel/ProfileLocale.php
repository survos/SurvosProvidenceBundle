<?php

namespace Survos\Providence\XmlModel;

class ProfileLocale
{
    use XmlAttributesTrait;
//    use XmlLabelsTrait;

    public function __toString(): string
    {
//        if (!isset($this->code)) { dd($this); }
        return sprintf('%s_%s', $this->lang, $this->country); // $this->code;
    }

    public function _label() { return sprintf("%s.%s", 'list', $this->getCode()); }


}
