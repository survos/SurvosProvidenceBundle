<?php

namespace Survos\Providence\Model;

use App\Repository\FieldTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Survos\BaseBundle\Entity\SurvosBaseEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FieldTypeRepository::class)]
class FieldType implements \Stringable
{
    final const AC_NUMBER='number';
    final const AC_TEXT='text';
    final const AC_TIMESTAMP='timestamp';
    final const AC_DATETIME='datetime';
    final const AC_HISTORIC_DATETIME='historic_datetime';
    final const AC_DATERANGE='daterange';
    final const AC_HISTORIC_DATERANGE='historic_daterange';
    final const AC_BIT='bit';
    final const AC_FILE='file';
    final const AC_MEDIA='media';
    final const AC_PASSWORD='password';
    final const AC_VARS='vars';
    final const AC_TIMECODE='timecode';
    final const AC_DATE='date';
    final const AC_HISTORIC_DATE='historic_date';
    final const AC_TIME='time';
    final const AC_TIMERANGE='timerange';
    final const AC_TEXTAREA='textarea';
    final const AC_RELATION='relationship';
    final const AC_NESTED_LIST_ITEM='nested_list_item';
    #[Groups(['spreadsheet'])]
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private string $code;
    /**
     * @return mixed
     */
    public function getCode(): string
    {
        return $this->code;
    }
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
    #[ORM\OneToMany(targetEntity: Field::class, mappedBy: 'fieldType', orphanRemoval: true)]
    private $field;
    #[Groups(['spreadsheet'])]
    #[ORM\Column(type: 'string', length: 255)]
    private $name;
    #[ORM\Column(type: 'integer', nullable: true)]
    private $caId;
    // removed for Profile ??  hack
    /**
     * @ ORM\OneToMany(targetEntity=CustomFieldType::class, mappedBy="fieldType")
     */
    private $customFieldTypes;
    public function __construct()
    {
        $this->field = new ArrayCollection();
        $this->customFieldTypes = new ArrayCollection();
    }
    /**
     * @return Collection|Field[]
     */
    public function getField(): Collection
    {
        return $this->field;
    }
    public function addField(Field $field): self
    {
        if (!$this->field->contains($field)) {
            $this->field[] = $field;
            $field->setFieldType($this);
        }

        return $this;
    }
    public function removeField(Field $field): self
    {
        if ($this->field->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getFieldType() === $this) {
                $field->setFieldType(null);
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
        return $this->getCode();
    }
    public function getUniqueIdentifiers(): array
    {
        return ['fieldTypeId' => $this->getName()];
    }
    /**
     * @return Collection|CustomFieldType[]
     */
    public function getCustomFieldTypes(): Collection
    {
        return $this->customFieldTypes;
    }
    public function addCustomFieldType(CustomFieldType $customFieldType): self
    {
        if (!$this->customFieldTypes->contains($customFieldType)) {
            $this->customFieldTypes[] = $customFieldType;
            $customFieldType->setFieldType($this);
        }

        return $this;
    }
    public function removeCustomFieldType(CustomFieldType $customFieldType): self
    {
        if ($this->customFieldTypes->removeElement($customFieldType)) {
            // set the owning side to null (unless already changed)
            if ($customFieldType->getFieldType() === $this) {
                $customFieldType->setFieldType(null);
            }
        }

        return $this;
    }
}
