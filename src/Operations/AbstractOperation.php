<?php


namespace TaHUoP\Operations;


use TaHUoP\VmInstruction;

abstract class AbstractOperation implements OperationInterface
{
    protected const WRITE_D_TO_STACK_INSTRUCTIONS = [
        '@SP',
        'A=M',
        'M=D',

        '@SP',
        'M=M+1',
    ];

    public function __construct(
        public VmInstruction $vmInstruction
    ){}

    public function getAsmInstructions(): string
    {
        return "//{$this->vmInstruction->getText()}";
    }

    protected static function evalVmInstruction(string $instruction): string {
        $operation = OperationFactory::getOperation(new VmInstruction($instruction, 0, 0, ''));

        if($operation::class === static::class)
            throw new \InvalidArgumentException("Recursive instruction evaluation is not allowed");

        $asmInstructions = explode(PHP_EOL, $operation->getAsmInstructions());
        array_shift($asmInstructions);

        return implode(PHP_EOL, $asmInstructions);
    }
}