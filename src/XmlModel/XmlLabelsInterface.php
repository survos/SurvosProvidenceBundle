<?php

namespace Survos\Providence\XmlModel;

interface XmlLabelsInterface
{
    public function getLabels();

    public function _label();
    public function _description();
    public function getCode();
    public function hasDescription(): bool;
    public function setHasDescription(bool $hasDescription): self;

}
