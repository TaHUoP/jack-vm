<?php


namespace TaHUoP\Operations;


use Exception;
use TaHUoP\Enums\ArithmeticOperationType;
use TaHUoP\Enums\MemoryAccessOperationType;
use TaHUoP\Enums\MemorySegment;
use TaHUoP\VmInstruction;

class OperationFactory
{
    /**
     * @param VmInstruction $vmInstruction
     * @param string $filename
     * @return OperationInterface
     * @throws Exception
     */
    public static function getOperation(VmInstruction $vmInstruction, string $filename): OperationInterface
    {
        if (preg_match(ArithmeticOperation::getRegexp(), $vmInstruction->getText(), $matches)) {
            return new ArithmeticOperation($vmInstruction, ArithmeticOperationType::get($matches[1]));
        } elseif (preg_match(MemoryAccessOperation::getRegexp(), $vmInstruction->getText(), $matches)) {
            return new MemoryAccessOperation($vmInstruction, MemoryAccessOperationType::get($matches[1]), MemorySegment::get($matches[2]), $matches[3], $filename);
        } else {
            throw new Exception("Invalid instruction \"{$vmInstruction->getText()}\" on line " . $vmInstruction->getOriginalFileLine() . '.');
        }
    }
}
