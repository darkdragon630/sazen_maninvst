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

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $investasi_id = $_POST['investasi_id'];
    $kategori_id = $_POST['kategori_id'];
    $judul_keuntungan = trim($_POST['judul_keuntungan']);
    $deskripsi = trim($_POST['deskripsi']);

    // Parsing jumlah keuntungan
    if (isset($_POST['jumlah_keuntungan_parsed']) && is_numeric($_POST['jumlah_keuntungan_parsed'])) {
        $jumlah_keuntungan = floatval($_POST['jumlah_keuntungan_parsed']);
    } else {
        $jumlah_keuntungan_raw = trim($_POST['jumlah_keuntungan'] ?? '0');
        $last_comma = strrpos($jumlah_keuntungan_raw, ',');
        $last_dot = strrpos($jumlah_keuntungan_raw, '.');

        if ($last_comma !== false && $last_dot !== false) {
            if ($last_comma > $last_dot) {
                $jumlah_keuntungan = floatval(str_replace(['.', ','], ['', '.'], $jumlah_keuntungan_raw));
            } else {
                $jumlah_keuntungan = floatval(str_replace(',', '', $jumlah_keuntungan_raw));
            }
        } elseif ($last_comma !== false) {
            $parts = explode(',', $jumlah_keuntungan_raw);
            if (count($parts) == 2 && strlen($parts[1]) <= 2) {
                $jumlah_keuntungan = floatval(str_replace(',', '.', $jumlah_keuntungan_raw));
            } else {
                $jumlah_keuntungan = floatval(str_replace(',', '', $jumlah_keuntungan_raw));
            }
        } elseif ($last_dot !== false) {
            $parts = explode('.', $jumlah_keuntungan_raw);
            if (count($parts) == 2 && strlen($parts[1]) <= 2) {
                $jumlah_keuntungan = floatval($jumlah_keuntungan_raw);
            } else {
                $jumlah_keuntungan = floatval(str_replace('.', '', $jumlah_keuntungan_raw));
            }
        } else {
            $jumlah_keuntungan = floatval($jumlah_keuntungan_raw);
        }
    }

    $persentase_keuntungan = !empty($_POST['persentase_keuntungan']) ? floatval($_POST['persentase_keuntungan']) : null;
    $tanggal_keuntungan = $_POST['tanggal_keuntungan'];
    $sumber_keuntungan = $_POST['sumber_keuntungan'];
    $status = $_POST['status'];

    // Validasi
    if (empty($investasi_id) || empty($kategori_id) || empty($judul_keuntungan) || $jumlah_keuntungan < 0 || empty($tanggal_keuntungan)) {
        $error = 'Semua field wajib diisi dengan benar.';
    } else {
        try {
            $sql_update = "UPDATE keuntungan_investasi SET 
                          investasi_id = ?, 
                          kategori_id = ?, 
                          judul_keuntungan = ?, 
                          deskripsi = ?, 
                          jumlah_keuntungan = ?, 
                          persentase_keuntungan = ?, 
                          tanggal_keuntungan = ?, 
                          sumber_keuntungan = ?, 
                          status = ?,
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-2xl);
            overflow: hidden;
        }

        .header {
            background: var(--gradient-warning);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            opacity: 0.9;
        }

        .current-data {
            background: var(--warning-50);
            padding: 15px;
            margin: 20px 0;
            border-radius: var(--radius-xl);
            border-left: 4px solid var(--warning-500);
        }

        .current-data strong {
            color: var(--warning-700);
        }

        .form-container {
            padding: 40px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--radius-lg);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.error {
            background: var(--error-50);
            color: var(--error-700);
            border-left: 4px solid var(--error-500);
        }

        .alert.success {
            background: var(--success-50);
            color: var(--success-700);
            border-left: 4px solid var(--success-500);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gray-800);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 16px;
            transition: all 0.3s ease;
            background: var(--gray-50);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--warning-500);
            background: white;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-warning {
            background: var(--gradient-warning);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .btn-secondary {
            background: var(--gray-500);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--gray-600);
        }

        .btn-danger {
            background: var(--error-500);
            color: white;
        }

        .btn-danger:hover {
            background: var(--error-600);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .investment-info {
            background: var(--gray-50);
            padding: 15px;
            border-radius: var(--radius-lg);
            margin-bottom: 20px;
            display: none;
        }

        .investment-info.show {
            display: block;
        }

        .profit-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }

        .profit-type {
            padding: 10px;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-md);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .profit-type input {
            display: none;
        }

        .profit-type.selected {
            border-color: var(--warning-500);
            background: var(--warning-100);
            color: var(--warning-700);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .profit-types {
                grid-template-columns: 1fr 1fr;
            }
            .form-actions {
                flex-direction: column;
            }
        }
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

            <?php if ($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
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
                                    data-nama-kategori="<?= htmlspecialchars($inv['nama_kategori']) ?>"
                                    <?= $keuntungan['investasi_id'] == $inv['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($inv['judul_investasi']) ?> 
                                (<?= htmlspecialchars($inv['nama_kategori']) ?>)
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
                           class="form-control" placeholder="Contoh: Dividen Triwulan Q1 2024"
                           value="<?= htmlspecialchars($keuntungan['judul_keuntungan']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_keuntungan"><i class="fas fa-money-bill-wave"></i> Jumlah Keuntungan (Rp)</label>
                        <input type="text" name="jumlah_keuntungan" id="jumlah_keuntungan" 
                               class="form-control" 
                               placeholder="Contoh: 0.87, 1.500, 1.500.50, 2.500.000,75"
                               value="<?= number_format($keuntungan['jumlah_keuntungan'], 2, ',', '.') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="persentase_keuntungan"><i class="fas fa-percentage"></i> Persentase (%)</label>
                        <input type="number" name="persentase_keuntungan" id="persentase_keuntungan" 
                               class="form-control" step="0.01" min="0"
                               placeholder="Opsional"
                               value="<?= $keuntungan['persentase_keuntungan'] ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="tanggal_keuntungan"><i class="fas fa-calendar-alt"></i> Tanggal Keuntungan</label>
                    <input type="date" name="tanggal_keuntungan" id="tanggal_keuntungan" 
                           class="form-control"
                           value="<?= $keuntungan['tanggal_keuntungan'] ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Sumber Keuntungan</label>
                    <div class="profit-types">
                        <?php $sources = ['dividen', 'capital_gain', 'bunga', 'bonus', 'lainnya']; ?>
                        <?php foreach ($sources as $src): ?>
                            <div class="profit-type" onclick="selectProfitType(this, '<?= $src ?>')">
                                <input type="radio" name="sumber_keuntungan" value="<?= $src ?>"
                                       <?= $keuntungan['sumber_keuntungan'] == $src ? 'checked' : '' ?>>
                                <i class="fas <?= $src == 'dividen' ? 'fa-coins' : 
                                                ($src == 'capital_gain' ? 'fa-chart-line' : 
                                                ($src == 'bunga' ? 'fa-percent' : 
                                                ($src == 'bonus' ? 'fa-gift' : 'fa-ellipsis-h'))) ?>"></i><br>
                                <?= ucfirst($src) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status"><i class="fas fa-flag"></i> Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="realized" <?= $keuntungan['status'] == 'realized' ? 'selected' : '' ?>>
                                Sudah Direalisasi
                            </option>
                            <option value="unrealized" <?= $keuntungan['status'] == 'unrealized' ? 'selected' : '' ?>>
                                Belum Direalisasi
                            </option>
                        </select>
                    </div>
                    <div></div>
                </div>

                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-file-alt"></i> Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"
                              placeholder="Deskripsi tambahan mengenai keuntungan ini..."><?= htmlspecialchars($keuntungan['deskripsi']) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Keuntungan
                    </button>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="delete_keuntungan.php?id=<?= $keuntungan_id ?>" class="btn btn-danger"
                       onclick="return confirm('Apakah Anda yakin ingin menghapus keuntungan ini?');">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('investasi_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const investmentInfo = document.getElementById('investmentInfo');
            const categorySpan = document.getElementById('selectedCategory');
            const categoryInput = document.getElementById('kategori_id');

            if (selected.value) {
                const kategoriId = selected.dataset.kategori;
                const namaKategori = selected.dataset.namaKategori;
                
                categorySpan.textContent = namaKategori;
                categoryInput.value = kategoriId;
                investmentInfo.classList.add('show');
            } else {
                investmentInfo.classList.remove('show');
                categoryInput.value = '';
            }
        });

        function selectProfitType(element, value) {
            document.querySelectorAll('.profit-type').forEach(type => {
                type.classList.remove('selected');
            });
            element.classList.add('selected');
            element.querySelector('input').checked = true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="sumber_keuntungan"]:checked');
            if (checkedRadio) {
                checkedRadio.closest('.profit-type').classList.add('selected');
            }
        });

        // Parsing input uang
        document.getElementById('jumlah_keuntungan').addEventListener('blur', function() {
            let val = this.value.replace(/[^0-9.,]/g, '');
            if (val.includes(',')) {
                val = val.replace(/\./g, '').replace(',', '.');
            } else {
                val = val.replace(/\./g, '');
            }
            this.value = parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        });

        document.getElementById('editProfitForm').addEventListener('submit', function(e) {
            const raw = document.getElementById('jumlah_keuntungan').value;
            const parsed = raw.replace(/\./g, '').replace(',', '.');
            if (!isNaN(parsed)) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'jumlah_keuntungan_parsed';
                hidden.value = parsed;
                this.appendChild(hidden);
            }
        });
    </script>
</body>
</html>
