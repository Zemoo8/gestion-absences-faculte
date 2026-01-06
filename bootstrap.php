<?php
/**
 * Bootstrap File
 * Defines all constants and loads configuration
 */

// Development: show PHP errors in browser to help debug HTTP 500s
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Define directory separators for cross-platform compatibility
define('DS', DIRECTORY_SEPARATOR);

// Define base paths
define('BASE_PATH', realpath(dirname(__FILE__)));
define('APP_PATH', BASE_PATH . DS . 'app');
define('CONFIG_PATH', BASE_PATH . DS . 'config');
define('PUBLIC_PATH', BASE_PATH . DS . 'public');
// Views are stored in `app/hello` in this project — keep existing files unchanged
define('VIEWS_PATH', APP_PATH . DS . 'hello');
define('CONTROLLERS_PATH', APP_PATH . DS . 'controllers');
define('MODELS_PATH', APP_PATH . DS . 'models');

// Define URL paths (adjust based on your setup)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Use SCRIPT_NAME to determine the URL of the current entry script (usually public/index.php).
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $protocol . '://' . $host . $scriptDir);

// PUBLIC_URL should point to the web-accessible directory (the directory containing index.php).
define('PUBLIC_URL', BASE_URL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
if (file_exists(CONFIG_PATH . DS . 'config.php')) {
    require_once CONFIG_PATH . DS . 'config.php';
} else {
    die('Configuration file not found!');
}

// Auto-load controllers and models
spl_autoload_register(function($className) {
    $controllerFile = CONTROLLERS_PATH . DS . $className . '.php';
    $modelFile = MODELS_PATH . DS . $className . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
    } elseif (file_exists($modelFile)) {
        require_once $modelFile;
    }
});
?>