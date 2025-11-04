<?php
/**
 * PHP Syntax Checker for ImproveSEO Plugin
 * 
 * This script checks all PHP files for syntax errors
 * Run from command line: php check-syntax.php
 */

$files_to_check = [
    'modules/single_AI_post_function.php',
    'modules/bulk_AI_post_function.php',
    'modules/single_and_bulk_AI_post_function.php',
    'improveseo.php',
    'includes/crons.php'
];

echo "=== ImproveSEO Plugin Syntax Checker ===\n\n";

$errors_found = false;

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    
    if (!file_exists($full_path)) {
        echo "❌ FILE NOT FOUND: $file\n";
        $errors_found = true;
        continue;
    }
    
    echo "Checking: $file ... ";
    
    // Check syntax using php -l
    $output = [];
    $return_var = 0;
    exec("php -l " . escapeshellarg($full_path) . " 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "✅ OK\n";
    } else {
        echo "❌ SYNTAX ERROR\n";
        echo "  " . implode("\n  ", $output) . "\n";
        $errors_found = true;
    }
}

echo "\n";

if ($errors_found) {
    echo "❌ ERRORS FOUND! Fix the syntax errors above.\n";
    exit(1);
} else {
    echo "✅ All files passed syntax check!\n";
    exit(0);
}
