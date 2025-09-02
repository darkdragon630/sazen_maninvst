<?php
session_start();
require_once "../config.php";

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
$sql_keuntungan = "SELECT k.*, i.judul_investasi, kat.nama_kategori 
                   FROM keuntungan_investasi k
                   JOIN investasi i ON k.investasi_id = i.id
                   JOIN kategori kat ON k.kategori_id = kat.id
                   WHERE k.id = ?";
$stmt_keuntungan = $koneksi->prepare($sql_keuntungan);
$stmt_keuntungan->execute([$keuntungan_id]);
$keuntungan = $stmt_keuntungan->fetch();

if (!$keuntungan) {
    header("Location: ../dashboard.php?error=Data tidak ditemukan");
    exit;
}

// Ambil data investasi
$sql_investasi = "SELECT i.id, i.judul_investasi, k.nama_kategori, i.kategori_id 
                  FROM investasi i 
                  JOIN kategori k ON i.kategori_id = k.id 
                  ORDER BY i.judul_investasi";
$stmt_investasi = $koneksi->query($sql_investasi);
$investasi_list = $stmt_investasi->fetchAll();

$error = '';
$success = '';

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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $investasi_id = $_POST['investasi_id'];
    $kategori_id = $_POST['kategori_id'];
    $judul_keuntungan = trim($_POST['judul_keuntungan']);
    $deskripsi = trim($_POST['deskripsi']);
    $jumlah_keuntungan = parseRupiah($_POST['jumlah_keuntungan']);
    $persentase_input = $_POST['persentase_keuntungan'] ?? '';
    $persentase_keuntungan = $persentase_input !== '' ? floatval($persentase_input) / 100 : null;
    $tanggal_keuntungan = $_POST['tanggal_keuntungan'];
    $sumber_keuntungan = $_POST['sumber_keuntungan'];
    $status = $_POST['status'];

    if (empty($investasi_id) || empty($kategori_id) || empty($judul_keuntungan) || $jumlah_keuntungan < 0 || empty($tanggal_keuntungan)) {
        $error = 'Semua field wajib diisi dengan benar.';
    } else {
        try {
            $sql_update = "UPDATE keuntungan_investasi SET 
                          investasi_id = ?, kategori_id = ?, judul_keuntungan = ?, 
                          deskripsi = ?, jumlah_keuntungan = ?, persentase_keuntungan = ?, 
                          tanggal_keuntungan = ?, sumber_keuntungan = ?, status = ?, 
                          updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            
            $stmt_update = $koneksi->prepare($sql_update);
            $stmt_update->execute([
                $investasi_id, $kategori_id, $judul_keuntungan, $deskripsi, 
                $jumlah_keuntungan, $persentase_keuntungan, $tanggal_keuntungan, 
                $sumber_keuntungan, $status, $keuntungan_id
            ]);
            
            header("Location: ../dashboard.php?success=1&msg=Keuntungan berhasil diperbarui");
            exit;
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Keuntungan Investasi - SAZEN</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS sama seperti sebelumnya */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 2rem; margin-bottom: 10px; }
        .current-data { background: #fff3cd; padding: 15px; margin: 20px 0; border-radius: 10px; border-left: 4px solid #ffc107; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: 500; }
        .alert.error { background: #fee; color: #c33; border-left: 4px solid #c33; }
        .alert.success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50; }
        .form-control { width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px; font-size: 16px; transition: all 0.3s ease; background: #f8f9fa; }
        .form-control:focus { outline: none; border-color: #f39c12; background: white; box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn { padding: 12px 30px; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
        .btn-warning:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(243, 156, 18, 0.3); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .investment-info { background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: none; }
        .investment-info.show { display: block; }
        .profit-types { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
        .profit-type { padding: 10px; border: 2px solid #e1e8ed; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s ease; background: white; }
        .profit-type input { display: none; }
        .profit-type.selected { border-color: #f39c12; background: #fff8e1; color: #f39c12; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } .profit-types { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Edit Keuntungan Investasi</h1>
            <p>Perbarui data keuntungan investasi</p>
        </div>

        <div class="form-container">
            <div class="current-data">
                <strong>Data Saat Ini:</strong><br>
                <strong>Investasi:</strong> <?= htmlspecialchars($keuntungan['judul_investasi']) ?><br>
                <strong>Kategori:</strong> <?= htmlspecialchars($keuntungan['nama_kategori']) ?><br>
                <strong>Keuntungan:</strong> <?= htmlspecialchars($keuntungan['judul_keuntungan']) ?><br>
                <strong>Jumlah:</strong> Rp <?= number_format($keuntungan['jumlah_keuntungan'], 2, ',', '.') ?>
            </div>

            <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="editProfitForm">
                <div class="form-group">
                    <label for="investasi_id"><i class="fas fa-briefcase"></i> Pilih Investasi</label>
                    <select name="investasi_id" id="investasi_id" class="form-control" required>
                        <option value="">-- Pilih Investasi --</option>
                        <?php foreach ($investasi_list as $inv): ?>
                            <option value="<?= $inv['id'] ?>" 
                                    data-kategori="<?= $inv['kategori_id'] ?>"
                                    <?= $keuntungan['investasi_id'] == $inv['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($inv['judul_investasi']) ?> (<?= htmlspecialchars($inv['nama_kategori']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="investment-info show" id="investmentInfo">
                    <strong>Kategori:</strong> <span id="selectedCategory"><?= htmlspecialchars($keuntungan['nama_kategori']) ?></span>
                    <input type="hidden" name="kategori_id" id="kategori_id" value="<?= $keuntungan['kategori_id'] ?>">
                </div>

                <div class="form-group">
                    <label for="judul_keuntungan"><i class="fas fa-tag"></i> Judul Keuntungan</label>
                    <input type="text" name="judul_keuntungan" id="judul_keuntungan" 
                           class="form-control" value="<?= htmlspecialchars($keuntungan['judul_keuntungan']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_keuntungan"><i class="fas fa-money-bill-wave"></i> Jumlah Keuntungan (Rp)</label>
                        <input type="text" name="jumlah_keuntungan" id="jumlah_keuntungan" 
                               class="form-control" placeholder="Contoh: 0.87, 1.500,75, 2.500.000,75"
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

                <div class="form-group">
                    <label for="tanggal_keuntungan"><i class="fas fa-calendar-alt"></i> Tanggal Keuntungan</label>
                    <input type="date" name="tanggal_keuntungan" id="tanggal_keuntungan" 
                           class="form-control" value="<?= $keuntungan['tanggal_keuntungan'] ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Sumber Keuntungan</label>
                    <div class="profit-types">
                        <?php $sources = ['dividen', 'capital_gain', 'bunga', 'bonus', 'lainnya']; ?>
                        <?php foreach ($sources as $src): ?>
                            <div class="profit-type" onclick="selectProfitType(this, '<?= $src ?>')">
                                <input type="radio" name="sumber_keuntungan" value="<?= $src ?>" <?= $keuntungan['sumber_keuntungan'] == $src ? 'checked' : '' ?>>
                                <i class="fas <?= $src == 'dividen' ? 'fa-coins' : ($src == 'capital_gain' ? 'fa-chart-line' : ($src == 'bunga' ? 'fa-percent' : ($src == 'bonus' ? 'fa-gift' : 'fa-ellipsis-h'))) ?>"></i><br>
                                <?= ucfirst($src) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status"><i class="fas fa-flag"></i> Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="realized" <?= $keuntungan['status'] == 'realized' ? 'selected' : '' ?>>Sudah Direalisasi</option>
                            <option value="unrealized" <?= $keuntungan['status'] == 'unrealized' ? 'selected' : '' ?>>Belum Direalisasi</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-file-alt"></i> Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($keuntungan['deskripsi']) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Keuntungan
                    </button>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        function selectProfitType(el, value) {
            document.querySelectorAll('.profit-type').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const checked = document.querySelector('.profit-type input:checked');
            if (checked) checked.closest('.profit-type').classList.add('selected');
        });
    </script>
</body>
</html>
