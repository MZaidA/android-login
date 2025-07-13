<?php
function load_env($filepath = '.env') {
    if (!file_exists($filepath)) {
        return;
    }

    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Lewati komentar
        if (!strpos($line, '=')) continue;            // Lewati baris tidak valid

        list($name, $value) = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);

        // Hapus tanda kutip jika ada
        $value = trim($value, '"\'');

        putenv("$name=$value");
    }
}
