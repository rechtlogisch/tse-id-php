<?php

declare(strict_types=1);

namespace Rechtlogisch\TseId {
    // Override namespaced realpath to simulate failure after a successful write
    function realpath(string $path): string|false
    {
        if (str_contains($path, 'force_realpath_false')) {
            return false;
        }

        return \realpath($path);
    }
}

namespace {

    use Rechtlogisch\TseId\Retrieve;
    use Symfony\Component\BrowserKit\HttpBrowser;
    use Symfony\Component\HttpClient\MockHttpClient;
    use Symfony\Component\HttpClient\Response\MockResponse;

    it('handles realpath() returning false after saving', function () {
        $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 10 from a total of 1</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0000-2000</td><td>X</td><td>Y</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

        $client = new MockHttpClient([new MockResponse($html)]);
        $browser = new HttpBrowser($client);
        $retrieve = new Retrieve($browser);

        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'force_realpath_false_'.uniqid('', true);
        mkdir($dir);

        // write succeeds, but our overridden realpath forces false => RuntimeException thrown
        expect(fn () => $retrieve->save($dir))->toThrow(RuntimeException::class);

        cleanup($dir);
    });

}
