<?php


namespace TaHUoP\JackVM\Operations;


use TaHUoP\JackVM\VmInstruction;

interface OperationInterface
{
    public function getAsmInstructions(): array;

    public static function getRegexp(): string;

    public static function getSelf(VmInstruction $vmInstruction, array $matches): self;
}
