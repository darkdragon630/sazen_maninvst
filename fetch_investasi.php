<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $sql = "
        SELECT i.id, i.judul_investasi, i.deskripsi, i.jumlah, i.tanggal_investasi, k.nama_kategori
        FROM investasi i
        JOIN kategori k ON i.kategori_id = k.id
        ORDER BY i.tanggal_investasi DESC
    ";

    $stmt = $koneksi->query($sql);
    $investasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert jumlah to number for easier processing in JavaScript
    foreach ($investasi as &$item) {
        $item['jumlah'] = (float) $item['jumlah'];
    }

    echo json_encode($investasi, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>