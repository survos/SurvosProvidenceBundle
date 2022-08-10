<?php

namespace Survos\Providence\XmlModel;

use Survos\Providence\Repository\ProfileBundlePlacementsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity(repositoryClass=ProfileBundlePlacementsRepository::class)
 */
class ProfileBundlePlacements
{
    /** @var ProfilePlacement[] */
    public $placement = [];
}
