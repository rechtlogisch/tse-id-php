<?php

use Rechtlogisch\TseId\Retrieve;

$retrieve = (function () {
    return new Retrieve;
})();

it('retrieve data from BSI website', function () use (&$retrieve) {
    $list = $retrieve->list();
    expect($list)
        ->toBeArray()
        ->not->toBeEmpty()
        ->and(count($list))->toBeGreaterThanOrEqual(26);
})->group('external');
