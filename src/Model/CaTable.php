<?php

namespace Survos\Providence\Model;

use App\Repository\CaTableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaTableRepository::class)]
class CaTable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    #[ORM\Column(type: 'integer')]
    private $caTableNum;
    #[ORM\Column(type: 'string', length: 255)]
    private $name;
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCaTableNum(): ?int
    {
        return $this->caTableNum;
    }
    public function setCaTableNum(int $caTableNum): self
    {
        $this->caTableNum = $caTableNum;

        return $this;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
