<?php

use Rechtlogisch\TseId\RetrieveCommand;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('executes the command successfully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
  <body>
    <div id="content">
      <nav class="c-pagination"><p>Search results 1 to 10 from a total of 1</p></nav>
      <div class="wrapperTable">
        <table class="textualData">
          <tbody>
            <tr>
              <td>BSI-K-TR-9999-2099</td>
              <td>Test Content</td>
              <td>ACME</td>
              <td>01.01.2099</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
HTML;

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);

    $application = new Application;
    $application->addCommand(new RetrieveCommand($browser));

    $command = $application->find('tse-id:retrieve');

    $commandTester = new CommandTester($command);

    $commandTester->execute([]);

    $display = $commandTester->getDisplay();

    expect($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($display)
        ->toContain('Retrieving TSE list from BSI...')
        ->toContain('Retrieved listed saved to: ')
        ->toContain('.json');

    cleanup(__DIR__);
});

it('fails and prints details when retrieval throws RetrieveException', function () {
    $html = '<html lang="en"><head><title>Fail</title></head><body><p>No required content</p></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);

    $application = new Application;
    $application->addCommand(new RetrieveCommand($browser));

    $command = $application->find('tse-id:retrieve');
    $tester = new CommandTester($command);

    $tester->execute([]);

    $display = $tester->getDisplay();

    expect($tester->getStatusCode())->toBe(Command::FAILURE)
        ->and($display)
        ->toContain('Error while processing URL: ')
        ->toContain('Exception message: The current node list is empty.')
        ->toContain('Response HTML begin >>>')
        ->toContain('<<< [Retrieve] Response HTML end')
        ->toContain('No required content');
});

it('fails with message when saving to invalid targetPath', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
  <body>
    <div id="content">
      <nav class="c-pagination"><p>Search results 1 to 10 from a total of 1</p></nav>
      <div class="wrapperTable">
        <table class="textualData">
          <tbody>
            <tr>
              <td>BSI-K-TR-0000-2000</td>
              <td>Content</td>
              <td>Maker</td>
              <td>01.01.2000</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
HTML;

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);

    $app = new Application;
    $app->addCommand(new RetrieveCommand($browser));

    $command = $app->find('tse-id:retrieve');
    $tester = new CommandTester($command);

    $status = $tester->execute(['targetPath' => 'does/not/exist/path']);
    $output = $tester->getDisplay();

    expect($status)->toBe(Command::FAILURE)
        ->and($output)
        ->toContain('Retrieving TSE list from BSI...')
        ->toContain('Failed to retrieve TSE list from BSI');
});
