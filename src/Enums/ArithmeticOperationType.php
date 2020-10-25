<?php


namespace TaHUoP\Enums;

/**
 * @method static MemoryAccessOperationType ADD()
 * @method static MemoryAccessOperationType SUB()
 * @method static MemoryAccessOperationType NEG()
 * @method static MemoryAccessOperationType EQ()
 * @method static MemoryAccessOperationType GT()
 * @method static MemoryAccessOperationType LT()
 * @method static MemoryAccessOperationType AND()
 * @method static MemoryAccessOperationType OR()
 * @method static MemoryAccessOperationType NOT()
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