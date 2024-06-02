<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer:risky' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'global_namespace_import' => ['import_classes' => false, 'import_constants' => false, 'import_functions' => false],
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'class_attributes_separation' => ['elements' => ['const' => 'none', 'method' => 'one', 'property' => 'only_if_meta', 'trait_import' => 'none', 'case' => 'none']],
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true]
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
