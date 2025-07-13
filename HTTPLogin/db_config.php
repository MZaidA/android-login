<?php
require_once 'load_env.php';
load_env(__DIR__ . '/.env');

// Ambil dari environment
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$db   = getenv('DB_NAME');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal terhubung ke database.',
        'detail' => $e->getMessage()
    ]);
    exit;
}
