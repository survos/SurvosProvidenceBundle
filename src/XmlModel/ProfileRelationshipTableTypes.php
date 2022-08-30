<?php

namespace Survos\Providence\XmlModel;


use Symfony\Component\Serializer\Annotation\Groups;

class ProfileRelationshipTableTypes
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    /** @var ProfileRelationshipTypes[] */
    #[Groups('relationship')]
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
