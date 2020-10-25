<?php


namespace TaHUoP\Enums;

/**
 * @method static MemoryAccessOperationType PUSH()
 * @method static MemoryAccessOperationType POP()
 */
class MemoryAccessOperationType extends AbstractEnum
{
    public const PUSH = 'push';
    public const POP = 'pop';
}