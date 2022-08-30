<?php

namespace Survos\Providence\Model;

use App\Repository\FieldDisplayTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Survos\BaseBundle\Entity\SurvosBaseEntity;

#[ORM\Entity(repositoryClass: FieldDisplayTypeRepository::class)]
class FieldDisplayType implements \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32)]
    private $code;
    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }
    /**
     * @return FieldDisplayType
     */
    public function setCode(mixed $code)
    {
        $this->code = $code;
        return $this;
    }
    #[ORM\OneToMany(mappedBy: 'fieldDisplayType', targetEntity: Field::class, orphanRemoval: true)]
    private $fields;
    #[ORM\Column(type: 'string', length: 255)]
    private $name;
    #[ORM\Column(type: 'integer', nullable: true)]
    private $caId;
    public function __construct()
    {
        $this->fields = new ArrayCollection();
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
            $field->setFieldDisplayType($this);
        }

        return $this;
    }
    public function removeField(Field $field): self
    {
        if ($this->fields->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getFieldDisplayType() === $this) {
                $field->setFieldDisplayType(null);
            }
        }

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
    public function getCaId(): ?int
    {
        return $this->caId;
    }
    public function setCaId(?int $caId): self
    {
        $this->caId = $caId;

        return $this;
    }
    public function __toString(): string
    {
        return (string) $this->getName();
    }
    public function getUniqueIdentifiers(): array
    {
        return ['fieldTypeId' => $this->getName()];
    }
}
