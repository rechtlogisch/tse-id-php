<?php

use Rechtlogisch\TseId\Retrieve;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

// Mock page only once
$retrieve = (function () {
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
              <td>BSI-K-TR-0362-2019</td>
              <td>Swissbit TSE, Version 1.0 Swissbit USB TSE Swissbit SD TSE Swissbit microSD TSE</td>
              <td>Swissbit AG</td>
              <td>20.12.2019</td>
            </tr>
            <tr>
              <td>BSI-K-TR-9999-2099</td>
              <td>Another Content</td>
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

    return new Retrieve($browser);
})();

it('retrieve data from website (mocked)', function () use (&$retrieve) {
    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->not->toBeEmpty()
        ->and(count($list))->toBeGreaterThanOrEqual(1);
});

it('retrieve and returns json', function () use (&$retrieve) {
    $json = $retrieve->json();
    expect($json)
        ->toBeJson();
});

it('retrieve in desired format', function () use (&$retrieve) {
    $desiredOutput = <<<'JSON'
{"0362-2019":{"id":"0362","year":"2019","content":"Swissbit TSE, Version 1.0 Swissbit USB TSE Swissbit SD TSE Swissbit microSD TSE","manufacturer":"Swissbit AG","date_issuance":"20.12.2019"}}
JSON;

    $list = $retrieve->json('0362-2019', false);
    expect($list)
        ->toBeJson()
        ->toBe($desiredOutput);
});

it('retrieve and save data to file', function () use (&$retrieve) {
    $files = $retrieve->save();

    /** @noinspection PhpUnusedLocalVariableInspection */
    foreach ($files as $type => $path) {
        expect($path)
            ->toBeFile()
            ->toBeReadableFile();

        $content = file_get_contents($path);

        expect($content)
            ->toBeJson()
            ->not->toBeEmpty();

        unlink($path);
    }
});

it('retrieve and check the retrieved keys are in descending order', function () use (&$retrieve) {
    $list = $retrieve->list();
    $keys = array_keys($list);

    $sorted = $keys;
    arsort($sorted, SORT_NATURAL);

    /** @noinspection JsonEncodingApiUsageInspection */
    expect($keys)->toBe($sorted)
        ->and(count($keys))->toBe(count($sorted))
        ->and(array_values($keys))->toBe(array_values($sorted))
        ->and(json_encode($keys))->toBe(json_encode($sorted));
});
