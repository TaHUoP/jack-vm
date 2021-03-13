<?php


namespace TaHUoP\Operations;


use TaHUoP\Enums\FunctionOperationType;
use TaHUoP\Enums\MemorySegment;
use TaHUoP\VmInstruction;

class FunctionOperation extends AbstractOperation
{
    /**
     * @var int[]
     */
    private static array $returnLabelsCount = [];

    public function __construct(
        VmInstruction $vmInstruction,
        private FunctionOperationType $type,
        private string $functionName,
        private string $num
    ) {
        parent::__construct($vmInstruction);
    }

    public function getAsmInstructions(): string
    {
        if ($this->type === FunctionOperationType::FUNCTION()) {
            //TODO: implement function instruction commands
            $instructions = [];
        } else {
            $returnLabel = "{$this->vmInstruction->getFileName()}.{$this->functionName}";
            self::$returnLabelsCount[$returnLabel] += 1;
            $returnLabel .= '.ret.' . self::$returnLabelsCount[$returnLabel];

            $stateLabels = [
                $returnLabel,
                ...array_filter(array_map(
                    fn(string $segment): ?string => MemorySegment::get($segment)->getHackSegmentAlias(),
                    MemorySegment::values()
                ))
            ];

            $instructions = [
                '//#saving state',
                ...array_map(
                    fn (string $label): string => implode(PHP_EOL, [
                        "@{$label}",
                        "D=A",
                        self::WRITE_D_TO_STACK_INSTRUCTIONS
                    ]),
                    $stateLabels,
                ),
                '//#repositioning ARG',
                '@SP',
                'D=M',
                '@5',
                'D=D-A',
                "@{$this->num}",
                'D=D-A',
                '@ARG',
                'M=D',
                '//#reposition LCL',
                '@SP',
                'D=M',
                '@LCL',
                'M=D',
                "//#goto $this->functionName",
                self::evalVmInstruction("goto $this->functionName"),
                "({$returnLabel})"
            ];
        }

        return implode(PHP_EOL, [parent::getAsmInstructions(), ...$instructions]);
    }

    public static function getRegexp(): string
    {
        return sprintf(
            '/^(%s) (\S+) (\S+)$/',
            implode('|', FunctionOperationType::values()),
        );
    }

    public static function getSelf(VmInstruction $vmInstruction, array $matches): OperationInterface
    {
        return new self($vmInstruction, FunctionOperationType::get($matches[1]), $matches[2], $matches[3]);
    }
}