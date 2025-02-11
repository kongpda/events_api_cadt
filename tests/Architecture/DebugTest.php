<?php

declare(strict_types=1);

test('debug functions are not used in the codebase')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed()
    ->ignoring(['tests', 'database/factories', 'database/seeders']);

test('laravel debug helpers are not used in the codebase')
    ->expect(['debug', 'Log::debug'])
    ->not->toBeUsed()
    ->ignoring(['tests', 'database/factories', 'database/seeders']);

test('no debugging packages are used in production')
    ->expect('App')
    ->not->toUse([
        'Barryvdh\Debugbar',
        'Laravel\Telescope',
        'Spatie\Ray',
    ])
    ->ignoring(['tests', 'database/factories', 'database/seeders', 'App\Providers']);
