<?php


namespace TaHUoP\Operations;


use TaHUoP\Enums\ArithmeticOperationType;

/*
 * Vm memory access commands implementation examples
//add
@SP
A=M
D=M

@SP
M=M-1';

@SP
A=M
M=D+M

//sub
@SP
A=M
D=M

@SP
M=M-1';

@SP
A=M
M=M-D

//neg
@SP
A=M
M=-M

//and
@SP
A=M
D=M

@SP
M=M-1';

@SP
A=M
M=D&M

//or
@SP
A=M
D=M

@SP
M=M-1';

@SP
A=M
M=D|M

//not
@SP
A=M
M=!M

//eq
@SP
A=M
D=M

@SP
M=M-1';

@SP
A=M
D=D-M

@EQ
D;JEZ

M=0

(EQ)
M=1

//gt
@SP
A=M
D=M

@SP
M=M-1';

@SP
A=M
D=D-M

@GT
D;JLZ

M=0

(GT)
M=1

//lt
@SP
A=M
D=M

@SP
M=M-1';

@SP
A=M
D=D-M

@LT
D;JGZ

M=0

(LT)
M=1
*/

class ArithmeticOperation implements OperationInterface
{
    private ArithmeticOperationType $type;

    public function __construct(ArithmeticOperationType $type)
    {
        $this->type = $type;
    }

    public function getAsmInstructions(): string
    {
        $instruction =
            "@SP
            A=M\n";

        $expression = match ($this->type) {
            ArithmeticOperationType::EQ() => 'JEZ',
            ArithmeticOperationType::GT() => 'JLZ',
            ArithmeticOperationType::LT() => 'JGZ',
            ArithmeticOperationType::ADD() => 'D+M',
            ArithmeticOperationType::SUB() => 'M-D',
            ArithmeticOperationType::AND() => 'D&M',
            ArithmeticOperationType::OR() => 'D|M',
            ArithmeticOperationType::NEG() => '-',
            ArithmeticOperationType::NOT() => '!',
        };

        if (in_array($this->type, [ArithmeticOperationType::NEG(), ArithmeticOperationType::NOT()], true)) {
            $instruction .= "M={$expression}M";
        } else {
            $instruction .=
                "D=M
                
                @SP
                M=M-1';
                
                @SP
                A=M\n" .
                match ($this->type) {
                    ArithmeticOperationType::EQ(), ArithmeticOperationType::GT(), ArithmeticOperationType::LT() =>
                        "D=D-M
                        
                        @TRUE
                        D;{$expression}
                        
                        M=0
                        
                        (TRUE)
                        M=1\n",
                    ArithmeticOperationType::ADD(), ArithmeticOperationType::SUB(), ArithmeticOperationType::AND(),
                    ArithmeticOperationType::OR() =>
                        "M={$expression}\n",
                };
        }

        return $instruction;
    }

    public static function getRegexp(): string
    {
        return '/^(add|sub|neg|eq|gt|lt|and|or|not)$/';
    }
}