<?php

namespace Survos\Providence\XmlModel;

class ProfileLists
{
    use XmlAttributesTrait;

    /** @var ProfileList[] */
    public $list = [];
    /** @var ProfileLabels[] */
    public $labels = [];

    public function findByCode($code): array
    {
        // use current(...) to get first
        return array_filter($this->list, fn($e) => $e->code === $code);
    }
}
