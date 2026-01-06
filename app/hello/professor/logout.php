<?php
// Session is handled by bootstrap; destroy and redirect.
if (!defined('BASE_PATH')) {
	require_once __DIR__ . '/../../../bootstrap.php';
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