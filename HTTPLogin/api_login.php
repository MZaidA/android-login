<?php
// Sertakan file koneksi dan helper
require_once 'db_config.php';
require_once 'api_helpers.php';

// Atur header API di awal
set_api_headers();

// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, [
        'status' => 'error',
        'message' => 'Metode tidak diizinkan. Gunakan metode POST.'
    ]);
}

// Ambil data input JSON
$input_data = get_request_input();

$username = isset($input_data['username']) ? trim($input_data['username']) : '';
$password = isset($input_data['password']) ? trim($input_data['password']) : '';

// Validasi input
if (empty($username) || empty($password)) {
    send_json_response(400, [
        'status' => 'error',
        'message' => 'Username dan password wajib diisi.'
    ]);
}

try {
    // Gunakan prepared statement PDO
    $stmt = $pdo->prepare("SELECT `id`, `username`, `password` FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Password cocok, login berhasil
        send_json_response(200, [
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ]);
    } else {
        // Pengguna tidak ditemukan atau password salah
        send_json_response(401, [
            'status' => 'error',
            'message' => 'Username atau password salah.'
        ]);
    }
} catch (PDOException $e) {
    send_json_response(500, [
        'status' => 'error',
        'message' => 'Terjadi kesalahan pada server.',
        'detail' => $e->getMessage() // Tampilkan saat development saja
    ]);
}
