<?php


namespace TaHUoP\Operations;


use Exception;
use TaHUoP\Enums\ArithmeticOperationType;
use TaHUoP\Enums\MemoryAccessOperationType;
use TaHUoP\Enums\MemorySegment;
use TaHUoP\Instruction;

class OperationFactory
{
    /**
     * @param Instruction $instruction
     * @param string $filename
     * @return OperationInterface
     * @throws Exception
     */
    public static function getOperation(Instruction $instruction, string $filename): OperationInterface
    {
        if (preg_match(ArithmeticOperation::getRegexp(), $instruction->text, $matches)) {
            return new ArithmeticOperation(ArithmeticOperationType::get($matches[1]));
        } elseif (preg_match(MemoryAccessOperation::getRegexp(), $instruction->text, $matches)) {
            return new MemoryAccessOperation(MemoryAccessOperationType::get($matches[1]), MemorySegment::get($matches[2]), $matches[3], $filename);
        } else {
            throw new Exception("Invalid instruction \"$instruction->text\" on line " . $instruction->getOriginalFileLine() . '.');
        }
    }
}
