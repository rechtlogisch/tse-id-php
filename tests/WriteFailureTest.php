<?php

declare(strict_types=1);

namespace Rechtlogisch\TseId {
    // Override namespaced file_put_contents to simulate write failure
    function file_put_contents(string $filename, string $data, int $flags = 0, $context = null): int|false
    {
        if (str_contains($filename, 'force_write_failure')) {
            return false;
        }

        return \file_put_contents($filename, $data, $flags, $context);
    }
}

namespace {

    use Rechtlogisch\TseId\Retrieve;
    use Symfony\Component\BrowserKit\HttpBrowser;
    use Symfony\Component\HttpClient\MockHttpClient;
    use Symfony\Component\HttpClient\Response\MockResponse;

    it('throws when file_put_contents fails', function () {
        $html = '<html lang="en"><body><div id="content"><nav class="c-pagination"><p>Search results 1 to 10 from a total of 1</p></nav><div class="wrapperTable"><table class="textualData"><tbody><tr><td>BSI-K-TR-0000-2000</td><td>X</td><td>Y</td><td>01.01.2000</td></tr></tbody></table></div></div></body></html>';

        $client = new MockHttpClient([new MockResponse($html)]);
        $browser = new HttpBrowser($client);
        $retrieve = new Retrieve($browser);

        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'force_write_failure_'.uniqid('', true);
        mkdir($dir);

        // Our override forces file_put_contents to return false => RuntimeException thrown
        expect(fn () => $retrieve->save($dir))->toThrow(RuntimeException::class);

        cleanup($dir);
    });

}
