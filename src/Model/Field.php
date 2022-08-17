<?php

// the CORE fields as defined by CA.

namespace Survos\Providence\Model;

use App\Repository\FieldRepository;
use Doctrine\ORM\Mapping as ORM;
use Survos\BaseBundle\Entity\SurvosBaseEntity;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: FieldRepository::class)]
class Field implements RouteParametersInterface
{
    use RouteParametersTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    #[ORM\ManyToOne(targetEntity: Core::class, inversedBy: 'fields', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'SET NULL')]
    private $core;
    #[ORM\Column(type: 'string', length: 255)]
    private $name;
    #[ORM\Column(type: 'array')]
    private array $data = [];
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $nullable;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isIdentity;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $defaultValue;
    #[ORM\Column(type: 'integer')]
    private $caFieldType;
    #[ORM\Column(type: 'integer')]
    private $caDisplayType;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $label;
    #[ORM\Column(type: 'string', nullable: true)]
    private $displayWidth;
    #[ORM\Column(type: 'string', nullable: true)]
    private $displayHeight;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isLabel;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $doctrineType;
    #[ORM\Column(type: 'integer', nullable: true)]
    private $maxLength;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $typesList;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $dictionary;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $target;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $inverse;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mappedBy;
    #[ORM\Column(type: 'string', length: 255)]
    private $caFieldName;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $dbType;
    #[ORM\ManyToOne(targetEntity: SystemList::class, inversedBy: 'fields')]
    private $systemList;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isIgnored;
    #[ORM\ManyToOne(targetEntity: FieldType::class, inversedBy: 'field', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'code')]
    private $fieldType;
    #[ORM\ManyToOne(targetEntity: FieldDisplayType::class, inversedBy: 'field', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'code', fieldName: 'code')]
    private $fieldDisplayType;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $example;
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCore(): ?Core
    {
        return $this->core;
    }
    public function setCore(?Core $core): self
    {
        $this->core = $core;

        return $this;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $name = u($name)->camel()->toString();
//        assert($name <> 'list_item_list_items');
        $this->name = $name;
        if ($this->getCore()) {
            assert((in_array($this->getCore()->getCaTable(), ['metadata_elements', 'list_items', 'lists'])) || ($name <> 'listId'), "adding listId in " . $this->getCore()->getCaTable());
        }

        return $this;
    }
    public function getData(): ?array
    {
        return $this->data;
    }
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
    public function getNullable(): ?bool
    {
        return $this->nullable;
    }
    public function setNullable(?bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }
    public function getIsIdentity(): ?bool
    {
        return $this->isIdentity;
    }
    public function setIsIdentity(?bool $isIdentity): self
    {
        $this->isIdentity = $isIdentity;

        return $this;
    }
    public function getDefaultValue()
    {
        $defaultValue = $this->defaultValue;
        if ($this->getNullable() && empty($this->defaultValue)) {
            return 'null';
        }
        return $this->getDbType() == 'boolean' ? (bool)$defaultValue : ($this->getDbType() == 'integer' ? (int) $defaultValue : $defaultValue);
    }
    public function setDefaultValue(?string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }
    public function getCaFieldType(): ?int
    {
        return $this->caFieldType;
    }
    public function setCaFieldType(int $caFieldType): self
    {
        $this->caFieldType = $caFieldType;

        return $this;
    }
    public function getCaDisplayType(): ?int
    {
        return $this->caDisplayType;
    }
    public function setCaDisplayType(int $caDisplayType): self
    {
        $this->caDisplayType = $caDisplayType;

        return $this;
    }
    public function getLabel(): ?string
    {
        return $this->label;
    }
    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
    public function getDisplayWidth(): ?string
    {
        return $this->displayWidth;
    }
    public function setDisplayWidth(?string $displayWidth): self
    {
        $this->displayWidth = $displayWidth;

        return $this;
    }
    public function getDisplayHeight(): ?int
    {
        return $this->displayHeight;
    }
    public function setDisplayHeight(?int $displayHeight): self
    {
        $this->displayHeight = $displayHeight;

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
    public function getDoctrineType(): ?string
    {
        return $this->doctrineType;
    }
    public function setDoctrineType(string $doctrineType): self
    {
        assert(strlen($doctrineType), $doctrineType);
        $this->doctrineType = $doctrineType;

        return $this;
    }
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }
    public function setMaxLength(?int $maxLength): self
    {
        $this->maxLength = $maxLength;

        return $this;
    }
    public function getTypesList(): ?string
    {
        return $this->typesList;
    }
    public function setTypesList(?string $typesList): self
    {
        $this->typesList = $typesList;

        return $this;
    }
    public function getDictionary(): ?string
    {
        return $this->dictionary;
    }
    public function setDictionary(?string $dictionary): self
    {
        $this->dictionary = $dictionary;

        return $this;
    }
    public function getTarget(): ?string
    {
        return $this->target;
    }
    public function setTarget(?string $target): self
    {
        $this->target = $target;

        return $this;
    }
    public function getInverse(): ?string
    {
        return $this->inverse;
    }
    public function setInverse(?string $inverse): self
    {
        $this->inverse = $inverse;

        return $this;
    }
    public function getMappedBy(): ?string
    {
        return $this->mappedBy;
    }
    public function setMappedBy(?string $mappedBy): self
    {
        $this->mappedBy = $mappedBy;

        return $this;
    }
    public function getCaFieldName(): ?string
    {
        return $this->caFieldName;
    }
    public function setCaFieldName(string $caFieldName): self
    {
        $this->caFieldName = $caFieldName;

        return $this;
    }
    public function getDbType(): ?string
    {
        return $this->dbType;
    }
    public function setDbType(string $dbType): self
    {
        $this->dbType = $dbType;

        return $this;
    }
    public function getSystemList(): ?SystemList
    {
        return $this->systemList;
    }
    public function setSystemList(?SystemList $systemList): self
    {
        $this->systemList = $systemList;

        return $this;
    }
    public function getIsIgnored(): ?bool
    {
        return $this->isIgnored;
    }
    public function setIsIgnored(?bool $isIgnored): self
    {
        $this->isIgnored = $isIgnored;

        return $this;
    }
    public function getFieldType(): ?FieldType
    {
        return $this->fieldType;
    }
    public function setFieldType(?FieldType $fieldType): self
    {
        $this->fieldType = $fieldType;

        return $this;
    }
    public function getFieldDisplayType(): ?FieldDisplayType
    {
        return $this->fieldDisplayType;
    }
    public function setFieldDisplayType(?FieldDisplayType $fieldDisplayType): self
    {
        $this->fieldDisplayType = $fieldDisplayType;

        return $this;
    }
    function getUniqueIdentifiers(): array
    {
        return ['fieldId' => $this->getCaFieldName()];
    }
    public function getExample(): ?string
    {
        return $this->example;
    }
    public function setExample(?string $example): self
    {
        $this->example = $example;

        return $this;
    }
}
