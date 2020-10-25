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
        if(!is_readable($filePath))
            throw new InvalidArgumentException("Unable to read from $filePath");

        $lines = [];
        $lineNum = 0;
        foreach (file($filePath) as $originalLineNum => $line) {
            $line = trim(str_replace(' ', '', preg_replace(self::COMMENT_REGEX, '', $line)));

            if (!$line)
                continue;

            $lines[]= new Instruction($line, $lineNum, $originalLineNum);
            $lineNum++;
        }

        $content = '';
        /** @var Instruction $instruction */
        foreach ($lines as $key => $instruction) {
            $content .= ($key != 0 ? PHP_EOL : '') . OperationFactory::getOperation($instruction)->getAsmInstructions();
        }

        return $content;
    }
}