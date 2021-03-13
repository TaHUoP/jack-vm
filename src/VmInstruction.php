<?php


namespace TaHUoP;


class VmInstruction
{
    public function __construct(
        private string $text,
        private int $line,
        private int $originalFileLine,
        private string $fileName,
    ) {}

    public function getText(): string
    {
        return $this->text;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getOriginalFileLine(): int
    {
        return $this->originalFileLine;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}