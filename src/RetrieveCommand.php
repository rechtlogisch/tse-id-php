<?php

namespace Rechtlogisch\TseId;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tse-id:retrieve', description: 'Retrieve the TSE list from BSI')]
class RetrieveCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Retrieving TSE list from BSI...');

        $retrieve = new Retrieve;
        $result = $retrieve->save('list');

        if (empty($result) || empty($result[array_key_first($result)])) {
            $output->writeln('Failed to retrieve TSE list from BSI');

            return Command::FAILURE;
        }

        $path = $result[array_key_first($result)];

        $output->writeln('Retrieved listed saved to: '.$path);

        unlink($path);

        return Command::SUCCESS;
    }
}
