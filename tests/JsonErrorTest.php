<?php

use Rechtlogisch\TseId\Retrieve;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('returns error message when json encoding fails', function () {
    // Minimal valid HTML to build a Retrieve instance
    $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 1 from a total of 1</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0000-2000</td><td>X</td><td>Y</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    /** @noinspection PhpUnhandledExceptionInspection */
    $ref = new ReflectionClass($retrieve);
    /** @noinspection PhpUnhandledExceptionInspection */
    $prop = $ref->getProperty('retrieved');
    /** @noinspection PhpExpressionResultUnusedInspection */
    $prop->setAccessible(true);
    $bad = [
        'bad-2000' => [
            'id' => 'bad',
            'year' => '2000',
            'content' => NAN, // NaN cannot be JSON-encoded with JSON_THROW_ON_ERROR
            'manufacturer' => 'Z',
            'date_issuance' => '01.01.2000',
        ],
    ];
    $prop->setValue($retrieve, $bad);

    $result = $retrieve->json();

    expect($result)
        ->toBeString()
        ->not->toBeJson()
        ->and(strlen($result))->toBeGreaterThan(0);
});
