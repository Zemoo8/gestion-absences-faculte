<?php
class BaseController {
    protected function render($viewPath, $data = []) {
        $fullPath = rtrim(VIEWS_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $viewPath) . '.php';
        if (!file_exists($fullPath)) {
            // try slash-style
            $fullPath = rtrim(VIEWS_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $viewPath . '.php';
        }

        extract($data);
        require_once $fullPath;
    }

    protected function redirect($path) {
        // Prefer sending the path directly so redirects respect the current document root.
        // If an absolute URL is provided, use it unchanged.
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            header('Location: ' . $path);
        } else {
            header('Location: ' . $path);
        }
        exit;
    }
}

?>