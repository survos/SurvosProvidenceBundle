<?php

namespace Survos\Providence\XmlModel;

class ProfileUserInterfaces
{
    /* @var ProfileUserInterface[] */
    public $userInterface = [];

    public function findByCode($code): array
    {
        // use current(...) to get first
        return array_filter($this->userInterface, fn(ProfileUserInterface $e) => $e->code === $code);
    }

}
