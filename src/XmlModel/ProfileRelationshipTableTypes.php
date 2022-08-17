<?php

namespace Survos\Providence\XmlModel;


class ProfileRelationshipTableTypes
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    /** @var ProfileRelationshipTypes[] */
    public $type = [];
    public string $subTypeLeft;
    public string $subTypeRight;



//    public function getTypes()
//    {
//        return $this->rtypes;
//    }





//    public string $table;
//    public ProfileSettings $settings;
//    public $type;
//
//    public $includeSubtypes;
//    public ProfileBundlePlacements $bundlePlacements;
//
//    /** @var ProfileRestrictions[] */
//    public $restriction = [];
//    public $screen = [];

}
