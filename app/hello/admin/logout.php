<?php

// Ensure bootstrap is loaded when this view is accessed directly or via controller.
if (!defined('BASE_PATH')) {
	require_once __DIR__ . '/../../../bootstrap.php';
}

// Make DB connection available
global $mysqli;

// Session is handled by bootstrap; destroy session and redirect.
session_destroy();
header("Location: " . PUBLIC_URL . "/index.php/login/login");
exit();
?>