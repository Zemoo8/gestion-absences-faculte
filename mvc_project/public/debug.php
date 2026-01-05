<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: text/plain');

echo "DEBUG PAGE\n";
echo "BASE_PATH: " . BASE_PATH . "\n";
echo "APP_PATH: " . APP_PATH . "\n";
echo "VIEWS_PATH: " . VIEWS_PATH . "\n";
echo "CONTROLLERS_PATH: " . CONTROLLERS_PATH . "\n";
echo "PUBLIC_PATH: " . PUBLIC_PATH . "\n";

echo "BASE_URL: " . BASE_URL . "\n";
echo "PUBLIC_URL: " . PUBLIC_URL . "\n";

echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? '') . "\n";

echo "\nHEADERS:\n";
if (function_exists('getallheaders')) {
    foreach (getallheaders() as $k => $v) echo "$k: $v\n";
} else {
    foreach ($_SERVER as $k => $v) if (strpos($k, 'HTTP_') === 0) echo "$k: $v\n";
}

echo "\nSESSION:\n";
print_r($_SESSION);

?>