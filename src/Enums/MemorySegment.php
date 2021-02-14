<?php


namespace TaHUoP\Enums;

/**
 * @method static MemorySegment LOCAL()
 * @method static MemorySegment ARGUMENT()
 * @method static MemorySegment THIS()
 * @method static MemorySegment THAT()
 * @method static MemorySegment CONSTANT()
 * @method static MemorySegment STATIC()
 * @method static MemorySegment POINTER()
 * @method static MemorySegment TEMP()
 */
class MemorySegment extends AbstractEnum
{
    public const LOCAL = 'local';
    public const ARGUMENT = 'argument';
    public const THIS = 'this';
    public const THAT = 'that';
    public const CONSTANT = 'constant';
    public const STATIC = 'static';
    public const POINTER = 'pointer';
    public const TEMP = 'temp';

    public function getHackSegmentAlias(): ?string
    {
        return match ($this->value) {
            self::LOCAL => 'LCL',
            self::ARGUMENT => 'ARG',
            self::THIS => 'THIS',
            self::THAT => 'THAT',
            default => null,
        };
    }
}