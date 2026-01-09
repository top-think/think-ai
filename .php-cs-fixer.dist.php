<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in(__DIR__)
;

return (new Config())
    ->setRules([
        '@PhpCsFixer'            => true,
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],
    ])
    ->setFinder($finder)
;
