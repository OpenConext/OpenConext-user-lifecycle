<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../../bin',
        __DIR__ . '/../../config',
        __DIR__ . '/../../src',
        __DIR__ . '/../../tests',
    ])
    ->withPhpSets()
    ->withComposerBased(doctrine: true, symfony: true, phpunit: true)
    ->withAttributesSets(doctrine: true, symfony: true, phpunit: true)
    ->withSkip([
        \Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector::class,
        \Rector\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector::class,
        // Produces invalid PHP ("self::getContainer() = ...") when self::$container is
        // used as an assignment target (this repo's own legacy self-declared static
        // $container property), not just as a read. Fixed manually in the 3 affected
        // integration Command tests instead.
        \Rector\Symfony\Symfony53\Rector\StaticPropertyFetch\KernelTestCaseContainerPropertyDeprecationRector::class,
    ]);
