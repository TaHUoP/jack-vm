<?php


namespace TaHUoP\Operations;


use TaHUoP\VmInstruction;

interface OperationInterface
{
    public function getAsmInstructions(): array;

    public static function getRegexp(): string;

    public static function getSelf(VmInstruction $vmInstruction, array $matches): self;
}
