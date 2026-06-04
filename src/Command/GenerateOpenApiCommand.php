<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate-openapi',
    description: 'Generates the OpenAPI documentation using swagger-php.'
)]
class GenerateOpenApiCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();
        $docsDir = 'docs';
        $outputFile = $docsDir . '/openapi.yaml';

        // Ensure docs/ directory exists
        if (!$filesystem->exists($docsDir)) {
            $filesystem->mkdir($docsDir);
            $output->writeln('<comment>Created docs/ directory.</comment>');
        }

        // Build the command
        $command = 'vendor/bin/openapi src --format yaml > ' . $outputFile;

        // Execute the command
        $result = null;
        $outputText = null;
        exec($command, $outputText, $result);

        if ($result !== 0) {
            $output->writeln('<error>Failed to generate OpenAPI documentation.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>OpenAPI documentation successfully generated:</info>');
        $output->writeln(' → ' . $outputFile);

        return Command::SUCCESS;
    }
}
