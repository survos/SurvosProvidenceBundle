<?php

namespace Survos\Providence\Model;

use App\Repository\SystemListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: SystemListRepository::class)]
class SystemList implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    #[ORM\Column(type: 'string', length: 255)]
    private $code;
    #[ORM\Column(type: 'string', length: 255)]
    private $description;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isTree;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isOneAndOnlyOne;
    #[ORM\Column(type: 'text', nullable: true)]
    private $repoCode;
    #[ORM\Column(type: 'text', nullable: true)]
    private $entityCode;
    #[ORM\Column(type: 'string', length: 255)]
    private $entityName;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isLabel;
    #[ORM\OneToMany(targetEntity: Field::class, mappedBy: 'systemList')]
    private $fields;
    #[ORM\ManyToOne(targetEntity: Core::class, inversedBy: 'systemLists')]
    private $usedBy;
    #[ORM\Column(type: 'string', length: 255)]
    private $category;
    #[ORM\Column(type: 'text', nullable: true)]
    private $traitCode;
    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCode(): ?string
    {
        return $this->code;
    }
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
    public function getIsTree(): ?bool
    {
        return $this->isTree;
    }
    public function setIsTree(?bool $isTree): self
    {
        $this->isTree = $isTree;

        return $this;
    }
    public function getIsOneAndOnlyOne(): ?bool
    {
        return $this->isOneAndOnlyOne;
    }
    public function setIsOneAndOnlyOne(?bool $isOneAndOnlyOne): self
    {
        $this->isOneAndOnlyOne = $isOneAndOnlyOne;

        return $this;
    }
    public function getRepoCode(): ?string
    {
        return $this->repoCode;
    }
    public function setRepoCode(?string $repoCode): self
    {
        $this->repoCode = $repoCode;

        return $this;
    }
    public function getEntityCode(): ?string
    {
        return $this->entityCode;
    }
    public function setEntityCode(?string $entityCode): self
    {
        $this->entityCode = $entityCode;

        return $this;
    }
    public function getEntityName(): ?string
    {
        return $this->entityName;
    }
    public function setEntityName(string $entityName): self
    {
        $this->entityName = $entityName;

        return $this;
    }
    public function getIsLabel(): ?bool
    {
        return $this->isLabel;
    }
    public function setIsLabel(?bool $isLabel): self
    {
        $this->isLabel = $isLabel;

        return $this;
    }
    /**
     * @return Collection|Field[]
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }
    public function addField(Field $field): self
    {
        if (!$this->fields->contains($field)) {
            $this->fields[] = $field;
            $field->setSystemList($this);
        }

        return $this;
    }
    public function removeField(Field $field): self
    {
        if ($this->fields->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getSystemList() === $this) {
                $field->setSystemList(null);
            }
        }

        return $this;
    }
    public function singular()
    {
        $singular = $this->getEntityName();
        $singular = preg_replace('/s$/', '', $singular);
        return u($singular)->camel();
    }
    public function getUsedBy(): ?Core
    {
        return $this->usedBy;
    }
    public function setUsedBy(?Core $usedBy): self
    {
        $this->usedBy = $usedBy;

        return $this;
    }
    public function getCategory(): ?string
    {
        return $this->category;
    }
    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }
    public function __toString(): string
    {
        return $this->getCode();
    }
    public function getTraitCode(): ?string
    {
        return $this->traitCode;
    }
    public function setTraitCode(?string $traitCode): self
    {
        $this->traitCode = $traitCode;

        return $this;
    }
}
