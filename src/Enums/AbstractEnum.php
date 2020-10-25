<?php


namespace TaHUoP\Enums;


use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

abstract class AbstractEnum extends Enum
{
    use AutoDiscoveredValuesTrait;

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
