<?php

namespace Survos\Providence\XmlModel;

use Symfony\Component\String\Slugger\AsciiSlugger;

class ProfileLabel implements \Stringable
{
    use XmlAttributesTrait;

    public $locale;
    public ?string $name = null;
    public $description;
    public $name_singular;
    public $name_plural;
    public $typename;
    public $typename_reverse;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
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
