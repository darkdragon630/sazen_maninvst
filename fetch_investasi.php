<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT id, judul_investasi, jumlah, tanggal_investasi 
            FROM investasi 
            ORDER BY tanggal_investasi DESC";
    $stmt = $koneksi->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data"   => $data
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
