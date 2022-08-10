<?php
// StorageLocation php/Entity.php.twig

namespace Survos\Providence\Model;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\StorageLocationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\KeyValue;
use Survos\BaseBundle\Entity\SurvosBaseEntity;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use App\Entity\StorageLocationType;
use App\Entity\StorageLocationLabelType;
use App\Entity\StorageLocationSource;
use App\Annotations\CATable;
use App\Annotations\CAField;
use App\Annotations\ACConfig;
use App\Traits\LabelTrait;
use App\Traits\NestedEntityTrait;
use App\Traits\CollectiveAccessTrait;
use App\Traits\ProjectCoreTrait;
use App\Traits\UuidAttributeTrait;
use App\Traits\UuidTrait;
use App\Traits\ImportDataTrait;
use App\Traits\ProjectTrait;
use App\Traits\InstanceTrait;
// this is neede for the trait, not sure why, but it fails without it.
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
// #[ApiResource(operations: [new Get(), new Put(), new Delete(), new Patch(), new Post(uriTemplate: 'storage_locations'), new GetCollection()], shortName: 'storage_locations', denormalizationContext: ['groups' => ['write']], normalizationContext: ['groups' => ['read', 'tree']])]
#[CATable('ca_storage_locations', StorageLocation::class, tableName: 'storage_locations')]
#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: StorageLocationRepository::class)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'storageLocations_project_plus_code', columns: ['project_id', 'code'])]
#[UniqueEntity(fields: ['project', 'code'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['id' => 'exact', 'project' => 'exact'])]
class StorageLocation extends CoreEntity implements UuidInterface, ProjectInterface, ListItemInterface, Translatable, ImportDataInterface, InstanceInterface, UuidAttributeInterface
{
    use UuidTrait, UuidAttributeTrait, CollectiveAccessTrait, NestedEntityTrait, LabelTrait, ProjectCoreTrait, ImportDataTrait, ProjectTrait, InstanceTrait;
    final const CA_TABLE_NAME = 'ca_storage_locations';
    final const API_SHORTNAME = 'storage_locations';
    #[Groups(['write'])]
    #[ACConfig(coreClass: Project::class)]
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'storageLocations')]
    #[ORM\JoinColumn(nullable: false)]
    protected Project $project;
    final const typesClass = StorageLocationType::class;
    #[Groups(['read', 'tree'])]
    public function getTypeId() : ?Uuid
    {
        return $this->getStorageLocationType()?->getId();
    }
    public function getTypeProperty() : ?Uuid
    {
        return $this->getStorageLocationType()?->getId();
    }
    public function getType() : ?StorageLocationType
    {
        return $this->getStorageLocationType();
    }
    public function setType(?StorageLocationType $x) : self
    {
        return $this->setStorageLocationType($x);
    }
    #[ACConfig(coreClass: StorageLocationType::class)]
    #[ORM\ManyToOne(targetEntity: StorageLocationType::class, inversedBy: 'storageLocations')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?StorageLocationType $storageLocationType = null;
    final const label_typesClass = StorageLocationLabelType::class;
    #[Groups(['read', 'tree'])]
    public function getLabelTypeId() : ?Uuid
    {
        return $this->getStorageLocationLabelType()?->getId();
    }
    public function getLabelTypeProperty() : ?Uuid
    {
        return $this->getStorageLocationLabelType()?->getId();
    }
    public function getLabelType() : ?StorageLocationLabelType
    {
        return $this->getStorageLocationLabelType();
    }
    public function setLabelType(?StorageLocationLabelType $x) : self
    {
        return $this->setStorageLocationLabelType($x);
    }
    #[ACConfig(coreClass: StorageLocationLabelType::class)]
    #[ORM\ManyToOne(targetEntity: StorageLocationLabelType::class, inversedBy: 'storageLocations')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?StorageLocationLabelType $storageLocationLabelType = null;
    final const sourcesClass = StorageLocationSource::class;
    #[Groups(['read', 'tree'])]
    public function getSourceId() : ?Uuid
    {
        return $this->getStorageLocationSource()?->getId();
    }
    public function getSourceProperty() : ?Uuid
    {
        return $this->getStorageLocationSource()?->getId();
    }
    public function getSource() : ?StorageLocationSource
    {
        return $this->getStorageLocationSource();
    }
    public function setSource(?StorageLocationSource $x) : self
    {
        return $this->setStorageLocationSource($x);
    }
    #[ACConfig(coreClass: StorageLocationSource::class)]
    #[ORM\ManyToOne(targetEntity: StorageLocationSource::class, inversedBy: 'storageLocations')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?StorageLocationSource $storageLocationSource = null;
    #[ORM\Embedded(class: 'KeyValue', columnPrefix: 'import_')]
    private ?KeyValue $importDataWrapper = null;
    public function __construct()
    {
        parent::__construct();
        $this->children = new ArrayCollection();
        $this->importDataWrapper = null;
        //  = new KeyValue(); ?
    }
    /**
    * Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('location_id', description: 'Unique numeric identifier used by CollectiveAccess internally to identify this storage location', caType: 0)]
    #[ORM\Column(name: 'location_id', nullable: true, type: 'integer', options: ['comment' => 'CollectiveAccess id'])]
    private ?int $locationId = null;
    /**
    * *TypesList(storage_location_types) )* Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('type_id', description: 'The type of the storage location. In CollectiveAccess every storage location has a single &quot;instrinsic&quot; type that determines the set of descriptive and administrative metadata that can be applied to it.', caType: 0)]
    #[ORM\Column(name: 'type_id', nullable: true, type: 'integer', options: ['comment' => 'Type'])]
    private ?int $typeId = null;
    /**
    * *TypesList(storage_location_sources) )* Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('source_id', description: 'Administrative source of storage location. This value is often used to indicate the administrative sub-division or legacy database from which the object originates, but can also be re-tasked for use as a simple classification tool if needed.', caType: 0)]
    #[ORM\Column(name: 'source_id', nullable: true, type: 'integer', options: ['comment' => 'Source'])]
    private ?int $sourceId = null;
    /**
    * Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('hier_left', description: 'Left-side boundary for nested set-style hierarchical indexing; used to accelerate search and retrieval of hierarchical record sets.', caType: 0)]
    #[ORM\Column(name: 'hier_left', nullable: true, type: 'integer', options: ['comment' => 'Hierarchical index - left bound'])]
    private ?int $hierLeft = null;
    /**
    * Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('hier_right', description: 'Right-side boundary for nested set-style hierarchical indexing; used to accelerate search and retrieval of hierarchical record sets.', caType: 0)]
    #[ORM\Column(name: 'hier_right', nullable: true, type: 'integer', options: ['comment' => 'Hierarchical index - right bound'])]
    private ?int $hierRight = null;
    /**
    * *TypesList() D:access_statuses)* Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('access', description: 'Indicates if location information is accessible to the public or not. ', caType: 0)]
    #[ORM\Column(name: 'access', nullable: true, type: 'integer', options: ['comment' => 'Access'])]
    private ?int $access = null;
    /**
    * Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('submission_user_id', description: 'User submitting this object', caType: 0)]
    #[ORM\Column(name: 'submission_user_id', nullable: true, type: 'integer', options: ['comment' => 'Submitted by user'])]
    private ?int $submissionUserId = null;
    /**
    * Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('submission_group_id', description: 'Group this object was submitted under', caType: 0)]
    #[ORM\Column(name: 'submission_group_id', nullable: true, type: 'integer', options: ['comment' => 'Submitted for group'])]
    private ?int $submissionGroupId = null;
    /**
    * *TypesList(submission_statuses) )* Doctrine(int) **caType(0)
    * *dbType(integer)
    * *sysList() **default(null)
    */
    #[CAField('submission_status_id', description: 'Indicates submission status of the object.', caType: 0)]
    #[ORM\Column(name: 'submission_status_id', nullable: true, type: 'integer', options: ['comment' => 'Submission status'])]
    private ?int $submissionStatusId = null;
    #[Assert\Valid]
    #[Groups(['write'])]
    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: 'StorageLocation', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'ancestor_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;
    #[Groups(['tree', 'read'])]
    public function getParentId() : ?Uuid
    {
        return $this->getParent() ? $this->getParent()->getId() : null;
    }
    #[ORM\OneToMany(targetEntity: 'StorageLocation', mappedBy: 'parent')]
    #[ORM\OrderBy(['left' => 'ASC'])]
    private $children;
    public function getImportDataWrapper() : KeyValue
    {
        return $this->importDataWrapper;
    }
    public function setImportDataWrapper(KeyValue $importDataWrapper) : self
    {
        $this->importDataWrapper = $importDataWrapper;
        return $this;
    }
    public function getLocationId() : ?int
    {
        return $this->locationId;
    }
    public function setLocationId(?int $locationId) : self
    {
        $this->locationId = $locationId;
        return $this;
    }
    public function setTypeId(?int $typeId) : self
    {
        $this->typeId = $typeId;
        return $this;
    }
    public function setSourceId(?int $sourceId) : self
    {
        $this->sourceId = $sourceId;
        return $this;
    }
    public function getHierLeft() : ?int
    {
        return $this->hierLeft;
    }
    public function setHierLeft(?int $hierLeft) : self
    {
        $this->hierLeft = $hierLeft;
        return $this;
    }
    public function getHierRight() : ?int
    {
        return $this->hierRight;
    }
    public function setHierRight(?int $hierRight) : self
    {
        $this->hierRight = $hierRight;
        return $this;
    }
    public function getSubmissionUserId() : ?int
    {
        return $this->submissionUserId;
    }
    public function setSubmissionUserId(?int $submissionUserId) : self
    {
        $this->submissionUserId = $submissionUserId;
        return $this;
    }
    public function getSubmissionGroupId() : ?int
    {
        return $this->submissionGroupId;
    }
    public function setSubmissionGroupId(?int $submissionGroupId) : self
    {
        $this->submissionGroupId = $submissionGroupId;
        return $this;
    }
    public function getSubmissionStatusId() : ?int
    {
        return $this->submissionStatusId;
    }
    public function setSubmissionStatusId(?int $submissionStatusId) : self
    {
        $this->submissionStatusId = $submissionStatusId;
        return $this;
    }
    public function getStorageLocationType() : ?StorageLocationType
    {
        return $this->storageLocationType;
    }
    public function setStorageLocationType(?StorageLocationType $storageLocationType) : self
    {
        $this->storageLocationType = $storageLocationType;
        return $this;
    }
    public function getStorageLocationLabelType() : ?StorageLocationLabelType
    {
        return $this->storageLocationLabelType;
    }
    public function setStorageLocationLabelType(?StorageLocationLabelType $storageLocationLabelType) : self
    {
        $this->storageLocationLabelType = $storageLocationLabelType;
        return $this;
    }
    public function getStorageLocationSource() : ?StorageLocationSource
    {
        return $this->storageLocationSource;
    }
    public function setStorageLocationSource(?StorageLocationSource $storageLocationSource) : self
    {
        $this->storageLocationSource = $storageLocationSource;
        return $this;
    }
    public function getParent() : ?self
    {
        return $this->parent;
    }
    public function setParent(?self $parent) : self
    {
        $this->parent = $parent;
        return $this;
    }
    /**
     * @return Collection|StorageLocation[]
     */
    public function getChildren() : Collection
    {
        return $this->children;
    }
    public function addChild(StorageLocation $child) : self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }
        return $this;
    }
    public function removeChild(StorageLocation $child) : self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }
}

