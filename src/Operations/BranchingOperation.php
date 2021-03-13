<?php


namespace TaHUoP\Operations;


use TaHUoP\Enums\BranchingOperationType;
use TaHUoP\VmInstruction;

class BranchingOperation extends AbstractOperation
{
    public function __construct(
        VmInstruction $vmInstruction,
        private BranchingOperationType $type,
        private string $label
    ) {
        parent::__construct($vmInstruction);
    }

    public function getAsmInstructions(): string
    {
        $instructions = match ($this->type) {
            BranchingOperationType::LABEL() =>
                "({$this->label})",
            BranchingOperationType::GOTO() =>
                "@{$this->label}
                0;JMP",
            BranchingOperationType::IF_GOTO() =>
                "@SP
                M=M-1
                A=M
                D=M
                @{$this->label}
                D;JNE",
        };

        return parent::getAsmInstructions() . PHP_EOL . $instructions;
    }

    public static function getRegexp(): string
    {
        return sprintf(
            '/^(%s) (\S+)$/',
            implode('|', BranchingOperationType::values()),
        );
    }

    public static function getSelf(VmInstruction $vmInstruction, string $filename, array $matches): OperationInterface
    {
        return new self($vmInstruction, BranchingOperationType::get($matches[1]), $matches[2]);
    }
}