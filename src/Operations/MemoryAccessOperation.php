<?php


namespace TaHUoP\Operations;


use InvalidArgumentException;
use TaHUoP\Enums\MemoryAccessOperationType;
use TaHUoP\Enums\MemorySegment;
use TaHUoP\VmInstruction;

class MemoryAccessOperation extends AbstractOperation
{
    private const WRITE_STACK_TO_D_INSTRUCTIONS = [
        '@SP',
        'M=M-1',
        'A=M',
        'D=M',
    ];

    public function __construct(
        VmInstruction $vmInstruction,
        private MemoryAccessOperationType $type,
        private MemorySegment $segment,
        private int $arg
    ) {
        parent::__construct($vmInstruction);
        
        if ($arg < 0)
            throw new InvalidArgumentException('Memory segment argument can\'t be negative.');

        if ($type === MemoryAccessOperationType::POP() && $segment === MemorySegment::CONSTANT())
            throw new InvalidArgumentException('You can\'t pop to constant memory segment.');

        if ($segment === MemorySegment::TEMP() && $arg > 7)
            throw new InvalidArgumentException('Temp memory segment size is exceeded.');

        if ($segment === MemorySegment::POINTER() && !in_array($arg, [0,1]))
            throw new InvalidArgumentException('Pointer memory segment size is exceeded.');
    }

    public function getAsmInstructions(): string
    {
        $memoryAddress = match ($this->segment) {
            MemorySegment::CONSTANT() => $this->arg,
            MemorySegment::POINTER() => ($this->arg === 0 ? 'THIS' : 'THAT'),
            MemorySegment::STATIC() => "{$this->vmInstruction->getFileName()}.{$this->arg}",
            MemorySegment::TEMP() => 5 + $this->arg,
            MemorySegment::LOCAL(), MemorySegment::ARGUMENT(), MemorySegment::THAT(), MemorySegment::THIS() =>
                $this->segment->getHackSegmentAlias(),
        };

        if ($this->segment === MemorySegment::CONSTANT()) {
            $instructions = [
                "@{$memoryAddress}",
                'D=A',
                ...self::WRITE_D_TO_STACK_INSTRUCTIONS
            ];
        } elseif (in_array($this->segment, [MemorySegment::STATIC(), MemorySegment::TEMP(), MemorySegment::POINTER()], true)) {
            $instructions = match ($this->type) {
                MemoryAccessOperationType::PUSH() => [
                    "@{$memoryAddress}",
                    'D=M',
                    ...self::WRITE_D_TO_STACK_INSTRUCTIONS
                ],
                MemoryAccessOperationType::POP() => [
                    ...self::WRITE_STACK_TO_D_INSTRUCTIONS,
                    "@{$memoryAddress}",
                    'M=D'
                ],
            };
        } else {
            $writeGlobalAddressToD = [
                "@{$memoryAddress}",
                'D=M',
                "@{$this->arg}",
                'D=D+A',
            ];

            $instructions = match ($this->type) {
                MemoryAccessOperationType::PUSH() => [
                    ...$writeGlobalAddressToD,
                    'A=D',
                    'D=M',
                    ...self::WRITE_D_TO_STACK_INSTRUCTIONS
                ],
                MemoryAccessOperationType::POP() => [
                    ...$writeGlobalAddressToD,
                    '@R13',
                    'M=D',
                    ...self::WRITE_STACK_TO_D_INSTRUCTIONS,
                    '@R13',
                    'A=M',
                    'M=D',
                ],
            };
        }

        return implode(PHP_EOL, [parent::getAsmInstructions(), ...$instructions]);
    }

    public static function getRegexp(): string
    {
        return sprintf(
            '/^(%s) (%s) ([0-9]+)$/',
            implode('|', MemoryAccessOperationType::values()),
            implode('|', MemorySegment::values()),
        );
    }

    public static function getSelf(VmInstruction $vmInstruction, array $matches): OperationInterface
    {
        return new self(
            $vmInstruction,
            MemoryAccessOperationType::get($matches[1]),
            MemorySegment::get($matches[2]),
            $matches[3]
        );
    }
}