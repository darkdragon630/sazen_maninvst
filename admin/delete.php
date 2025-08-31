<?php
require_once "../config.php";

// Cek apakah parameter id ada dan valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID investasi tidak valid.');
}

$id = (int)$_GET['id'];

try {
    // hapus data investasi berdasarkan ID
    $stmt = $koneksi->prepare("DELETE FROM investasi WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    // redirect ke dashboard dengan pesan sukses
    header("Location: ../dashboard.php?deleted=1");
    exit;
} catch (PDOException $e) {
    die("gagal menghapus data: " . $e->getMessage());
}
?>

