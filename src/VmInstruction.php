<?php


namespace TaHUoP\JackVM;


class VmInstruction
{
    public function __construct(
        public readonly string $text,
        public readonly int $line,
        public readonly int $originalFileLine,
        public readonly string $fileName,
    ) {}
}
