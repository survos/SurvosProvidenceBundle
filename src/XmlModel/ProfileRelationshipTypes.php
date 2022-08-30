<?php

namespace Survos\Providence\XmlModel;


use Symfony\Component\Serializer\Annotation\Groups;

class ProfileRelationshipTypes implements \Stringable
{
    use XmlAttributesTrait;
    /** @var ProfileRelationshipTable[] */
    #[Groups('relationship')]
    public $relationshipTable = [];



//    /** @return ProfileRelationshipTableType[] */
//    public function getTypes(): array
//    {
//        return $this->relationshipTable->types;
//    }

    public function __toString(): string {
        return __METHOD__;
    }




}
