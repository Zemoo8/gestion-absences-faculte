<?php
// Session is handled by bootstrap; destroy and redirect.
if (!defined('BASE_PATH')) {
	require_once __DIR__ . '/../../../bootstrap.php';
}

// If logout is accessed directly, redirect through front-controller for consistency
if (basename($_SERVER['SCRIPT_NAME']) !== 'index.php') {
	$base = defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
	header('Location: ' . $base . '/index.php/login/logout');
	exit();
}
// clear session and cookies then redirect
$_SESSION = [];
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}
session_destroy();
header("Location: " . PUBLIC_URL . "/index.php/login/login");
exit();
?>