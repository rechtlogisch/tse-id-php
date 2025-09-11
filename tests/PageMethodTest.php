<?php

use Rechtlogisch\TseId\Retrieve;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('calls page method directly with different page numbers', function () {
    // Mock HTML for page 1 with pagination info (25 total results = 3 pages)
    $html1 = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 10 from a total of 25</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0001-2000</td><td>Content 1</td><td>Manufacturer 1</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

    // Mock HTML for page 2
    $html2 = '<html lang="en"><body><div id="content"><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0002-2000</td><td>Content 2</td><td>Manufacturer 2</td><td>02.01.2000</td></tr></tbody></table></div></div></body></html>';

    // Mock HTML for page 3
    $html3 = '<html lang="en"><body><div id="content"><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0003-2000</td><td>Content 3</td><td>Manufacturer 3</td><td>03.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([
        new MockResponse($html1), // First call for page 1 (constructor)
        new MockResponse($html2), // Second call for page 2 (constructor)
        new MockResponse($html3), // Third call for page 3 (constructor)
        new MockResponse($html2), // Fourth call for page 2 (direct call)
    ]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    // Test calling page method directly with page 2 again
    $retrieve->page(2);

    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->toHaveKey('0001-2000')
        ->toHaveKey('0002-2000')
        ->toHaveKey('0003-2000')
        ->and($list['0002-2000']['id'])->toBe('0002')
        ->and($list['0002-2000']['year'])->toBe('2000')
        ->and($list['0002-2000']['content'])->toBe('Content 2')
        ->and($list['0002-2000']['manufacturer'])->toBe('Manufacturer 2')
        ->and($list['0002-2000']['date_issuance'])->toBe('02.01.2000');
});

it('handles page method with different switch cases', function () {
    // Mock HTML with all different cell types to test switch cases
    $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 1 from a total of 1</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0003-2000</td><td>Content 3</td><td>Manufacturer 3</td><td>03.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    // The constructor already calls page(1), so we're testing the switch cases
    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->toHaveKey('0003-2000')
        ->and($list['0003-2000']['id'])->toBe('0003')
        ->and($list['0003-2000']['year'])->toBe('2000')
        ->and($list['0003-2000']['content'])->toBe('Content 3')
        ->and($list['0003-2000']['manufacturer'])->toBe('Manufacturer 3')
        ->and($list['0003-2000']['date_issuance'])->toBe('03.01.2000');
});
