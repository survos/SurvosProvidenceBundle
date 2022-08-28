<?php

namespace Survos\Providence\XmlModel;

use Symfony\Component\Serializer\Annotation\Groups;

class ProfileScreen implements XmlLabelsInterface
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;


    public ProfileBundlePlacements $bundlePlacements;

//    /** @var ProfileScreen[] */
//    public $screen = [];
    public string $idno;
    private array $bundles = [];

    public ProfileUserInterface $userInterface;
//    public ProfileTypeRestrictions $typeRestrictions;

    public function getCode(): string
    {
        return $this->idno;
    }

    public function _label(): string { return sprintf("%s.%s", 'screen', $this->getCode()); }

    #[Groups(['ui'])]
    public function getPlacements() { return $this->bundlePlacements->placement; }
    #[Groups(['ui'])]
    public function getBundles(): array {
        return $this->bundles;
    }

    public function setBundles(array $bundles): ProfileScreen {
        $this->bundles = $bundles;
        return $this;
    }

}
