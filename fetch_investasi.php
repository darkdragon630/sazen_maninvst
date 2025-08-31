<?php
// Header JSON & CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Nonaktifkan error output agar tidak merusak JSON
ini_set('display_errors', 0);
error_reporting(0);

require_once 'config.php';

try {
    if (!isset($koneksi)) {
        throw new Exception('Database connection not initialized.');
    }

    // Ambil data investasi + kategori
    $sql = "
        SELECT i.id, i.judul_investasi, i.deskripsi, i.jumlah, i.tanggal_investasi, k.nama_kategori
        FROM investasi i
        LEFT JOIN kategori k ON i.kategori_id = k.id
        ORDER BY i.tanggal_investasi DESC
    ";
    $stmt = $koneksi->prepare($sql);
    $stmt->execute();
    $investasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($investasi === false) {
        throw new Exception('Failed to fetch data from database');
    }

    // Hitung statistik
    $total_investasi = 0;
    foreach ($investasi as &$item) {
        $item['id'] = (int) $item['id'];
        $item['jumlah'] = (float) $item['jumlah'];
        $item['judul_investasi'] = $item['judul_investasi'] ?? '';
        $item['deskripsi'] = $item['deskripsi'] ?? '';
        $item['nama_kategori'] = $item['nama_kategori'] ?? '';
        $item['tanggal_investasi'] = $item['tanggal_investasi'] ?? date('Y-m-d');

        $total_investasi += $item['jumlah'];
    }

    $response = [
        "status" => "success",
        "total_investasi" => $total_investasi,
        "jumlah_item" => count($investasi),
        "data" => $investasi
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Server error"
    ]);
}
