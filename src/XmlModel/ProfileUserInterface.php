<?php

namespace Survos\Providence\XmlModel;

use Survos\Providence\Repository\ProfileUserInterfaceRepository;
use Doctrine\ORM\Mapping as ORM;

class ProfileUserInterface
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public $code;
    public $type;

    public ProfileScreens $screens;

    /** @returns ProfileScreen[] */
    public function getScreens(): array
    {
        return $this->screens->screen;
    }

    public function _label() { return sprintf("%s.%s", 'ui', $this->getCode()); }


}
