<?php

// 万方商事 B2B 采购门户 PHP CS Fixer 配置
// 代码风格统一和自动化修复

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database/factories',
        __DIR__ . '/database/seeders',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'bootstrap',
        'storage',
        'vendor',
        'node_modules',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        // PSR-12 规则
        '@PSR12' => true,
        
        // Laravel 规则集
        '@Laravel' => true,
        
        // PHP 8+ 特性
        '@PHP82Migration' => true,
        '@PHP82Migration:risky' => true,
        
        // 严格规则
        '@DoctrineAnnotation' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        
        // 数组规则
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,
        
        // 类和接口规则
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'no_blank_lines_after_class_opening' => true,
        'no_php4_constructor' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_public',
                'property_protected',
                'property_private',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        
        // 控制结构规则
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'case',
                'continue',
                'declare',
                'default',
                'exit',
                'goto',
                'include',
                'include_once',
                'phpdoc',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'yield',
                'yield_from',
            ],
        ],
        'curly_braces_position' => [
            'control_structures_opening_brace' => 'same_line',
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'anonymous_functions_opening_brace' => 'same_line',
            'classes_opening_brace' => 'next_line',
            'anonymous_classes_opening_brace' => 'same_line',
            'loops_opening_brace' => 'same_line',
        ],
        'no_superfluous_elseif' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_curly_braces' => true,
        'no_unneeded_final_method' => true,
        'no_unused_imports' => true,
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,
        
        // 函数和方法规则
        'function_declaration' => [
            'closure_function_spacing' => 'none',
            'closure_fn_spacing' => 'none',
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
        ],
        'no_spaces_after_function_name' => true,
        'no_spaces_inside_parenthesis' => true,
        'spaces_inside_parentheses' => false,
        'static_lambda' => true,
        
        // 导入规则
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'no_leading_import_slash' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => true,
        
        // 运算符规则
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'align',
                '=' => 'align_single_space_minimal',
            ],
        ],
        'concat_space' => ['spacing' => 'one'],
        'no_space_around_double_colon' => true,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => [
            'only_booleans' => true,
            'position' => 'end',
        ],
        
        // 分号规则
        'multiline_whitespace_before_semicolons' => false,
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        
        // 字符串规则
        'escape_implicit_backslashes' => true,
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'simple_to_complex_string_variable' => true,
        'single_quote' => true,
        
        // 类型规则
        'cast_spaces' => ['space' => 'none'],
        'lowercase_cast' => true,
        'modernize_types_casting' => true,
        'no_short_bool_cast' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        
        // 变量规则
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                'use',
            ],
        ],
        'no_trailing_whitespace' => true,
        'single_blank_line_at_eof' => true,
        'single_blank_line_before_namespace' => true,
        'spaces_inside_parentheses' => false,
        
        // 注释规则
        'comment_to_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => [
            'align' => 'vertical',
            'tags' => [
                'param',
                'property',
                'return',
                'throws',
                'type',
                'var',
            ],
        ],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,
        
        // Laravel 特定规则
        'laravel_braces' => true,
        'no_alias_functions' => true,
        'no_unset_on_property' => true,
        
        // 风险规则
        'dir_constant' => true,
        'ereg_to_preg' => true,
        'error_suppression' => true,
        'fopen_flag_order' => true,
        'fopen_flags' => ['b_mode' => true],
        'function_to_constant' => true,
        'is_null' => true,
        'logical_operators' => true,
        'mb_str_functions' => true,
        'modernize_types_casting' => true,
        'native_constant_invocation' => true,
        'native_function_casing' => true,
        'native_function_invocation' => [
            'include' => ['@all'],
            'scope' => 'all',
            'strict' => true,
        ],
        'no_alias_language_construct_call' => true,
        'no_homoglyph_names' => true,
        'no_mixed_echo_print' => true,
        'no_unreachable_default_argument_value' => true,
        'non_printable_character' => [
            'use_escape_sequences_in_strings' => true,
        ],
        'ordered_interfaces' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_dedicate_assert_internal_type' => true,
        'php_unit_expectation' => true,
        'php_unit_fqcn_annotation' => true,
        'php_unit_internal_class' => true,
        'php_unit_method_casing' => true,
        'php_unit_mock' => true,
        'php_unit_namespaced' => true,
        'php_unit_no_expectation_annotation' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_strict' => true,
        'php_unit_test_annotation' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
        'psr_autoloading' => true,
        'self_accessor' => true,
        'set_type_to_cast' => true,
        'string_line_ending' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'unary_operator_spaces' => true,
        'void_return' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/storage/.php-cs-fixer.cache')
    ->setRiskyAllowed(true)
    ->setUsingCache(true);