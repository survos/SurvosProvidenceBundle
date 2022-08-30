<?php

namespace Survos\Providence\XmlModel;

use Symfony\Component\Serializer\Annotation\Groups;

class ProfileLists
{
    use XmlAttributesTrait;
    use XmlLabelsTrait;

    /** @var ProfileList[] */
    #[Groups(['lists'])]
    public $list = [];
//    /** @var ProfileLabels[] */
//    public $labels = [];

    public function findByCode($code): array
    {
        // use current(...) to get first
        return array_filter($this->list, fn($e) => $e->code === $code);
    }
}
