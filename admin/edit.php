<?php
session_start();
require_once '../config.php'; // koneksi ke database

// cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$message = "";

// cek parameter id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID investasi tidak valid.');
}

$id = (int)$_GET['id'];

// ambil data investasi
try {
    $stmt = $koneksi->prepare("SELECT * FROM investasi WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $investasi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$investasi) {
        die('Data investasi tidak ditemukan.');
    }
} catch (PDOException $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}

// proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul        = $_POST['judul_investasi'] ?? '';
    $deskripsi    = $_POST['deskripsi'] ?? '';
    $jumlah_input = $_POST['jumlah'] ?? '';
    $tanggal      = $_POST['tanggal_investasi'] ?? '';
    $kategori     = (int)($_POST['kategori_id'] ?? 0);

    /* ========================
       FIX PARSING JUMLAH
    ======================== */
    if ($jumlah_input === '' || $jumlah_input === null) {
        $jumlah = 0;
    } else {
        $jumlah_input = trim($jumlah_input);

        // Jika input hanya angka (tanpa titik/koma)
        if (preg_match('/^\d+$/', $jumlah_input)) {
            $jumlah = (float)$jumlah_input;
        } else {
            // hapus titik ribuan
            $jumlah_bersih = str_replace('.', '', $jumlah_input);
            // ubah koma ke titik untuk desimal
            $jumlah_bersih = str_replace(',', '.', $jumlah_bersih);
            $jumlah = (float)$jumlah_bersih;
        }
    }

    // validasi
    if (empty($judul) || empty($jumlah_input) || empty($tanggal) || empty($kategori)) {
        $message = "❌ Semua field (kecuali deskripsi) wajib diisi.";
    } else {
        try {
            $sql = "UPDATE investasi SET 
                        judul_investasi   = :judul,
                        deskripsi         = :deskripsi,
                        jumlah            = :jumlah,
                        tanggal_investasi = :tanggal,
                        kategori_id       = :kategori,
                        updated_at        = NOW()
                    WHERE id = :id";

            $stmt = $koneksi->prepare($sql);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':jumlah', $jumlah);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':kategori', $kategori, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('Location: ../dashboard.php?msg=edit_sukses');
                exit;
            } else {
                $message = "❌ Gagal mengupdate data.";
            }
        } catch (PDOException $e) {
            $message = "❌ Error saat update: " . $e->getMessage();
        }
    }
} else {
    // isi form dari database (RAW tanpa format number_format)
    $judul     = $investasi['judul_investasi'];
    $deskripsi = $investasi['deskripsi'];
    $jumlah    = $investasi['jumlah'];
    $tanggal   = $investasi['tanggal_investasi'];
    $kategori  = $investasi['kategori_id'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Investasi</title>
    <link rel="stylesheet" href="../assets/css/edit.css">
</head>
<body>
    <div class="container">
        <h1>Edit Investasi</h1>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label>Judul Investasi:</label>
            <input type="text" name="judul_investasi" value="<?php echo htmlspecialchars($judul); ?>" required>

            <label>Deskripsi:</label>
            <textarea name="deskripsi"><?php echo htmlspecialchars($deskripsi); ?></textarea>

            <label>Jumlah:</label>
            <input type="text" name="jumlah" value="<?php echo htmlspecialchars($jumlah); ?>" required>
            <small>* Masukkan angka tanpa titik/koma, misal: 148500</small>

            <label>Tanggal Investasi:</label>
            <input type="date" name="tanggal_investasi" value="<?php echo htmlspecialchars($tanggal); ?>" required>

            <label>Kategori ID:</label>
            <input type="number" name="kategori_id" value="<?php echo htmlspecialchars($kategori); ?>" required>

            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>