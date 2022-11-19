<?php


namespace TaHUoP\OperationTypes;

enum MemorySegment: string
{
    case LOCAL = 'local';
    case ARGUMENT = 'argument';
    case THIS = 'this';
    case THAT = 'that';
    case CONSTANT = 'constant';
    case STATIC = 'static';
    case POINTER = 'pointer';
    case TEMP = 'temp';

    /**
     * @return string[]
     */
    public static function getStateSegmentAliases(): array {
        return array_filter(array_map(
            fn(self $segment): ?string => $segment->getHackSegmentAlias(),
            self::cases()
        ));
    }

    public function getHackSegmentAlias(): ?string
    {
        return match ($this) {
            self::LOCAL => 'LCL',
            self::ARGUMENT => 'ARG',
            self::THIS => 'THIS',
            self::THAT => 'THAT',
            default => null,
        };
    }
}
