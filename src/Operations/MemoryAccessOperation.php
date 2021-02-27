<?php


namespace TaHUoP\Operations;


use InvalidArgumentException;
use TaHUoP\Enums\MemoryAccessOperationType;
use TaHUoP\Enums\MemorySegment;

/*
 * Vm memory access commands implementation examples
//push local 5
$a = '
@LCL
D=M
@5
D=D+A

A=D
D=M

@SP
A=M
M=D

@SP
M=M+1';

//push constant 5
$a = '
@5
D=A

@SP
A=M
M=D

@SP
M=M+1';

//pop local 5
$a = '
@LCL
D=M
@5
D=D+A

@R13
M=D

@SP
A=M
D=M

@R13
A=M
M=D

@SP
M=M-1';

//push static 5
$a = '
@Foo.5
D=M

@SP
A=M
M=D

@SP
M=M+1';

//pop static 5
$a = '
@SP
A=M
D=M

@Foo.5
M=D

@SP
M=M+1';
*/

class MemoryAccessOperation implements OperationInterface
{
    private MemoryAccessOperationType $type;
    private MemorySegment $segment;
    private int $arg;
    private string $filename;

    public function __construct(MemoryAccessOperationType $type, MemorySegment $segment, int $arg, string $filename)
    {
        if ($arg < 0)
            throw new InvalidArgumentException('Memory segment argument can\'t be negative.');

        if ($type === MemoryAccessOperationType::POP() && $segment === MemorySegment::CONSTANT())
            throw new InvalidArgumentException('You can\'t pop to constant memory segment.');

        if ($segment === MemorySegment::TEMP() && $arg > 7)
            throw new InvalidArgumentException('Temp memory segment size is exceeded.');

        if ($segment === MemorySegment::POINTER() && !in_array($arg, [0,1]))
            throw new InvalidArgumentException('Pointer memory segment size is exceeded.');

        $this->type = $type;
        $this->segment = $segment;
        $this->arg = $arg;
        $this->filename = $filename;
    }

    public function getAsmInstructions(): string
    {
        $changeStackPositionOperator = $this->type === MemoryAccessOperationType::PUSH() ? '+' : '-';

        if (in_array($this->segment, [MemorySegment::CONSTANT(), MemorySegment::POINTER()], true)) {
            $memoryAddress = $this->segment === MemorySegment::CONSTANT()
                ? $this->arg
                : ($this->arg === 0 ? 'THIS' : 'THAT');

            $typeDependentPart = sprintf(
                "@{$memoryAddress}
                D=%s
                A=M\n",
                match ($this->type) {
                    MemoryAccessOperationType::PUSH() =>
                        "M
                        @SP\n",
                    MemoryAccessOperationType::POP() =>
                        "A\n"
                }
            );
        } elseif (in_array($this->segment, [MemorySegment::STATIC(), MemorySegment::TEMP()], true)) {
            $memoryAddress = $this->segment === MemorySegment::STATIC()
                ? "{$this->filename}.{$this->arg}"
                : 5 + $this->arg;

            if ($this->type === MemoryAccessOperationType::PUSH()) {
                $typeDependentPart =
                    "@{$memoryAddress}
                    D=M
                                        
                    @SP
                    A=M\n";
            } else {
                $typeDependentPart =
                    "@SP
                    A=M
                    D=M
                    
                    @{$memoryAddress}\n";
            }
        } else {
            $typeDependentPart = sprintf(
                "@{$this->segment->getHackSegmentAlias()}
                D=M
                @{$this->arg}
                D=D+A\n
                %s
                A=M\n",
                match ($this->type) {
                    MemoryAccessOperationType::PUSH() =>
                        "A=D
                        D=M
                        
                        @SP\n",
                    MemoryAccessOperationType::POP() =>
                        "@R13
                        M=D            
                        
                        @SP
                        A=M
                        D=M            
                        
                        @R13\n"
                }
            );
        }

        return
            "$typeDependentPart
            M=D        
            
            @SP
            M=M{$changeStackPositionOperator}1\n";
    }

    public static function getRegexp(): string
    {
        return '/^(push|pop) (local|argument|this|that|constant|static|pointer|temp) ([0-9]+)$/';
    }
}