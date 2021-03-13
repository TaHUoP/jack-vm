<?php


namespace TaHUoP\Operations;


use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
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
        $operationClasses = array_filter(
            ClassFinder::getClassesInNamespace('TaHUoP\Operations'),
            fn(string $className): bool => is_subclass_of($className, AbstractOperation::class)
        );

        /** @var AbstractOperation $operationClass */
        foreach ($operationClasses as $operationClass) {
            if (preg_match($operationClass::getRegexp(), $vmInstruction->getText(), $matches)) {
                return $operationClass::getSelf($vmInstruction, $filename, $matches);
            }
        }

        throw new Exception("Invalid instruction \"{$vmInstruction->getText()}\" on line " . $vmInstruction->getOriginalFileLine() . '.');
    }
}
