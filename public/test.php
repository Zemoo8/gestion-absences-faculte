<?php
/**
 * Test File - Verify MVC Structure Works
 */

// Load bootstrap
require_once __DIR__ . '/../bootstrap.php';

echo "<h1>✅ MVC Structure Test</h1>";
echo "<h2>Constants Defined:</h2>";
echo "<ul>";
echo "<li>BASE_PATH: " . (defined('BASE_PATH') ? '✅ ' . BASE_PATH : '❌') . "</li>";
echo "<li>APP_PATH: " . (defined('APP_PATH') ? '✅ ' . APP_PATH : '❌') . "</li>";
echo "<li>CONFIG_PATH: " . (defined('CONFIG_PATH') ? '✅ ' . CONFIG_PATH : '❌') . "</li>";
echo "<li>VIEWS_PATH: " . (defined('VIEWS_PATH') ? '✅ ' . VIEWS_PATH : '❌') . "</li>";
echo "</ul>";

echo "<h2>Configuration File:</h2>";
if (file_exists(CONFIG_PATH . '/config.php')) {
    echo "✅ config.php exists<br>";
    echo "✅ Database connection: ";
    if (isset($mysqli) && $mysqli->ping()) {
        echo "Connected successfully";
    } else {
        echo "Connection failed";
    }
} else {
    echo "❌ config.php not found";
}

echo "<h2>Sample URLs:</h2>";
echo "<ul>";
echo "<li><a href='index.php/admindash/dashboard'>Admin Dashboard</a></li>";
echo "<li><a href='index.php/profdash/prof_dashboard'>Professor Dashboard</a></li>";
echo "<li><a href='index.php/studdash/dashstud'>Student Dashboard</a></li>";
echo "<li><a href='index.php/login/login'>Login</a></li>";
echo "</ul>";
?>