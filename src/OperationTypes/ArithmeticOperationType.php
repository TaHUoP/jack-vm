<?php


namespace TaHUoP\JackVM\OperationTypes;

enum ArithmeticOperationType: string
{
    case ADD = 'add';
    case SUB = 'sub';
    case NEG = 'neg';
    case EQ = 'eq';
    case GT = 'gt';
    case LT = 'lt';
    case AND = 'and';
    case OR = 'or';
    case NOT = 'not';

    public function getExpression(): string
    {
        return match ($this) {
            ArithmeticOperationType::EQ => 'JEQ',
            ArithmeticOperationType::GT => 'JLT',
            ArithmeticOperationType::LT => 'JGT',
            ArithmeticOperationType::ADD => 'D+M',
            ArithmeticOperationType::SUB => 'M-D',
            ArithmeticOperationType::AND => 'D&M',
            ArithmeticOperationType::OR => 'D|M',
            ArithmeticOperationType::NEG => '-',
            ArithmeticOperationType::NOT => '!',
        };
    }
}
