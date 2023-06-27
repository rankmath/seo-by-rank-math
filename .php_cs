<?php
/*
 * Not using Automatic PHPCS Fixer just because PHPCS Fixer doesn't have option:
 * - To add extra space after opening parenthesis and before closing parenthesis
 * - To add extra space after '!' mark
 */

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@Symfony' => true,
            '@Symfony:risky' => true,
            '@PHP71Migration' => true,
            'array_syntax' => ['syntax' => 'short'],
            'dir_constant' => true,
            'no_spaces_inside_parenthesis' => false,
            'linebreak_after_opening_tag' => true,
            'no_multiline_whitespace_before_semicolons' => true,
            'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
            'phpdoc_order' => true,
            'concat_space' => [ 'spacing' => 'one' ],
            'phpdoc_annotation_without_dot' => false,
            'doctrine_annotation_braces' => true,
            'doctrine_annotation_indentation' => true,
            'doctrine_annotation_spaces' => ['after_argument_assignments' => true],
            'doctrine_annotation_array_assignment' => true,
            'psr4' => true,
            'no_php4_constructor' => true,
            'semicolon_after_instruction' => true,
            'align_multiline_comment' => true,
            'general_phpdoc_annotation_remove' => ['annotations' => ["author", "package"]],
            'list_syntax' => ["syntax" => "short"],
            'phpdoc_types_order' => ['null_adjustment'=> 'always_last'],
            'single_line_comment_style' => ['comment_types' => ['hash']],
            'binary_operator_spaces' => [
                'align_double_arrow' => true,
            ],
            'braces' => [
                'allow_single_line_closure' => true,
                'position_after_functions_and_oop_constructs' => 'same',
            ],
            'blank_line_before_return' => true,
            'trim_array_spaces' => false,
        ]
    )
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setCacheFile(__DIR__.'/.php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    );