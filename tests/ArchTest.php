<?php

test('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'echo', 'print_r'])
    ->not->toBeUsed()
    ->group('arch');

test('use strict mode')
    ->expect('Rechtlogisch\TseId')
    ->toUseStrictTypes()
    ->group('arch');
