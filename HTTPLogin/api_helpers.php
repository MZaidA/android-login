<?php
/**
 * File helper untuk fungsi-fungsi umum API.
 */

/**
 * Mengatur header standar untuk respons API JSON.
 */
function set_api_headers() {
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *"); // Untuk development. Di produksi, ganti dengan domain Anda.
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

/**
 * Mengirim respons JSON dan menghentikan eksekusi skrip.
 * @param int $status_code Kode status HTTP
 * @param array $data Data yang akan di-encode ke JSON
 */
function send_json_response($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

/**
 * Mendapatkan input dari body request, baik itu JSON atau form-data.
 * @return array Data input
 */
function get_request_input() {
    $input_data = json_decode(file_get_contents("php://input"), true);
    
    // Jika input bukan JSON (misalnya dari form-data), coba ambil dari $_POST
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $_POST;
    }
    return $input_data ?? [];
}
?>