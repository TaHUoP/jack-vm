<?php


namespace TaHUoP\Operations;


use TaHUoP\OperationTypes\MemorySegment;
use TaHUoP\VmInstruction;

class ReturnOperation extends AbstractOperation
{
    public function getAsmInstructions(): array
    {
        $writeEndFrameValWithOffset = fn(string $dest, int $offset): string => implode(PHP_EOL, [
            "//#save *(endFrame - {$offset}) to {$dest}",
            '@endFrame',
            'D=M',
            "@{$offset}",
            'D=D-A',
            'A=D',
            'D=M',
            "@{$dest}",
            'M=D',
        ]);

        $instructions = [
            '//#save LCL to endFrame variable',
            '@LCL',
            'D=M',
            '@endFrame',
            'M=D',
            $writeEndFrameValWithOffset('retAddr', 5),
            '//# *ARG = pop',
            '@SP',
            'A=M-1',
            'D=M',
            '@ARG',
            'A=M',
            'M=D',
            '//# SP = ARG + 1',
            '@ARG',
            'D=M+1',
            '@SP',
            'M=D',
            '//# recover state',
            ...array_map(
                function (string $label) use ($writeEndFrameValWithOffset) {
                    static $n = 0;
                    return $writeEndFrameValWithOffset($label, 4 - ($n++));
                },
                MemorySegment::getStateSegmentAliases()
            ),
            '//# goto retAddr',
            '@retAddr',
            'A=M',
            '0;JMP',
        ];

        return $instructions;
    }

    public static function getRegexp(): string
    {
        return '/^return$/';
    }

    public static function getSelf(VmInstruction $vmInstruction, array $matches): OperationInterface
    {
        return new self($vmInstruction);
    }
}
