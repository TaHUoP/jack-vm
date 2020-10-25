<?php


namespace TaHUoP\Operations;


use InvalidArgumentException;
use TaHUoP\Enums\MemoryAccessOperationType;
use TaHUoP\Enums\MemorySegment;

class MemoryAccessOperation implements OperationInterface
{
    private MemoryAccessOperationType $type;
    private MemorySegment $segment;
    private int $arg;

    public function __construct(MemoryAccessOperationType $type, MemorySegment $segment, int $arg)
    {
        if ($type === MemoryAccessOperationType::POP() && $segment === MemorySegment::CONSTANT())
            throw new InvalidArgumentException('You can\'t pop to constant memory segment.');

        $this->type = $type;
        $this->segment = $segment;
        $this->arg = $arg;
    }

    public function getAsmInstructions(): string
    {
        // TODO: Implement getAsmInstructions() method.
    }
}