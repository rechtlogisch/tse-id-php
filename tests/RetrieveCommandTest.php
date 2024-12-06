<?php

use Rechtlogisch\TseId\RetrieveCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

it('executes the command successfully', function () {
    $application = new Application;
    $application->add(new RetrieveCommand);

    $command = $application->find('tse-id:retrieve');

    $commandTester = new CommandTester($command);

    $commandTester->execute([]);

    expect($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($commandTester->getDisplay())
        ->toContain('Retrieving TSE list from BSI...')
        ->toContain('Retrieved listed saved to: ')
        ->toContain('.json');
});
