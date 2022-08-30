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
    #[Groups('relationship')]
    public ?string $subTypeLeft=null;
    #[Groups('relationship')]
    public ?string $subTypeRight=null;

    /**
     * @return string|null
     */
    public function getSubTypeLeft(): ?string
    {
        return $this->subTypeLeft;
    }

    /**
     * @param string|null $subTypeLeft
     * @return ProfileRelationshipTableTypes
     */
    public function setSubTypeLeft(?string $subTypeLeft): ProfileRelationshipTableTypes
    {
        $this->subTypeLeft = $subTypeLeft;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubTypeRight(): ?string
    {
        return $this->subTypeRight;
    }

    /**
     * @param string|null $subTypeRight
     * @return ProfileRelationshipTableTypes
     */
    public function setSubTypeRight(?string $subTypeRight): ProfileRelationshipTableTypes
    {
        $this->subTypeRight = $subTypeRight;
        return $this;
    }



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
