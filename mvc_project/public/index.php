<?php
/**
 * Gestion d'Absences - MVC Entry Point
 * 
 * This is the main entry point for the MVC restructured application.
 * All requests should be routed through this file.
 */

// Prevent direct access check
if (!defined('BASE_PATH')) {
    // Load bootstrap first (defines all constants and loads config)
    require_once __DIR__ . '/../bootstrap.php';
}

// Get the request URI and remove query string
$request_uri = $_SERVER['REQUEST_URI'];
$path_info = strtok($request_uri, '?');

// Determine route segment by removing the script name (index.php) and any leading path
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$script_base = basename($script_name);
// If the script basename is present in the URI, strip everything up to and including it
$pos = ($script_base && ($p = strpos($path_info, $script_base)) !== false) ? $p + strlen($script_base) : false;
if ($pos !== false) {
    $path_info = substr($path_info, $pos);
}

// Normalize and ensure path starts with '/'
$path_info = '/' . trim($path_info, '/');
if ($path_info === '/') $path_info = '/';

// Route to appropriate views based on original structure
try {
    switch(true) {
        // Admin routes -> AdminController
        case strpos($path_info, '/admindash/') === 0:
            require_once CONTROLLERS_PATH . '/AdminController.php';
            $controller = new AdminController();
            $action = trim(str_replace('/admindash/', '', $path_info), '/');
            $action = $action === '' ? 'dashboard' : $action;
            $called = false;
            $candidates = [
                $action,
                str_replace(['-', '_'], '', $action),
                preg_replace_callback('/[_-](.)/', function($m){return strtoupper($m[1]);}, $action),
                lcfirst(str_replace(' ', '', ucwords(str_replace(['-','_'], ' ', $action))))
            ];
            foreach ($candidates as $m) {
                if (method_exists($controller, $m)) { $controller->{$m}(); $called = true; break; }
            }
            if (!$called) { $controller->dashboard(); }
            break;
            
        // Professor routes -> ProfessorController
        case strpos($path_info, '/profdash/') === 0:    
            require_once CONTROLLERS_PATH . '/ProfessorController.php';
            $controller = new ProfessorController();
            $action = trim(str_replace('/profdash/', '', $path_info), '/');
            $action = $action === '' ? 'dashboard' : $action;
            $called = false;
            $candidates = [
                $action,
                str_replace(['-', '_'], '', $action),
                preg_replace_callback('/[_-](.)/', function($m){return strtoupper($m[1]);}, $action),
                lcfirst(str_replace(' ', '', ucwords(str_replace(['-','_'], ' ', $action))))
            ];
            foreach ($candidates as $m) {
                if (method_exists($controller, $m)) { $controller->{$m}(); $called = true; break; }
            }
            if (!$called) { $controller->dashboard(); }
            break;
            
        // Student routes -> StudentController
        case strpos($path_info, '/studdash/') === 0:
            require_once CONTROLLERS_PATH . '/StudentController.php';
            $controller = new StudentController();
            $action = trim(str_replace('/studdash/', '', $path_info), '/');
            $action = $action === '' ? 'dashboard' : $action;
            $called = false;
            $candidates = [
                $action,
                str_replace(['-', '_'], '', $action),
                preg_replace_callback('/[_-](.)/', function($m){return strtoupper($m[1]);}, $action),
                lcfirst(str_replace(' ', '', ucwords(str_replace(['-','_'], ' ', $action))))
            ];
            foreach ($candidates as $m) {
                if (method_exists($controller, $m)) { $controller->{$m}(); $called = true; break; }
            }
            if (!$called) { $controller->dashboard(); }
            break;
            
        // Auth routes - dispatch to AuthController for login/logout/etc.
        case strpos($path_info, '/login/') === 0:
            require_once CONTROLLERS_PATH . '/AuthController.php';
            $controller = new AuthController();
            $view = trim(str_replace('/login/', '', $path_info), '/');
            $action = $view ?: 'login';
            if (method_exists($controller, $action)) {
                $controller->{$action}();
            } else {
                $controller->login();
            }
            break;
            
        // Root and index -> AuthController index
        case $path_info === '' || $path_info === '/':
            require_once CONTROLLERS_PATH . '/AuthController.php';
            $controller = new AuthController();
            $controller->index();
            break;
            
        // Handle direct file access patterns (for backward compatibility)
        case preg_match('#^/(.*)\.php$#', $path_info, $matches):
            $file = $matches[1];
            
            // Try to find the file in appropriate view directory
            if (strpos($file, 'admin/') === 0) {
                $view_file = VIEWS_PATH . '/' . $file . '.php';
            } elseif (strpos($file, 'prof/') === 0 || strpos($file, 'profdash/') === 0) {
                $view_file = VIEWS_PATH . '/professor/' . basename($file) . '.php';
            } elseif (strpos($file, 'stud/') === 0 || strpos($file, 'studdash/') === 0) {
                $view_file = VIEWS_PATH . '/student/' . basename($file) . '.php';
            } elseif (strpos($file, 'login/') === 0) {
                $view_file = VIEWS_PATH . '/auth/' . basename($file) . '.php';
            } else {
                // Try auth directory as fallback
                $view_file = VIEWS_PATH . '/auth/' . basename($file) . '.php';
            }
            
            if (file_exists($view_file)) {
                require_once $view_file;
            } else {
                throw new Exception('View not found: ' . $file);
            }
            break;
            
        // Default to login for unknown routes
        default:
            require_once VIEWS_PATH . '/auth/login.php';
    }
    
} catch (Exception $e) {
    // Log error (in production, you'd want a proper error page)
    error_log('MVC Router Error: ' . $e->getMessage());
    
    // Show error or redirect to login
    if (file_exists(VIEWS_PATH . '/auth/login.php')) {
        require_once VIEWS_PATH . '/auth/login.php';
    } else {
        die('Application error. Please check the logs.');
    }
}
?>