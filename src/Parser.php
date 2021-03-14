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
            $files = array_filter(
                array_map(
                    fn(string $file): string => $path . DIRECTORY_SEPARATOR . $file,
                    scandir($path)
                ),
                fn(string $file): bool => is_file($file) && str_ends_with($file, '.vm')
            );

            if (!$files)
                throw new InvalidArgumentException('Directory doesn\'t contain readable .vm files');

            $content = implode(PHP_EOL, array_map([$this, 'parseFile'], $files));
        } else {
            if(!str_ends_with($path, '.vm'))
                throw new InvalidArgumentException('Only .vm extension is supported');

            $content = $this->parseFile($path);
        }

        return  preg_replace(
            ['/^ +/', '/\n +/'],
            ['', PHP_EOL],
            $this->getSystemBootstrapInstructions() . PHP_EOL . $content
        );
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
            $content .= ($key != 0 ? PHP_EOL : '') . OperationFactory::getOperation($vmInstruction)->getAsmInstructions();
        }

        return $content;
    }

    private function getSystemBootstrapInstructions(): string {
        return implode(PHP_EOL, [
            '@256',
            'D=A',
            '@0',
            'M=D',
            OperationFactory::getOperation(new VmInstruction('call Sys.init 0', 0, 0, ''))
                ->getAsmInstructions(),
        ]);
    }
}