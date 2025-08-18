<?php

use Rechtlogisch\TseId\Retrieve;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('returns empty path when saving to a non-writable directory', function () {
    $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 10 from a total of 1</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0000-2000</td><td>X</td><td>Y</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

    $client = new MockHttpClient([new MockResponse($html)]);
    $browser = new HttpBrowser($client);
    $retrieve = new Retrieve($browser);

    $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'no_write_'.uniqid('', true);
    mkdir($dir, 0755);
    chmod($dir, 0555); // make directory non-writable

    try {
        $result = $retrieve->save($dir);

        expect($result)->toBeArray()->toHaveKey('json')
            ->and($result['json'])->toBe('');
    } finally {
        // restore permissions so cleanup can remove
        chmod($dir, 0755);
        cleanup($dir);
    }
});
