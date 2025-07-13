<?php
require_once 'db_config.php';
require_once 'api_helpers.php';

set_api_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Metode tidak diizinkan. Gunakan metode POST.']);
}

$input_data = get_request_input();

$username = isset($input_data['username']) ? trim($input_data['username']) : '';
$password = isset($input_data['password']) ? trim($input_data['password']) : '';

if (empty($username) || empty($password)) {
    send_json_response(400, ['status' => 'error', 'message' => 'Username dan password wajib diisi.']);
}

if (strlen($password) < 8) {
    send_json_response(400, ['status' => 'error', 'message' => 'Password minimal harus 8 karakter.']);
}

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

try {
    // Masukkan data ke database
    $stmt = $pdo->prepare("INSERT INTO user (`username`, `password`) VALUES (?, ?)");
    $stmt->execute([$username, $password_hash]);

    send_json_response(201, ['status' => 'success', 'message' => 'Pengguna berhasil didaftarkan.']);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // Duplicate entry (UNIQUE constraint)
        send_json_response(409, ['status' => 'error', 'message' => 'Username sudah terdaftar.']);
    } else {
        send_json_response(500, [
            'status' => 'error',
            'message' => 'Registrasi gagal.',
            'detail' => $e->getMessage() // Tampilkan detail hanya di development
        ]);
    }
}
?>
