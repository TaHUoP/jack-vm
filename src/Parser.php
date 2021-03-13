<?php


namespace TaHUoP;


use Exception;
use InvalidArgumentException;
use TaHUoP\Operations\OperationFactory;

class Parser
{
    private const COMMENT_REGEX = '/\/\/.*$/';

    /**
     * @param string $path
     * @return string
     * @throws Exception
     */
    public function parse(string $path): string
    {
        if(!is_readable($path))
            throw new InvalidArgumentException("Unable to read from $path");

        if (is_dir($path)) {
            $files = array_filter(scandir($path), fn(string $file): bool => is_file($file) && str_ends_with($path, '.vm'));

            if (!$files)
                throw new InvalidArgumentException('Directory doesn\'t contain readable .vm files');

            return implode(PHP_EOL, array_map([$this, 'parseFile'], $files));
        } else {
            if(!str_ends_with($path, '.vm'))
                throw new InvalidArgumentException('Only .vm extension is supported');

            return $this->parseFile($path);
        }
    }

    /**
     * @param string $path
     * @return string
     * @throws Exception
     */
    private function parseFile(string $path): string
    {
        $lines = [];
        $lineNum = 0;
        foreach (file($path) as $originalLineNum => $line) {
            $line = trim(preg_replace(self::COMMENT_REGEX, '', $line));

            if (preg_match('/^\s*$/', $line))
                continue;

            $lines[]= new VmInstruction($line, $lineNum, $originalLineNum, basename($path, '.vm'));
            $lineNum++;
        }

        $content = '';
        /** @var VmInstruction $vmInstruction */
        foreach ($lines as $key => $vmInstruction) {
            $content .= ($key != 0 ? PHP_EOL : '') .
                preg_replace(
                    ['/^ +/', '/\n +/'],
                    ['', PHP_EOL],
                    OperationFactory::getOperation($vmInstruction)->getAsmInstructions()
                );
        }

        return $content;
    }
}