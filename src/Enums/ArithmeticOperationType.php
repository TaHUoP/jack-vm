<?php


namespace TaHUoP\Enums;

/**
 * @method static ArithmeticOperationType ADD()
 * @method static ArithmeticOperationType SUB()
 * @method static ArithmeticOperationType NEG()
 * @method static ArithmeticOperationType EQ()
 * @method static ArithmeticOperationType GT()
 * @method static ArithmeticOperationType LT()
 * @method static ArithmeticOperationType AND()
 * @method static ArithmeticOperationType OR()
 * @method static ArithmeticOperationType NOT()
 */
class ArithmeticOperationType extends AbstractEnum
{
    public const ADD = 'add';
    public const SUB = 'sub';
    public const NEG = 'neg';
    public const EQ = 'eq';
    public const GT = 'gt';
    public const LT = 'lt';
    public const AND = 'and';
    public const OR = 'or';
    public const NOT = 'not';
}