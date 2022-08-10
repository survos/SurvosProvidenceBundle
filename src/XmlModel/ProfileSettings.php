<?php

namespace Survos\Providence\XmlModel;

class ProfileSettings
{
    /** @var ProfileSetting[] */
    public $setting = [];

    public function asArray() {
        $x = [];
        foreach ($this->setting as $setting) {
            $x[$setting->name] = $setting->v ?? $setting->_value ?: null;
        }
        return $x;
    }
}
