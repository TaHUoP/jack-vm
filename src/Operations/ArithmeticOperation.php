<?php


namespace TaHUoP\Operations;


use TaHUoP\Enums\ArithmeticOperationType;
use TaHUoP\VmInstruction;

class ArithmeticOperation extends AbstractOperation
{
    public function __construct(
        VmInstruction $vmInstruction,
        private ArithmeticOperationType $type
    ) {
        parent::__construct($vmInstruction);
    }

    public function getAsmInstructions(): string
    {
        $instructions =
            "@SP
            A=M-1" . PHP_EOL;

        $expression = match ($this->type) {
            ArithmeticOperationType::EQ() => 'JEZ',
            ArithmeticOperationType::GT() => 'JLZ',
            ArithmeticOperationType::LT() => 'JGZ',
            ArithmeticOperationType::ADD() => 'D+M',
            ArithmeticOperationType::SUB() => 'M-D',
            ArithmeticOperationType::AND() => 'D&M',
            ArithmeticOperationType::OR() => 'D|M',
            ArithmeticOperationType::NEG() => '-',
            ArithmeticOperationType::NOT() => '!',
        };

        if (in_array($this->type, [ArithmeticOperationType::NEG(), ArithmeticOperationType::NOT()], true)) {
            $instructions .= "M={$expression}M";
        } else {
            $instructions .=
                "D=M
                
                @SP
                M=M-1
                
                @SP
                A=M-1" . PHP_EOL .
                match ($this->type) {
                    ArithmeticOperationType::EQ(), ArithmeticOperationType::GT(), ArithmeticOperationType::LT() =>
                        "D=D-M
                        
                        @TRUE
                        D;{$expression}
                        
                        M=0
                        
                        (TRUE)
                        M=1",
                    ArithmeticOperationType::ADD(), ArithmeticOperationType::SUB(), ArithmeticOperationType::AND(),
                    ArithmeticOperationType::OR() =>
                        "M={$expression}",
                };
        }

        return parent::getAsmInstructions() . PHP_EOL . $instructions;
    }

    public static function getRegexp(): string
    {
        return '/^(add|sub|neg|eq|gt|lt|and|or|not)$/';
    }
}