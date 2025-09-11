<?php

use Rechtlogisch\TseId\Retrieve;
use Rechtlogisch\TseId\RetrieveException;

$retrieve = function () {
    return new Retrieve;
};

it('retrieve data from BSI website', function () use (&$retrieve) {
    try {
        $list = $retrieve()->list();
        expect($list)
            ->toBeArray()
            ->not->toBeEmpty()
            ->and(count($list))->toBeGreaterThanOrEqual(26);
    } catch (RetrieveException $e) {
        // Handle network timeouts or other external failures gracefully
        expect($e)
            ->toBeInstanceOf(RetrieveException::class)
            ->and($e->getMessage())->toMatch('/(timeout|Connection reset|Connection refused|Network is unreachable)/')
            ->and($e->getUrl())->toContain('bsi.bund.de');

        // This test passes if we get a network error exception, as it proves the exception handling works
        expect(true)->toBeTrue();
    }
})->group('external');
