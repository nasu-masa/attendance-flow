<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

return (new Config())
    ->setRules([
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'no_unused_imports' => true,
        'indentation_type' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
