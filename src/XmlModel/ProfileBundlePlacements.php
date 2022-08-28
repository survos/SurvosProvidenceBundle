<?php

namespace Survos\Providence\XmlModel;

use Survos\Providence\Repository\ProfileBundlePlacementsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ ORM\Entity(repositoryClass=ProfileBundlePlacementsRepository::class)
 */
class ProfileBundlePlacements
{
    /** @var ProfilePlacement[] */
    #[Groups(['ui'])]
    public $placement = [];
}
