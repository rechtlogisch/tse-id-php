<?php

use Rechtlogisch\TseId\Retrieve;
use Rechtlogisch\TseId\RetrieveException;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('stores and returns context correctly', function () {
    $prev = new RuntimeException('prev');
    $e = new RetrieveException('msg', 123, $prev);

    $url = 'https://example.com/x';
    $html = '<html lang="en">body</html>';

    $e->addContext($url, $html);

    expect($e->getMessage())->toBe('msg')
        ->and($e->getCode())->toBe(123)
        ->and($e->getPrevious())->toBe($prev)
        ->and($e->getUrl())->toBe($url)
        ->and($e->getHtml())->toBe($html);
});

it('dumps response HTML when parsing fails', function () {
    $html = '<html lang="en"><head><title>Test Page</title></head><body><h1>No pagination here</h1></body></html>';

    $client = new MockHttpClient(new MockResponse($html));
    $browser = new HttpBrowser($client);

    try {
        new Retrieve($browser);
    } catch (RetrieveException $e) {
        expect($e->getMessage())->toBe('The current node list is empty.')
            ->and($e->getUrl())->toBe(Retrieve::URL.'1') // first page
            ->and($e->getHtml())->toBe($html);
    }
});
