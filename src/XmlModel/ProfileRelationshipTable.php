<?php

namespace Survos\Providence\XmlModel;


class ProfileRelationshipTable
{
    use XmlAttributesTrait;

    /** @var ProfileRelationshipTableType */
    public ProfileRelationshipTableTypes $types;
    public $name;
    public string $code;

    public function __toString() {
        return $this->name;
    }

    /** @return ProfileRelationshipTableType[] */
    public function getTypes() { return $this->types->type; }

    public function _label() { return sprintf("%s.%s", 'rel', $this->getCode()); }

    public function findByCode($code): array
    {
        // use current(...) to get first
        return array_filter($this->types, fn(ProfileRelationshipTableType $e) => $e->code === $code);
    }

    public function left() { preg_match('/ca_(.*?)_x_(.*?)$/', $this->name, $m); return $m[1]; }
    public function right() { preg_match('/ca_(.*?)_x_(.*?)$/', $this->name, $m); return $m[2]; }

//    public function getCode() { return $this->code; }



}
