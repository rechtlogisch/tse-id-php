<?php

declare(strict_types=1);

namespace Rechtlogisch\TseId;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tse-id:retrieve', description: 'Retrieve the TSE list from BSI')]
class RetrieveCommand extends Command
{
    private ?HttpBrowser $browser;

    public function __construct(?HttpBrowser $browser = null)
    {
        parent::__construct();
        $this->browser = $browser;
    }

    protected function configure(): void
    {
        $this->addArgument('targetPath', InputArgument::OPTIONAL, 'The path to save output. Can be relative or absolute. Must not end with a DIRECTORY_SEPARATOR.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Retrieving TSE list from BSI...');
        $arg = $input->getArgument('targetPath');
        $targetPath = is_string($arg) ? trim($arg) : '';
        if ($targetPath === '') {
            $targetPath = '.';
        }

        try {
            $retrieve = new Retrieve($this->browser);
        } catch (RetrieveException $e) {
            $output->writeln('Error while processing URL: '.$e->getUrl());
            $output->writeln('Exception message: '.$e->getMessage());

            $html = $e->getHtml();
            if (! empty($html)) {
                $output->writeln('Response HTML begin >>>');
                $output->writeln($html);
                $output->writeln('<<< [Retrieve] Response HTML end');
            }

            return Command::FAILURE;
        }
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
