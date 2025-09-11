<?php

use Rechtlogisch\TseId\Retrieve;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('tests edge case for page method with different pagination scenarios', function () {
    // Test with a single page result (no pagination needed)
    $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 5 from a total of 5</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0001-2000</td><td>Content 1</td><td>Manufacturer 1</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->toHaveKey('0001-2000')
        ->and($list['0001-2000']['id'])->toBe('0001')
        ->and($list['0001-2000']['year'])->toBe('2000');
});

it('tests page method with exact page boundary', function () {
    // Test with exactly 10 results (1 page)
    $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 10 from a total of 10</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0001-2000</td><td>Content 1</td><td>Manufacturer 1</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->toHaveKey('0001-2000')
        ->and($list['0001-2000']['id'])->toBe('0001')
        ->and($list['0001-2000']['year'])->toBe('2000');
});

it('tests page method with 11 results (2 pages)', function () {
    // Test with 11 results (2 pages)
    $html1 = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 10 from a total of 11</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0001-2000</td><td>Content 1</td><td>Manufacturer 1</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';
    $html2 = '<html lang="en"><body><div id="content"><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0002-2000</td><td>Content 2</td><td>Manufacturer 2</td><td>02.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([
        new MockResponse($html1),
        new MockResponse($html2),
    ]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->toHaveKey('0001-2000')
        ->toHaveKey('0002-2000')
        ->and($list['0001-2000']['id'])->toBe('0001')
        ->and($list['0002-2000']['id'])->toBe('0002');
});

it('handles invalid pagination text gracefully', function () {
    // Mock HTML with invalid pagination text that doesn't match the regex
    $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Invalid pagination text</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0001-2000</td><td>Content 1</td><td>Manufacturer 1</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->toHaveKey('0001-2000')
        ->and($list['0001-2000']['id'])->toBe('0001')
        ->and($list['0001-2000']['year'])->toBe('2000');
});

it('handles missing pagination element', function () {
    // Mock HTML with pagination element but empty text
    $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p></p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0001-2000</td><td>Content 1</td><td>Manufacturer 1</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->toHaveKey('0001-2000')
        ->and($list['0001-2000']['id'])->toBe('0001')
        ->and($list['0001-2000']['year'])->toBe('2000');
});
