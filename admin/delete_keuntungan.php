<?php
session_start();
require_once "../config.php";

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$keuntungan_id = $_GET['id'] ?? null;

if (!$keuntungan_id) {
    header("Location: ../dashboard.php?error=ID tidak valid");
    exit;
}

// Cek apakah keuntungan ada
$sql_check = "SELECT judul_keuntungan FROM keuntungan_investasi WHERE id = ?";
$stmt_check = $koneksi->prepare($sql_check);
$stmt_check->execute([$keuntungan_id]);
$keuntungan = $stmt_check->fetch();

if (!$keuntungan) {
    header("Location: ../dashboard.php?error=Data tidak ditemukan");
    exit;
}

try {
    // Hapus keuntungan
    $sql_delete = "DELETE FROM keuntungan_investasi WHERE id = ?";
    $stmt_delete = $koneksi->prepare($sql_delete);
    $stmt_delete->execute([$keuntungan_id]);
    
    header("Location: ../dashboard.php?success=1&msg=Keuntungan berhasil dihapus");
    exit;
} catch (Exception $e) {
    header("Location: ../dashboard.php?error=Gagal menghapus keuntungan: " . $e->getMessage());
    exit;
}
?>
