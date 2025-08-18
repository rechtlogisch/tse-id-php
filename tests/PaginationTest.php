<?php

use Rechtlogisch\TseId\Retrieve;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('parses multiple pages, sorts keys descending, and formats json correctly', function () {
    $page1 = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
  <body>
    <div id="content">
      <nav class="c-pagination"><p>Search results 1 to 10 from a total of 11</p></nav>
      <div class="wrapperTable">
        <table class="textualData"><tbody>
          <tr>
            <td>BSI-K-TR-0001-2000</td>
            <td>Content 1</td>
            <td>M1</td>
            <td>01.01.2000</td>
          </tr>
        </tbody></table>
      </div>
    </div>
  </body>
</html>
HTML;

    $page2 = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
  <body>
    <div id="content">
      <nav class="c-pagination"><p>Search results 11 to 11 from a total of 11</p></nav>
      <div class="wrapperTable">
        <table class="textualData"><tbody>
          <tr>
            <td>BSI-K-TR-0002-2001</td>
            <td>Content 2</td>
            <td>M2</td>
            <td>02.02.2001</td>
          </tr>
        </tbody></table>
      </div>
    </div>
  </body>
</html>
HTML;

    $client = new MockHttpClient([
        new MockResponse($page1),
        new MockResponse($page2),
    ]);
    $browser = new HttpBrowser($client);

    $retrieve = new Retrieve($browser);

    $list = $retrieve->list();

    expect($list)
        ->toBeArray()
        ->toHaveKeys(['0001-2000', '0002-2001']);

    $keys = array_keys($list);
    $sorted = $keys;
    arsort($sorted, SORT_NATURAL);
    expect($keys)->toBe($sorted);

    $jsonPretty = $retrieve->json();
    $jsonCompact = $retrieve->json(null, false);

    expect($jsonPretty)->toBeJson()
        ->and($jsonCompact)->toBeJson()
        ->and(str_contains($jsonCompact, "\n"))->toBeFalse();

    $single = $retrieve->json('0001-2000', false);
    expect($single)->toBe('{"0001-2000":{"id":"0001","year":"2000","content":"Content 1","manufacturer":"M1","date_issuance":"01.01.2000"}}');
});
