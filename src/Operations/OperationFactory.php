<?php


namespace TaHUoP\Operations;


use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use TaHUoP\VmInstruction;

class OperationFactory
{
    /**
     * @param VmInstruction $vmInstruction
     * @return OperationInterface
     * @throws Exception
     */
    public static function getOperation(VmInstruction $vmInstruction): OperationInterface
    {
        $operationClasses = array_filter(
            ClassFinder::getClassesInNamespace('TaHUoP\Operations'),
            fn(string $className): bool => is_subclass_of($className, AbstractOperation::class)
        );

        /** @var AbstractOperation $operationClass */
        foreach ($operationClasses as $operationClass) {
            if (preg_match($operationClass::getRegexp(), $vmInstruction->text, $matches)) {
                //TODO: extract instantiation logic from operation classes
                return $operationClass::getSelf($vmInstruction, $matches);
            }
        }

        throw new Exception("Invalid instruction \"{$vmInstruction->text}\" on line $vmInstruction->originalFileLine");
    }
}
