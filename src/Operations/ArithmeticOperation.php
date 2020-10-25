<?php


namespace TaHUoP\Operations;


use TaHUoP\Enums\ArithmeticOperationType;

class ArithmeticOperation implements OperationInterface
{
    private ArithmeticOperationType $type;

    public function __construct(ArithmeticOperationType $type)
    {
        $this->type = $type;
    }

    public function getAsmInstructions(): string
    {
        // TODO: Implement getAsmInstructions() method.
    }
}