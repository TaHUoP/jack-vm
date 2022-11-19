<?php


namespace TaHUoP\Operations;


use InvalidArgumentException;
use TaHUoP\OperationTypes\MemoryAccessOperationType;
use TaHUoP\OperationTypes\MemorySegment;
use TaHUoP\VmInstruction;

class MemoryAccessOperation extends AbstractOperation
{
    private const WRITE_STACK_TO_D_INSTRUCTIONS =
        "@SP
        M=M-1
        A=M
        D=M";

    public function __construct(
        VmInstruction $vmInstruction,
        private readonly MemoryAccessOperationType $type,
        private readonly MemorySegment $segment,
        private readonly int $arg
    ) {
        if ($arg < 0)
            throw new InvalidArgumentException('Memory segment argument can\'t be negative.');

        if ($type === MemoryAccessOperationType::POP && $segment === MemorySegment::CONSTANT)
            throw new InvalidArgumentException('You can\'t pop to constant memory segment.');

        if ($segment === MemorySegment::TEMP && $arg > 7)
            throw new InvalidArgumentException('Temp memory segment size is exceeded.');

        if ($segment === MemorySegment::POINTER && !in_array($arg, [0,1]))
            throw new InvalidArgumentException('Pointer memory segment size is exceeded.');

        parent::__construct($vmInstruction);
    }

    public function getAsmInstructions(): array
    {
        $memoryAddress = match ($this->segment) {
            MemorySegment::CONSTANT => $this->arg,
            MemorySegment::POINTER => ($this->arg === 0 ? 'THIS' : 'THAT'),
            MemorySegment::STATIC => "{$this->vmInstruction->fileName}.{$this->arg}",
            MemorySegment::TEMP => 5 + $this->arg,
            MemorySegment::LOCAL, MemorySegment::ARGUMENT, MemorySegment::THAT, MemorySegment::THIS =>
                $this->segment->getHackSegmentAlias(),
        };

        if ($this->segment === MemorySegment::CONSTANT) {
            $instructions = [
                "@{$memoryAddress}",
                'D=A',
                self::WRITE_D_TO_STACK_INSTRUCTIONS
            ];
        } elseif (in_array($this->segment, [MemorySegment::STATIC, MemorySegment::TEMP, MemorySegment::POINTER], true)) {
            $instructions = match ($this->type) {
                MemoryAccessOperationType::PUSH => [
                    "@{$memoryAddress}",
                    'D=M',
                    self::WRITE_D_TO_STACK_INSTRUCTIONS
                ],
                MemoryAccessOperationType::POP => [
                    self::WRITE_STACK_TO_D_INSTRUCTIONS,
                    "@{$memoryAddress}",
                    'M=D'
                ],
            };
        } else {
            $instructions = [
                "@{$memoryAddress}",
                'D=M',
                "@{$this->arg}",
                'D=D+A',
                ...match ($this->type) {
                    MemoryAccessOperationType::PUSH => [
                        'A=D',
                        'D=M',
                        self::WRITE_D_TO_STACK_INSTRUCTIONS
                    ],
                    MemoryAccessOperationType::POP => [
                        '@temp',
                        'M=D',
                        self::WRITE_STACK_TO_D_INSTRUCTIONS,
                        '@temp',
                        'A=M',
                        'M=D',
                    ],
                }
            ];
        }

        return $instructions;
    }

    public static function getRegexp(): string
    {
        return sprintf(
            '/^(%s) (%s) ([0-9]+)$/',
            implode('|', array_column(MemoryAccessOperationType::cases(), 'value')),
            implode('|', array_column(MemorySegment::cases(), 'value')),
        );
    }

    public static function getSelf(VmInstruction $vmInstruction, array $matches): OperationInterface
    {
        return new self(
            $vmInstruction,
            MemoryAccessOperationType::from($matches[1]),
            MemorySegment::from($matches[2]),
            $matches[3]
        );
    }
}
