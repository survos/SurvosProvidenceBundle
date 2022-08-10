<?php

namespace Survos\Providence\XmlModel;


class ProfileRelationshipTypes
{
    use XmlAttributesTrait;
    /** @var ProfileRelationshipTable[] */
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
