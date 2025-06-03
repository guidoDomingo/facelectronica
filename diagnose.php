<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Operating System: " . PHP_OS . "\n";
echo "Current working directory: " . getcwd() . "\n";
echo "Script location: " . __FILE__ . "\n";
echo "Loaded extensions: " . implode(", ", get_loaded_extensions()) . "\n";
echo "Memory limit: " . ini_get('memory_limit') . "\n";
echo "Max execution time: " . ini_get('max_execution_time') . "\n";
echo "Output buffering: " . (ob_get_level() ? "Enabled" : "Disabled") . "\n";
echo "Error reporting: " . ini_get('error_reporting') . "\n";
echo "Display errors: " . ini_get('display_errors') . "\n";

try {
    if (!file_exists('vendor/autoload.php')) {
        throw new Exception('vendor/autoload.php does not exist');
    }
    
    require_once 'vendor/autoload.php';
    echo "Autoloader loaded successfully\n";
    
    if (!class_exists('Endroid\QrCode\QrCode')) {
        throw new Exception('QrCode class not found after loading autoloader');
    }
    echo "QrCode class found\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
