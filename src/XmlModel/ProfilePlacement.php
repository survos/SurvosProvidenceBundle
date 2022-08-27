<?php

namespace Survos\Providence\XmlModel;

use Survos\Providence\Repository\ProfilePlacementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity(repositoryClass=ProfilePlacementRepository::class)
 */
class ProfilePlacement
{
    use XmlAttributesTrait;
    public ProfileSettings $settings;
    public ProfileScreen $screen; // parent, not sure if this automatically sets.  I guess we could set it afterwards.
    public $bundle;
    private ?array $field = null;

    /**
     * @return mixed
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @return ProfilePlacement
     */
    public function setField(mixed $field) {
        $this->field = $field;
        return $this;
    }

    public function __construct()
    {
        $this->settings = new ProfileSettings();
    }

    public function getSettings(): array
    {
        return $this->settings->setting;
    }

    public function getSettingsByName(): array
    {
        $x = [];
        foreach ($this->getSettings() as $setting) {
            $x[$setting->name] = $setting;
        }
        return $x;
    }
}
