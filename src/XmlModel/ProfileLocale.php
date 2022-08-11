<?php

namespace Survos\Providence\XmlModel;

class ProfileLocale
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public function __toString(): string
    {
        return $this->code;
    }

    public function _label() { return sprintf("%s.%s", 'list', $this->getCode()); }


}
