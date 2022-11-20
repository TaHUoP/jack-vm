<?php


namespace TaHUoP\JackVM\Operations;


use TaHUoP\JackVM\OperationTypes\ArithmeticOperationType;
use TaHUoP\JackVM\VmInstruction;

class ArithmeticOperation extends AbstractOperation
{
    private function __construct(
        VmInstruction $vmInstruction,
        private readonly ArithmeticOperationType $type
    ) {
        parent::__construct($vmInstruction);
    }

    public function getAsmInstructions(): array
    {
        $instructions = [
            '@SP',
            'A=M-1',
        ];

        if (in_array($this->type, [ArithmeticOperationType::NEG, ArithmeticOperationType::NOT], true)) {
            $instructions[]= "M={$this->type->getExpression()}M";
        } else {
            static $calls = 1;

            $instructions = [...$instructions,
                'D=M',

                '@SP',
                'M=M-1',

                '@SP',
                'A=M-1',
                ...match ($this->type) {
                    ArithmeticOperationType::EQ, ArithmeticOperationType::GT, ArithmeticOperationType::LT => [
                        'D=D-M',
                        
                        "@TRUE{$calls}",
                        "D;{$this->type->getExpression()}",
                        '@SP',
                        'A=M-1',
                        'M=0',
                        "@FALSE{$calls}",
                        '0;JMP',
                        "(TRUE{$calls})",
                        '@SP',
                        'A=M-1',
                        'M=1',
                        "(FALSE{$calls})",

                    ],
                    ArithmeticOperationType::ADD, ArithmeticOperationType::SUB, ArithmeticOperationType::AND,
                    ArithmeticOperationType::OR =>
                        ["M={$this->type->getExpression()}"],
                }
            ];

            $calls++;
        }

        return $instructions;
    }

    public static function getRegexp(): string
    {
        return sprintf(
            '/^(%s)$/',
            implode('|', array_column(ArithmeticOperationType::cases(), 'value')),
        );
    }

    public static function getSelf(VmInstruction $vmInstruction, array $matches): OperationInterface
    {
        return new self($vmInstruction, ArithmeticOperationType::from($matches[1]));
    }
}
