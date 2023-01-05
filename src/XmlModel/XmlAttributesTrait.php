<?php

namespace Survos\Providence\XmlModel;


use Symfony\Component\Serializer\Annotation\Groups;

trait XmlAttributesTrait
{
    private $attributes;
    public string $code; // or protected?
    public $v;
    public $default;
    public string|ProfileTypeRestrictions $typeRestrictions;
    public $typeRestrictionRight;
    public $includeSubtypes;
    public $includeSubtypesRight;
    public $includeSubtypesLeft;
    public $typeRestrictionLeft;

    public $lang;
    public $dontUseForCataloguing;
    public $country;
    public $preferred;
    public $value;
    public $rank;
    public $defaultSort;
    public $type = [];

    public $list = [];
    public $color;
    public $system;
    public $user;
    public $access;
    public $filepath;
    public $infoUrl;

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        foreach ($attributes as $var=>$val) {
//            dump($attributes, $var, $val, $this->{$var});

            try {
                if (str_contains($var, 'http:')) {
                    continue;
                }
                $this->{$var} = $val;
            } catch (\TypeError $error) {
                dd($error, $var, $val);
            }
            if (isset($this->{$var})) {
            } else {
                // add it?

            }
        }
//        dd($this, $attributes);
    }

//    #[Groups(['attributes'])]
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function _($attribute)
    {
        return $this->attributes[$attribute] ?? '!' . $attribute;
    }

    public function __toString()
    {
        return $this->code ?? $this::class;
    }

    // can't do this, because it's in XmlLabelTrait
//    public function getCode(): string
//    {
//        return $this->code;
//    }


}
