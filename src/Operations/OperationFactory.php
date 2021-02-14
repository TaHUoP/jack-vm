<?php


namespace TaHUoP\Operations;


use Exception;
use TaHUoP\Enums\ArithmeticOperationType;
use TaHUoP\Enums\MemoryAccessOperationType;
use TaHUoP\Enums\MemorySegment;
use TaHUoP\Instruction;

class OperationFactory
{
    private const ARITHMETIC_OPERATION_REGEX = '/^(add|sub|neg|eq|gt|lt|and|or|not)$/';
    private const MEMORY_ACCESS_OPERATION_REGEX = '/^(push|pop) (local|argument|this|that|constant|static|pointer|temp) ([0-9]+)$/';

    /**
     * @param Instruction $instruction
     * @param string $filename
     * @return OperationInterface
     * @throws Exception
     */
    public static function getOperation(Instruction $instruction, string $filename): OperationInterface
    {
        if (preg_match(self::ARITHMETIC_OPERATION_REGEX, $instruction->text, $matches)) {
            return new ArithmeticOperation(ArithmeticOperationType::get($matches[1]));
        } elseif (preg_match(self::MEMORY_ACCESS_OPERATION_REGEX, $instruction->text, $matches)) {
            return new MemoryAccessOperation(MemoryAccessOperationType::get($matches[1]), MemorySegment::get($matches[2]), $matches[3], $filename);
        } else {
            throw new Exception("Invalid instruction \"$instruction->text\" on line " . $instruction->getOriginalFileLine() . '.');
        }
    }
}
