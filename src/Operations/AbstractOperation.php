<?php


namespace TaHUoP\Operations;


use TaHUoP\VmInstruction;

abstract class AbstractOperation implements OperationInterface
{
    public function __construct(
        public VmInstruction $vmInstruction
    ){}

    public function getAsmInstructions(): string
    {
        return "//{$this->vmInstruction->getText()}";
    }
}