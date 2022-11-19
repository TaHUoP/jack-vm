<?php

namespace TaHUoP;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class App extends SingleCommandApplication
{
    public function __construct(
        private readonly Parser $parser
    ) {
        parent::__construct();
        $this
            ->addArgument('inputPath', InputArgument::REQUIRED, 'Path to .vm file or directory with .vm files')
            ->addArgument('outputFilePath', InputArgument::OPTIONAL, 'Path to .asm file')
            ->addArgument('memoryLimit', InputArgument::OPTIONAL, 'PHP memory limit. Unlimited by default')
            ->setCode([$this, 'main']);
    }

    public function main(InputInterface $input, OutputInterface $output): void
    {
        try {
            ini_set('memory_limit', $input->getArgument('memoryLimit') ?? -1);

            $inputPath = $input->getArgument('inputPath');
            $outputFileContent = $this->parser->parse($inputPath);

            $outputFilePath = $input->getArgument('outputFilePath') ?? $this->getOutputFilePath($inputPath);
            if (file_put_contents($outputFilePath, $outputFileContent) !== false) {
                $output->writeln("File $outputFilePath was successfully built.");
            } else {
                $output->writeln("<fg=red>Unable to write to file $outputFilePath.</>");
            }
        } catch (Exception $e) {
            $output->writeln('<fg=red>' . $e->getMessage() . '</>');
        }
    }

    private function getOutputFilePath(string $inputPath): string
    {
        $pathInfo = pathinfo($inputPath);
        $inputPath = rtrim((is_dir($inputPath) ? $inputPath : $pathInfo['dirname']), DIRECTORY_SEPARATOR);

        return $inputPath . DIRECTORY_SEPARATOR . "{$pathInfo['filename']}.asm";
    }
}
