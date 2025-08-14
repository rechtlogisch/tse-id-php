<?php

use Rechtlogisch\TseId\Retrieve;

// Retrieve data from BSI only once
$retrieve = (function () {
    return new Retrieve;
})();

it('retrieve data from BSI website', function () use (&$retrieve) {
    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->not->toBeEmpty()
        ->and(count($list))->toBeGreaterThanOrEqual(19);
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
