<?php


namespace TaHUoP\JackVM\Operations;


use TaHUoP\JackVM\OperationTypes\BranchingOperationType;
use TaHUoP\JackVM\VmInstruction;

class BranchingOperation extends AbstractOperation
{
    private function __construct(
        VmInstruction $vmInstruction,
        private readonly BranchingOperationType $type,
        private readonly string $label
    ) {
        parent::__construct($vmInstruction);
    }

    public function getAsmInstructions(): array
    {
        $instructions = match ($this->type) {
            BranchingOperationType::LABEL => ["({$this->label})"],
            BranchingOperationType::GOTO => [
                "@{$this->label}",
                '0;JMP',
            ],
            BranchingOperationType::IF_GOTO => [
                '@SP',
                'M=M-1',
                'A=M',
                'D=M',
                "@{$this->label}",
                'D;JNE',
            ]
        };

        return $instructions;
    }

    public static function getRegexp(): string
    {
        return sprintf(
            '/^(%s) (\S+)$/',
            implode('|', array_column(BranchingOperationType::cases(), 'value')),
        );
    }

    public static function getSelf(VmInstruction $vmInstruction, array $matches): OperationInterface
    {
        return new self($vmInstruction, BranchingOperationType::from($matches[1]), $matches[2]);
    }
}
