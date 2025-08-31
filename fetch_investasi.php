<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    // Check database connection
    if (!isset($koneksi) || !$koneksi) {
        throw new Exception('Database connection failed');
    }

    $sql = "
        SELECT i.id, i.judul_investasi, i.deskripsi, i.jumlah, i.tanggal_investasi, k.nama_kategori
        FROM investasi i
        JOIN kategori k ON i.kategori_id = k.id
        ORDER BY i.tanggal_investasi DESC
    ";

    $stmt = $koneksi->prepare($sql);
    $stmt->execute();
    $investasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert jumlah to number for easier processing in JavaScript
    foreach ($investasi as &$item) {
        $item['jumlah'] = (float) $item['jumlah'];
        $item['id'] = (int) $item['id'];
        
        // Ensure all fields are properly set
        $item['judul_investasi'] = $item['judul_investasi'] ?? '';
        $item['deskripsi'] = $item['deskripsi'] ?? '';
        $item['nama_kategori'] = $item['nama_kategori'] ?? '';
        $item['tanggal_investasi'] = $item['tanggal_investasi'] ?? date('Y-m-d');
    }

    // Log for debugging
    error_log('Fetched ' . count($investasi) . ' investment records');

    echo json_encode($investasi, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
