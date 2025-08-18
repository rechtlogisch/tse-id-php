<?php

use Rechtlogisch\TseId\Retrieve;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('returns empty path when saving to a non-existing nested directory', function () {
    $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 10 from a total of 1</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0000-2000</td><td>X</td><td>Y</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    // Deep relative path that does not exist; save() does not create directories
    $result = $retrieve->save('does/not/exist/path');

    // For json entry, path should be empty string on failure
    expect($result)->toBeArray()->toHaveKey('json')
        ->and($result['json'])->toBe('');
});
