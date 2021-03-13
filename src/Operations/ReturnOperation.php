<?php


namespace TaHUoP\Operations;


use TaHUoP\VmInstruction;

class ReturnOperation extends AbstractOperation
{
    public function getAsmInstructions(): string
    {
        //TODO: implement return instruction commands
        $instructions = [];
        return implode(PHP_EOL, [parent::getAsmInstructions(), ...$instructions]);
    }

    public static function getRegexp(): string
    {
        return '/^return$/';
    }

    public static function getSelf(VmInstruction $vmInstruction, array $matches): OperationInterface
    {
        return new self($vmInstruction);
    }
}