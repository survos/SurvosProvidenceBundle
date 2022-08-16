<?php

namespace Survos\Providence\XmlModel;

use Survos\Providence\Repository\ProfileUserInterfaceRepository;
use Doctrine\ORM\Mapping as ORM;

class ProfileUserInterface implements XmlLabelsInterface
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    public const LABEL_PREFIX = 'ui';

    public $type;

    public ProfileScreens $screens;

    /** @returns ProfileScreen[] */
    public function getScreens(): array
    {
        return $this->screens->screen;
    }

    public function _label(): string { return sprintf("%s.%s", 'ui', $this->getCode()); }


}
