<?php

namespace Survos\Providence\XmlModel;

use Survos\Providence\Repository\ProfileLabelsRepository;
use Doctrine\ORM\Mapping as ORM;


class ProfileLabels
{
//    use XmlAttributesTrait;

    /** @var ProfileLabel[] */
    public $label = [];
}
