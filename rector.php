<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Rector\MethodCall\RedirectRouteToToRouteHelperRector;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\Packages\Livewire\LivewireSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/public',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/**/vendor/*',
        __DIR__ . '/bootstrap/cache/*',
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withPhpSets(php84: true)
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withSets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_84,
        LaravelSetList::LARAVEL_110,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LivewireSetList::LIVEWIRE_30,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        strictBooleans: true,
        carbon: true,
    )
    ->withRules([
        RedirectRouteToToRouteHelperRector::class,
    ])
    ->withImportNames();
