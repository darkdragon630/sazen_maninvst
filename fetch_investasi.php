<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Turn off error output to prevent JSON corruption
ini_set('display_errors', 0);
error_reporting(0);

require_once 'config.php';

try {
    // Check if database connection exists
    if (!isset($koneksi)) {
        throw new Exception('Database connection variable not found');
    }

    $sql = "
        SELECT i.id, i.judul_investasi, i.deskripsi, i.jumlah, i.tanggal_investasi, k.nama_kategori
        FROM investasi i
        JOIN kategori k ON i.kategori_id = k.id
        ORDER BY i.tanggal_investasi DESC
    ";

    $stmt = $koneksi->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement');
    }

    $stmt->execute();
    $investasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure we have data
    if ($investasi === false) {
        throw new Exception('Failed to fetch data from database');
    }

    // Convert and clean data for JavaScript
    foreach ($investasi as &$item) {
        $item['id'] = (int) $item['id'];
        $item['jumlah'] = (float) $item['jumlah'];
        $item['judul_investasi'] = $item['judul_investasi'] ?? '';
        $item['deskripsi'] = $item['deskripsi'] ?? '';
        $item['nama_kategori'] = $item['nama_kategori'] ?? '';
        $item['tanggal_investasi'] = $item['tanggal_investasi'] ?? date('Y-m-d');
    }

    // Return clean JSON
    echo json_encode($investasi);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error occurred']);
}
?>
