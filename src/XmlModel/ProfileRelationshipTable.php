<?php

namespace Survos\Providence\XmlModel;


class ProfileRelationshipTable implements \Stringable
{
    use XmlAttributesTrait;

    public ProfileRelationshipTableTypes $types;
    public $name;
    public string $code;

    public function __toString(): string {
        return (string) $this->name;
    }

    /** @return ProfileRelationshipTableType[] */
    public function getTypes() { return $this->types->type; }

    public function _label(): string { return sprintf("%s.%s", 'rel', $this->name); }

    public function findByCode($code): array
    {
        // use current(...) to get first
        return array_filter($this->types, fn(ProfileRelationshipTableType $e) => $e->code === $code);
    }

    public function left() { preg_match('/ca_(.*?)_x_(.*?)$/', (string) $this->name, $m); return $m[1]; }
    public function right() { preg_match('/ca_(.*?)_x_(.*?)$/', (string) $this->name, $m); return $m[2]; }

//    public function getCode() { return $this->code; }



}
