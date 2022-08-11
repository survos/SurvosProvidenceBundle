<?php

namespace Survos\Providence\XmlModel;

class ProfileLocales
{
    use XmlAttributesTrait;

    /** @var ProfileLocale[] */
    public $locale = [];
//    /** @var ProfileLabels[] */
//    public $labels = [];

//    public function findByCode($code): array
//    {
//        // use current(...) to get first
//        return array_filter($this->list, fn($e) => $e->code === $code);
//    }


}
