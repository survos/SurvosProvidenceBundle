<?php

namespace Survos\Providence\XmlModel;

use Survos\Providence\Repository\ProfileTypeRestrictionsRepository;
use Doctrine\ORM\Mapping as ORM;

class ProfileTypeRestrictions
{
    use XmlAttributesTrait;
    public $relationshipTypes;
    public $roles;
    public $logins;

    public ProfileBundlePlacements $bundlePlacements;
    /** @var ProfileRestrictions[] */
    public $restriction = [];
    public $screen = [];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
