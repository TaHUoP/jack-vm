<?php


namespace TaHUoP;


use Exception;
use InvalidArgumentException;
use TaHUoP\Operations\OperationFactory;

class Parser
{
    private const COMMENT_REGEX = '/\/\/.*$/';

    /**
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

            $asmInstructions = array_reduce(
                $files,
                fn(array $carry, string $path): array => [...$carry, ...$this->parseFileToAsmInstructions($path)],
                []
            );
        } else {
            if(!str_ends_with($path, '.vm'))
                throw new InvalidArgumentException('Only .vm extension is supported');

            $asmInstructions = $this->parseFileToAsmInstructions($path);
        }

        $content = implode(PHP_EOL, [...$this->getSystemBootstrapInstructions(), ...$asmInstructions]);

        return $this->sanitizeWhitespaces($content);
    }

    /**
     * @throws Exception
     */
    private function parseFileToAsmInstructions(string $path): array
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

        $asmInstructions = [];
        /** @var VmInstruction $vmInstruction */
        foreach ($lines as $vmInstruction) {
            $asmInstructions = [
                ...$asmInstructions,
                "//{$vmInstruction->text}",
                ...OperationFactory::getOperation($vmInstruction)->getAsmInstructions()
            ];
        }

        return $asmInstructions;
    }

    private function getSystemBootstrapInstructions(): array
    {
        $vmInstruction = new VmInstruction('call Sys.init 0', 0, 0, '');

        return [
            '@256',
            'D=A',
            '@0',
            'M=D',
            "//{$vmInstruction->text}",
            ...OperationFactory::getOperation($vmInstruction)->getAsmInstructions(),
        ];
    }

    private function sanitizeWhitespaces(string $content): string
    {
        return preg_replace(['/^ +/', '/\n +/'], ['', PHP_EOL], $content);
    }
}
