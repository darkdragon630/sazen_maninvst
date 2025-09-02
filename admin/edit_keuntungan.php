<?php
session_start();
require_once "../config.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$keuntungan_id = $_GET['id'] ?? null;
if (!$keuntungan_id) {
    header("Location: ../dashboard.php");
    exit;
}

// Ambil data keuntungan
$sql = "SELECT k.*, i.judul_investasi, kat.nama_kategori 
        FROM keuntungan_investasi k
        JOIN investasi i ON k.investasi_id = i.id
        JOIN kategori kat ON k.kategori_id = kat.id
        WHERE k.id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->execute([$keuntungan_id]);
$keuntungan = $stmt->fetch();

if (!$keuntungan) {
    header("Location: ../dashboard.php?error=Data tidak ditemukan");
    exit;
}

// Ambil data investasi
$sql_investasi = "SELECT i.id, i.judul_investasi, k.nama_kategori, i.kategori_id 
                  FROM investasi i 
                  JOIN kategori k ON i.kategori_id = k.id 
                  ORDER BY i.judul_investasi";
$investasi_list = $koneksi->query($sql_investasi)->fetchAll();

$error = '';

// Fungsi parsing rupiah
function parseRupiah($value) {
    $value = preg_replace('/[^\d\,\.]/', '', $value);
    $lastComma = strrpos($value, ',');
    $lastDot = strrpos($value, '.');

    if ($lastComma !== false && $lastDot !== false) {
        if ($lastComma > $lastDot) {
            return floatval(str_replace(['.', ','], ['', '.'], $value));
        } else {
            return floatval(str_replace(',', '', $value));
        }
    } elseif ($lastComma !== false) {
        return floatval(str_replace(',', '.', $value));
    } elseif ($lastDot !== false) {
        return floatval(str_replace('.', '', $value));
    } else {
        return floatval($value);
    }
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $investasi_id = $_POST['investasi_id'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $judul_keuntungan = trim($_POST['judul_keuntungan'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $jumlah_keuntungan = parseRupiah($_POST['jumlah_keuntungan'] ?? '0');
    $persentase_input = $_POST['persentase_keuntungan'] ?? '';
    $persentase_keuntungan = $persentase_input !== '' ? floatval($persentase_input) / 100 : null;
    $tanggal_keuntungan = $_POST['tanggal_keuntungan'] ?? '';
    $sumber_keuntungan = $_POST['sumber_keuntungan'] ?? '';
    $status = $_POST['status'] ?? '';

    if (empty($investasi_id) || empty($judul_keuntungan) || $jumlah_keuntungan < 0 || empty($tanggal_keuntungan)) {
        $error = 'Harap isi semua field wajib dengan benar.';
    } else {
        try {
            $sql_update = "UPDATE keuntungan_investasi SET 
                          investasi_id = ?, kategori_id = ?, judul_keuntungan = ?, 
                          deskripsi = ?, jumlah_keuntungan = ?, persentase_keuntungan = ?, 
                          tanggal_keuntungan = ?, sumber_keuntungan = ?, status = ?, 
                          updated_at = CURRENT_TIMESTAMP 
                          WHERE id = ?";
            
            $stmt_update = $koneksi->prepare($sql_update);
            $stmt_update->execute([
                $investasi_id, $kategori_id, $judul_keuntungan, $deskripsi, 
                $jumlah_keuntungan, $persentase_keuntungan, $tanggal_keuntungan, 
                $sumber_keuntungan, $status, $keuntungan_id
            ]);
            
            header("Location: ../dashboard.php?success=1&msg=Keuntungan berhasil diperbarui");
            exit;
        } catch (Exception $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Keuntungan - SAZEN</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <section class="investments-section">
            <div class="section-header">
                <div class="header-content">
                    <h2 class="section-title">
                        <i class="fas fa-edit"></i> Edit Keuntungan Investasi
                    </h2>
                    <p class="section-subtitle">Perbarui informasi keuntungan Anda</p>
                </div>
            </div>

            <!-- Current Data -->
            <div class="current-data">
                <strong>Data Saat Ini:</strong><br>
                <strong>Investasi:</strong> <?= htmlspecialchars($keuntungan['judul_investasi']) ?><br>
                <strong>Kategori:</strong> <?= htmlspecialchars($keuntungan['nama_kategori']) ?><br>
                <strong>Keuntungan:</strong> <?= htmlspecialchars($keuntungan['judul_keuntungan']) ?><br>
                <strong>Jumlah:</strong> Rp <?= number_format($keuntungan['jumlah_keuntungan'], 2, ',', '.') ?>
            </div>

            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-container">
                <!-- Investasi -->
                <div class="form-group">
                    <label for="investasi_id"><i class="fas fa-briefcase"></i> Pilih Investasi</label>
                    <select name="investasi_id" id="investasi_id" class="form-control" required>
                        <option value="">-- Pilih Investasi --</option>
                        <?php foreach ($investasi_list as $inv): ?>
                            <option value="<?= $inv['id'] ?>"
                                    data-kategori="<?= $inv['kategori_id'] ?>"
                                    data-nama-kategori="<?= htmlspecialchars($inv['nama_kategori']) ?>"
                                    <?= $keuntungan['investasi_id'] == $inv['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($inv['judul_investasi']) ?> (<?= htmlspecialchars($inv['nama_kategori']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Kategori Info -->
                <div class="investment-info show" id="investmentInfo">
                    <strong>Kategori:</strong> <span id="selectedCategory"><?= htmlspecialchars($keuntungan['nama_kategori']) ?></span>
                    <input type="hidden" name="kategori_id" id="kategori_id" value="<?= $keuntungan['kategori_id'] ?>">
                </div>

                <!-- Judul Keuntungan -->
                <div class="form-group">
                    <label for="judul_keuntungan"><i class="fas fa-tag"></i> Judul Keuntungan</label>
                    <input type="text" name="judul_keuntungan" id="judul_keuntungan"
                           class="form-control" placeholder="Contoh: Dividen Q1 2024"
                           value="<?= htmlspecialchars($keuntungan['judul_keuntungan']) ?>" required>
                </div>

                <!-- Jumlah & Persentase -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_keuntungan"><i class="fas fa-money-bill-wave"></i> Jumlah Keuntungan (Rp)</label>
                        <input type="text" name="jumlah_keuntungan" id="jumlah_keuntungan"
                               class="form-control" placeholder="0,00"
                               value="<?= number_format($keuntungan['jumlah_keuntungan'], 2, ',', '.') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="persentase_keuntungan"><i class="fas fa-percentage"></i> Persentase (%)</label>
                        <input type="number" name="persentase_keuntungan" id="persentase_keuntungan"
                               class="form-control" step="0.01" min="0"
                               placeholder="Opsional"
                               value="<?= $keuntungan['persentase_keuntungan'] ? number_format($keuntungan['persentase_keuntungan'] * 100, 6) : '' ?>">
                    </div>
                </div>

                <!-- Tanggal -->
                <div class="form-group">
                    <label for="tanggal_keuntungan"><i class="fas fa-calendar-alt"></i> Tanggal Keuntungan</label>
                    <input type="date" name="tanggal_keuntungan" id="tanggal_keuntungan"
                           class="form-control" value="<?= $keuntungan['tanggal_keuntungan'] ?>" required>
                </div>

                <!-- Sumber Keuntungan -->
                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Sumber Keuntungan</label>
                    <div class="profit-types">
                        <?php $sources = ['dividen', 'capital_gain', 'bunga', 'bonus', 'lainnya']; ?>
                        <?php
                        $ikon_map = [
                            'dividen' => 'fa-coins',
                            'capital_gain' => 'fa-chart-line',
                            'bunga' => 'fa-percent',
                            'bonus' => 'fa-gift',
                        ];
                        ?>
                        <?php foreach ($sources as $src): ?>
                            <div class="profit-type" onclick="selectProfitType(this, '<?= $src ?>')">
                                <input type="radio" name="sumber_keuntungan" value="<?= $src ?>"
                                       <?= $keuntungan['sumber_keuntungan'] == $src ? 'checked' : '' ?>>
                                <?php $ikon = $ikon_map[$src] ?? 'fa-ellipsis-h'; ?>
                                <i class="fas <?= $ikon ?>"></i><br>
                                <?= ucfirst($src) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="status"><i class="fas fa-flag"></i> Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="realized" <?= $keuntungan['status'] === 'realized' ? 'selected' : '' ?>>Sudah Direalisasi</option>
                            <option value="unrealized" <?= $keuntungan['status'] === 'unrealized' ? 'selected' : '' ?>>Belum Direalisasi</option>
                        </select>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-file-alt"></i> Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"
                              placeholder="Catatan tambahan..."><?= htmlspecialchars($keuntungan['deskripsi']) ?></textarea>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Keuntungan
                    </button>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </section>
    </div>

    <script>
        // Update kategori saat investasi berubah
        document.getElementById('investasi_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const categorySpan = document.getElementById('selectedCategory');
            const categoryInput = document.getElementById('kategori_id');
            if (selected.value) {
                categorySpan.textContent = selected.dataset.namaKategori;
                categoryInput.value = selected.dataset.kategori;
                document.getElementById('investmentInfo').classList.add('show');
            }
        });

        // Handle sumber keuntungan selection
        function selectProfitType(el, value) {
            document.querySelectorAll('.profit-type').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            const checked = document.querySelector('input[name="sumber_keuntungan"]:checked');
            if (checked) {
                checked.closest('.profit-type').classList.add('selected');
            }
        });
    </script>
</body>
</html>
