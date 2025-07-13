<?php
// Untuk PHP built-in web server (php -S)
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $fullPath = __DIR__ . $path;
    if (is_file($fullPath)) {
        return false;
    }
}

// Ambil path dari URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($uri, '/');

// Routing
switch ($path) {
    case 'api/login':
        require __DIR__ . '/api_login.php';
        break;

    case 'api/register':
        require __DIR__ . '/api_register.php';
        break;

    default:
        require_once __DIR__ . '/api_helpers.php';
        set_api_headers();
        send_json_response(404, ['status' => 'error', 'message' => 'Endpoint tidak ditemukan.']);
        break;
}
