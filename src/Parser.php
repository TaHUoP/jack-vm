<?php


namespace TaHUoP;


use Exception;
use InvalidArgumentException;
use TaHUoP\Operations\OperationFactory;

class Parser
{
    private const COMMENT_REGEX = '/\/\/.*$/';

    /**
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    public function parseFile(string $filePath): string
    {
        if(!str_ends_with($filePath, '.vm'))
            throw new InvalidArgumentException('Only .vm extension is supported');

        if(!is_readable($filePath))
            throw new InvalidArgumentException("Unable to read from $filePath");

        $lines = [];
        $lineNum = 0;
        foreach (file($filePath) as $originalLineNum => $line) {
            $line = trim(preg_replace(self::COMMENT_REGEX, '', $line));

            if (preg_match('/^\s*$/', $line))
                continue;

            $lines[]= new Instruction($line, $lineNum, $originalLineNum);
            $lineNum++;
        }

        $content = '';
        /** @var Instruction $instruction */
        foreach ($lines as $key => $instruction) {
            $content .= ($key != 0 ? PHP_EOL : '') . OperationFactory::getOperation($instruction, basename($filePath, '.vm'))->getAsmInstructions();
        }

        return $content;
    }
}