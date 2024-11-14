<?php

namespace Survos\Providence\XmlModel;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ProfileLabel implements \Stringable
{
    use XmlAttributesTrait;

    #[Groups(['labels'])]
    public $locale;
    #[Groups(['labels'])]
    public string|null $name = null;
    #[Groups(['labels'])]
    public $description;
    #[Groups(['labels'])]
    public $name_singular;
    #[Groups(['labels'])]
    public $name_plural;
    #[Groups(['labels'])]
    public $typename;
    #[Groups(['labels'])]
    public $typename_reverse;

    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name ?: $this->name_singular;
    }

    public function getCode()
    {
        $slugger = new AsciiSlugger();
        return $slugger->slug($this->getName())->ascii()->toString();
    }

    public function __toString(): string
    {
        return (string) ($this->getName() ?: '(empty)'); // json_encode($this); // '??'; // $this->getCode();
    }
}
