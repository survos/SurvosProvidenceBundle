<?php

namespace Survos\Providence\XmlModel;


class ProfileRestrictions
{
    use XmlAttributesTrait;

    public string $table;
    public ProfileSettings $settings;
//    public $type;
//
//    public $includeSubtypes;
//    public ProfileBundlePlacements $bundlePlacements;
//
//    /** @var ProfileRestrictions[] */
//    public $restriction = [];
//    public $screen = [];

    public function getCode()
    {
        return $this->code ?: 'T.' . $this->table;
    }

}
