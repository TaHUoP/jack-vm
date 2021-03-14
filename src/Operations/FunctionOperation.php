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
            $instructions = [
                self::evalVmInstruction("label $this->functionName"),
                ...array_fill(0, $this->num, self::evalVmInstruction("push constant 0")),
            ];
        } else {
            $returnLabel = "{$this->vmInstruction->getFileName()}.{$this->functionName}";
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
                self::evalVmInstruction("goto $this->functionName"),
                self::evalVmInstruction("label $returnLabel"),
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