<?php


namespace TaHUoP\Operations;


use TaHUoP\OperationTypes\FunctionOperationType;
use TaHUoP\OperationTypes\MemorySegment;
use TaHUoP\VmInstruction;

class FunctionOperation extends AbstractOperation
{
    /**
     * @var int[]
     */
    private static array $returnLabelsCount = [];

    public function __construct(
        VmInstruction $vmInstruction,
        private readonly FunctionOperationType $type,
        private readonly string $functionName,
        //TODO: add comment explaining what num represents
        private readonly string $num
    ) {
        parent::__construct($vmInstruction);
    }

    public function getAsmInstructions(): array
    {
        if ($this->type === FunctionOperationType::FUNCTION) {
            $instructions = [
                ...self::evalVmInstruction("label $this->functionName"),
                ...array_merge(...array_fill(0, $this->num, self::evalVmInstruction("push constant 0"))),
            ];
        } else {
            $returnLabel = "{$this->vmInstruction->fileName}.{$this->functionName}";
            @self::$returnLabelsCount[$returnLabel] += 1;
            $returnLabel .= '.ret.' . self::$returnLabelsCount[$returnLabel];

            $stateLabels = [
                $returnLabel,
                ...MemorySegment::getStateSegmentAliases()
            ];

            $instructions = [
                '//#saving state',
                ...array_map(
                    fn (string $label): string => implode(PHP_EOL, [
                        "@{$label}",
                        $label === $returnLabel ? 'D=A' : 'D=M',
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
                ...self::evalVmInstruction("goto $this->functionName"),
                ...self::evalVmInstruction("label $returnLabel"),
            ];
        }

        return $instructions;
    }

    public static function getRegexp(): string
    {
        return sprintf(
            '/^(%s) (\S+) (\S+)$/',
            implode('|', array_column(FunctionOperationType::cases(), 'value')),
        );
    }

    public static function getSelf(VmInstruction $vmInstruction, array $matches): OperationInterface
    {
        return new self($vmInstruction, FunctionOperationType::from($matches[1]), $matches[2], $matches[3]);
    }
}
