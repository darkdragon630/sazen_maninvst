<?php
session_start();
require_once "../config.php";

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Ambil data investasi untuk dropdown
$sql_investasi = "SELECT i.id, i.judul_investasi, k.nama_kategori, i.kategori_id 
                  FROM investasi i 
                  JOIN kategori k ON i.kategori_id = k.id 
                  ORDER BY i.judul_investasi";
$stmt_investasi = $koneksi->query($sql_investasi);
$investasi_list = $stmt_investasi->fetchAll();

// Ambil data kategori
$sql_kategori = "SELECT * FROM kategori ORDER BY nama_kategori";
$stmt_kategori = $koneksi->query($sql_kategori);
$kategori_list = $stmt_kategori->fetchAll();

$error = '';
$success = '';

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $investasi_id = $_POST['investasi_id'];
    $kategori_id = $_POST['kategori_id'];
    $judul_keuntungan = trim($_POST['judul_keuntungan']);
    $deskripsi = trim($_POST['deskripsi']);
    $jumlah_keuntungan = floatval($_POST['jumlah_keuntungan']);
    $persentase_keuntungan = !empty($_POST['persentase_keuntungan']) ? floatval($_POST['persentase_keuntungan']) : null;
    $tanggal_keuntungan = $_POST['tanggal_keuntungan'];
    $sumber_keuntungan = $_POST['sumber_keuntungan'];
    $status = $_POST['status'];

    // Validasi
    if (empty($investasi_id) || empty($kategori_id) || empty($judul_keuntungan) || empty($jumlah_keuntungan) || empty($tanggal_keuntungan)) {
        $error = 'Semua field wajib diisi kecuali deskripsi dan persentase.';
    } else {
        try {
            $sql = "INSERT INTO keuntungan_investasi (investasi_id, kategori_id, judul_keuntungan, deskripsi, jumlah_keuntungan, persentase_keuntungan, tanggal_keuntungan, sumber_keuntungan, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $koneksi->prepare($sql);
            $stmt->execute([$investasi_id, $kategori_id, $judul_keuntungan, $deskripsi, $jumlah_keuntungan, $persentase_keuntungan, $tanggal_keuntungan, $sumber_keuntungan, $status]);
            
            $success = 'Keuntungan investasi berhasil ditambahkan!';
            
            // Reset form
            $_POST = array();
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
    <title>Tambah Keuntungan Investasi - SAZEN</title>
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
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.8;
        }

        .form-container {
            padding: 40px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-weight: 500;
        }

        .alert.error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert.success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
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

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .investment-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
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
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .profit-type input {
            display: none;
        }

        .profit-type.selected {
            border-color: #667eea;
            background: #f0f2ff;
            color: #667eea;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .profit-types {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line-up"></i> Tambah Keuntungan Investasi</h1>
            <p>Catat keuntungan dari investasi Anda</p>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="profitForm">
                <div class="form-group">
                    <label for="investasi_id">
                        <i class="fas fa-briefcase"></i> Pilih Investasi
                    </label>
                    <select name="investasi_id" id="investasi_id" class="form-control" required>
                        <option value="">-- Pilih Investasi --</option>
                        <?php foreach ($investasi_list as $inv): ?>
                            <option value="<?= $inv['id'] ?>" 
                                    data-kategori="<?= $inv['kategori_id'] ?>"
                                    data-nama-kategori="<?= htmlspecialchars($inv['nama_kategori']) ?>"
                                    <?= (isset($_POST['investasi_id']) && $_POST['investasi_id'] == $inv['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($inv['judul_investasi']) ?> 
                                (<?= htmlspecialchars($inv['nama_kategori']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="investment-info" id="investmentInfo">
                    <strong>Kategori:</strong> <span id="selectedCategory"></span>
                    <input type="hidden" name="kategori_id" id="kategori_id">
                </div>

                <div class="form-group">
                    <label for="judul_keuntungan">
                        <i class="fas fa-tag"></i> Judul Keuntungan
                    </label>
                    <input type="text" name="judul_keuntungan" id="judul_keuntungan" 
                           class="form-control" placeholder="Contoh: Dividen Triwulan Q1 2024"
                           value="<?= isset($_POST['judul_keuntungan']) ? htmlspecialchars($_POST['judul_keuntungan']) : '' ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_keuntungan">
                            <i class="fas fa-money-bill-wave"></i> Jumlah Keuntungan (Rp)
                        </label>
                        <input type="number" name="jumlah_keuntungan" id="jumlah_keuntungan" 
                               class="form-control" step="0.01" min="0"
                               placeholder="0"
                               value="<?= isset($_POST['jumlah_keuntungan']) ? $_POST['jumlah_keuntungan'] : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="persentase_keuntungan">
                            <i class="fas fa-percentage"></i> Persentase (%)
                        </label>
                        <input type="number" name="persentase_keuntungan" id="persentase_keuntungan" 
                               class="form-control" step="0.01" min="0"
                               placeholder="Opsional"
                               value="<?= isset($_POST['persentase_keuntungan']) ? $_POST['persentase_keuntungan'] : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="tanggal_keuntungan">
                        <i class="fas fa-calendar-alt"></i> Tanggal Keuntungan
                    </label>
                    <input type="date" name="tanggal_keuntungan" id="tanggal_keuntungan" 
                           class="form-control"
                           value="<?= isset($_POST['tanggal_keuntungan']) ? $_POST['tanggal_keuntungan'] : date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-layer-group"></i> Sumber Keuntungan
                    </label>
                    <div class="profit-types">
                        <div class="profit-type" onclick="selectProfitType(this, 'dividen')">
                            <input type="radio" name="sumber_keuntungan" value="dividen" 
                                   <?= (isset($_POST['sumber_keuntungan']) && $_POST['sumber_keuntungan'] == 'dividen') ? 'checked' : '' ?>>
                            <i class="fas fa-coins"></i><br>Dividen
                        </div>
                        <div class="profit-type" onclick="selectProfitType(this, 'capital_gain')">
                            <input type="radio" name="sumber_keuntungan" value="capital_gain"
                                   <?= (isset($_POST['sumber_keuntungan']) && $_POST['sumber_keuntungan'] == 'capital_gain') ? 'checked' : '' ?>>
                            <i class="fas fa-chart-line"></i><br>Capital Gain
                        </div>
                        <div class="profit-type" onclick="selectProfitType(this, 'bunga')">
                            <input type="radio" name="sumber_keuntungan" value="bunga"
                                   <?= (isset($_POST['sumber_keuntungan']) && $_POST['sumber_keuntungan'] == 'bunga') ? 'checked' : '' ?>>
                            <i class="fas fa-percent"></i><br>Bunga
                        </div>
                        <div class="profit-type" onclick="selectProfitType(this, 'bonus')">
                            <input type="radio" name="sumber_keuntungan" value="bonus"
                                   <?= (isset($_POST['sumber_keuntungan']) && $_POST['sumber_keuntungan'] == 'bonus') ? 'checked' : '' ?>>
                            <i class="fas fa-gift"></i><br>Bonus
                        </div>
                        <div class="profit-type" onclick="selectProfitType(this, 'lainnya')">
                            <input type="radio" name="sumber_keuntungan" value="lainnya"
                                   <?= (isset($_POST['sumber_keuntungan']) && $_POST['sumber_keuntungan'] == 'lainnya') || !isset($_POST['sumber_keuntungan']) ? 'checked' : '' ?>>
                            <i class="fas fa-ellipsis-h"></i><br>Lainnya
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">
                            <i class="fas fa-flag"></i> Status
                        </label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="realized" <?= (isset($_POST['status']) && $_POST['status'] == 'realized') || !isset($_POST['status']) ? 'selected' : '' ?>>
                                Sudah Direalisasi
                            </option>
                            <option value="unrealized" <?= (isset($_POST['status']) && $_POST['status'] == 'unrealized') ? 'selected' : '' ?>>
                                Belum Direalisasi
                            </option>
                        </select>
                    </div>
                    <div></div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">
                        <i class="fas fa-file-alt"></i> Deskripsi (Opsional)
                    </label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"
                              placeholder="Deskripsi tambahan mengenai keuntungan ini..."><?= isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Keuntungan
                    </button>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Handle investment selection
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

        // Handle profit type selection
        function selectProfitType(element, value) {
            // Remove selected class from all
            document.querySelectorAll('.profit-type').forEach(type => {
                type.classList.remove('selected');
            });
            
            // Add selected class to clicked element
            element.classList.add('selected');
            
            // Check the radio button
            element.querySelector('input').checked = true;
        }

        // Initialize selected profit type on page load
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="sumber_keuntungan"]:checked');
            if (checkedRadio) {
                checkedRadio.closest('.profit-type').classList.add('selected');
            } else {
                // Select 'lainnya' by default
                document.querySelectorAll('.profit-type').forEach(type => {
                    if (type.textContent.includes('Lainnya')) {
                        type.classList.add('selected');
                        type.querySelector('input').checked = true;
                    }
                });
            }

            // Show investment info if investment is already selected
            const investasiSelect = document.getElementById('investasi_id');
            if (investasiSelect.value) {
                investasiSelect.dispatchEvent(new Event('change'));
            }
        });

        // Auto calculate percentage if needed
        document.getElementById('jumlah_keuntungan').addEventListener('input', function() {
            // You can add logic here to auto-calculate percentage based on investment amount
            // This would require additional data about the original investment amount
        });
    </script>
</body>
</html>
