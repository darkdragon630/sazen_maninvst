<?php
require_once "../config.php"; // koneksi ke database

$message = "";

// Proses simpan data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $judul     = $_POST['judul_investasi'];
    $deskripsi = $_POST['deskripsi'];

    // Bersihkan jumlah agar bisa input pakai titik/koma
    $jumlah = str_replace(['.', ','], '', $_POST['jumlah']); 
    $tanggal  = $_POST['tanggal_investasi'];
    $kategori = $_POST['kategori_id'];

    // upload file
    $targetDir =__DIR__"../bukti_investasi/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $fileName       = basename($_FILES["bukti"]["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;
    $fileType       = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowedTypes = ["jpg", "jpeg", "png", "pdf"];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["bukti"]["tmp_name"], $targetFilePath)) {
            try {
                // Simpan ke DB
                $sql = "INSERT INTO investasi
                        (judul_investasi, deskripsi, jumlah, tanggal_investasi, kategori_id, created_at, updated_at) 
                        VALUES (:judul, :deskripsi, :jumlah, :tanggal, :kategori, NOW(), NOW())";
                $stmt = $koneksi->prepare($sql);
                $stmt->execute([
                    ':judul'    => $judul,
                    ':deskripsi'=> $deskripsi,
                    ':jumlah'   => $jumlah,
                    ':tanggal'  => $tanggal,
                    ':kategori' => $kategori
                ]);
                // ✅ redirect ke dashboard dengan query string success
                header("Location: ../dashboard.php?success=1");
                exit;
            } catch (PDOException $e) {
                $message = "❌ Gagal menyimpan ke database: " . $e->getMessage();
            }
        } else {
            $message = "❌ Gagal upload file.";
        }
    } else {
        $message = "❌ Format file tidak valid. (Hanya jpg, jpeg, png, pdf)";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Investasi</title>
    <link rel="stylesheet" href="../assets/css/upload.css">
</head>
<body>
    <div class="form-container">
        <h2>Tambah Data Investasi</h2>

        <?php if (!empty($message)): ?>
            <p class="<?php echo strpos($message, 'berhasil') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <label>Judul Investasi:</label>
            <input type="text" name="judul_investasi" required>

            <label>Deskripsi:</label>
            <textarea name="deskripsi" required></textarea>

            <label>Jumlah (Rp):</label>
            <input type="text" name="jumlah" placeholder="contoh: 143.500" required>

            <label>Tanggal Investasi:</label>
            <input type="date" name="tanggal_investasi" required>

            <label>Kategori:</label>
            <select name="kategori_id" required>
                <option value="">-- Pilih Kategori --</option>
                <?php
                try {
                    $stmt = $koneksi->query("SELECT id, nama_kategori FROM kategori");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id']}'>{$row['nama_kategori']}</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option disabled>Gagal load kategori</option>";
                }
                ?>
            </select>

            <label>Upload Bukti Transaksi:</label>
            <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" required>

            <button type="submit">Simpan</button>
        </form>
    </div>
</body>
</html>
