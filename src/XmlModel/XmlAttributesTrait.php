<?php

namespace Survos\Providence\XmlModel;


use Symfony\Component\Serializer\Annotation\Groups;

trait XmlAttributesTrait
{
    private $attributes;
    public string $code; // or protected?

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        foreach ($attributes as $var=>$val) {
//            dump($attributes, $var, $val, $this->{$var});

            try {
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
