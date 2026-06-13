<?php
// Deklarasikan variabel bypass HANYA untuk membaca plain text/link
define('ALLOW_PUBLIC_VIEW', true);
require_once __DIR__ . '/core/config.php';

$requested_file = isset($_GET['f']) ? basename($_GET['f']) : null;

if (!$requested_file) {
    http_response_code(404);
    die("Asset not specified.");
}

function searchFileRecursively($dir, $filename_without_ext) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && pathinfo($file->getFilename(), PATHINFO_FILENAME) === $filename_without_ext) {
            return $file->getPathname();
        }
    }
    return false;
}

$found = searchFileRecursively(ASSETS_DIR, $requested_file);

if ($found && file_exists($found)) {
    // PROTEKSI EKSTRA: Pastikan ekstensi yang diakses murni teks (.txt) atau tanpa ekstensi.
    // Jika mencoba mengeksekusi atau melihat ekstensi lain dari IP luar whitelist, blokir!
    $ext = pathinfo($found, PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), ['txt', ''])) {
        http_response_code(403);
        die("Firewall Blocked: Access Denied. Only text-based assets are allowed for public global view.");
    }

    // Apapun ekstensinya (selama lolos filter di atas), kita paksa menjadi teks sesuai permintaan agar tidak mendownload otomatis.
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Length: ' . filesize($found));
    readfile($found);
    exit;
} else {
    http_response_code(404);
    die("File not found or access denied.");
}
?>