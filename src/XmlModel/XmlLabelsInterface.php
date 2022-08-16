<?php

namespace Survos\Providence\XmlModel;

interface XmlLabelsInterface
{
    public function getLabels();

    public function _label(): string;
    public function _description(): string;
    public function _typename(): ?string;
    public function _typename_reverse(): ?string;

    public function getCode();
    public function hasDescription(): bool;
    public function setHasDescription(bool $hasDescription): self;

}
