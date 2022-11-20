<?php


namespace TaHUoP\JackVM\Operations;


use TaHUoP\JackVM\VmInstruction;

abstract class AbstractOperation implements OperationInterface
{
    protected const WRITE_D_TO_STACK_INSTRUCTIONS =
        "@SP
        A=M
        M=D
        
        @SP
        M=M+1";

    protected function __construct(
        public readonly VmInstruction $vmInstruction
    ){}

    protected static function evalVmInstruction(string $instruction): array
    {
        $operation = OperationFactory::getOperation(new VmInstruction($instruction, 0, 0, ''));

        if($operation::class === static::class)
            throw new \InvalidArgumentException("Recursive instruction evaluation is not allowed");

        return $operation->getAsmInstructions();
    }
}
