<?php

namespace Survos\Providence\XmlModel;

class ProfileSettings
{
    /** @var ProfileSetting[] */
    public $setting = [];

    /**
     * @return array<int|string, mixed>
     */
    public function asArray(): array {
        $x = [];
        foreach ($this->setting as $setting) {
            $x[$setting->name] = $setting->v ?? $setting->_value ?: null;
        }
        return $x;
    }
}
