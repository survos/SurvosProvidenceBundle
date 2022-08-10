<?php

namespace Survos\Providence\XmlModel;

class ProfileDisplay
{
    use XmlAttributesTrait;

//    public $idno;
//    public $enabled;
//    public $default;
//    /** @var $display ProfileDisplay[]  */
//    public array $display=[];
    public ProfileLabels $labels;
    public ProfileBundlePlacements $bundle;

    // in the <display> element, type restrictions are a comma-delimited attribute, not an element.
//    public ProfileTypeRestrictions $typeRestrictions;
    public string $typeRestrictions;
    public ProfileGroupAccess $groupAccess;

    public ProfileSettings $settings;

}
