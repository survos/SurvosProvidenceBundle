<?php

namespace Survos\Providence\XmlModel;

use Survos\Providence\Repository\ProfileSettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity(repositoryClass=ProfileSettingRepository::class)
 */
class ProfileSetting implements \Stringable
{
    use XmlAttributesTrait;
    public $_value; // e.g. <setting name="min_value">12</setting>
    public $name;
    public $locale;

    public function __toString(): string
    {
        return $this->_value ?: $this->attributes['v'] ?: '~~';

    }

}
