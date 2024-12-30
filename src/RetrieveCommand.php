<?php

declare(strict_types=1);

namespace Rechtlogisch\TseId;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tse-id:retrieve', description: 'Retrieve the TSE list from BSI')]
class RetrieveCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('targetPath', InputArgument::OPTIONAL, 'The path to save output. Can be relative or absolute. Must not end with a DIRECTORY_SEPARATOR.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Retrieving TSE list from BSI...');
        $targetPath = $input->getArgument('targetPath');

        if (is_string($targetPath)) {
            $targetPath = trim($targetPath);
        }

        if (empty($targetPath)) {
            $targetPath = '.';
        }

        $retrieve = new Retrieve;
        $result = $retrieve->save($targetPath);

        if (empty($result) || empty($result[array_key_first($result)])) {
            $output->writeln('Failed to retrieve TSE list from BSI');

            return Command::FAILURE;
        }

        $path = $result[array_key_first($result)];

        $output->writeln('Retrieved listed saved to: '.$path);

        return Command::SUCCESS;
    }
}
