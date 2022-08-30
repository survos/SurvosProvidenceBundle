<?php

namespace Survos\Providence\XmlModel;

use Symfony\Component\Serializer\Annotation\Groups;

class ProfileElementSets
{
    use XmlAttributesTrait;

    /** @var ProfileMetaDataElement[]  */
    #[Groups('mde')]
    public $metadataElement = [];
    public ProfileUserInterfaces $userInterfaces;
//    public $relationshipTypes;


}
