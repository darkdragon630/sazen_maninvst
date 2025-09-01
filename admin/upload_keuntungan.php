<?php
session_start();
require_once "../config.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Ambil data investasi untuk dropdown (tambahkan jumlah_investasi)
$sql_investasi = "SELECT i.id, i.judul_investasi, i.jumlah_investasi, k.nama_kategori, i.kategori_id 
                  FROM investasi i 
                  JOIN kategori k ON i.kategori_id = k.id 
                  ORDER BY i.judul_investasi";
$stmt_investasi = $koneksi->query($sql_investasi);
$investasi_list = $stmt_investasi->fetchAll();

$error = '';
$success = '';

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $investasi_id = $_POST['investasi_id'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $judul_keuntungan = trim($_POST['judul_keuntungan'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    // Sanitasi angka - hapus pemisah ribuan sebelum floatval
    $jumlah_keuntungan_raw = $_POST['jumlah_keuntungan'] ?? '0';
    $jumlah_keuntungan = floatval(str_replace(['.', ','], ['', '.'], $jumlah_keuntungan_raw));
    
    $persentase_keuntungan = !empty($_POST['persentase_keuntungan']) ? floatval($_POST['persentase_keuntungan']) : null;
    $tanggal_keuntungan = $_POST['tanggal_keuntungan'] ?? '';
    $sumber_keuntungan = $_POST['sumber_keuntungan'] ?? 'lainnya';
    $status = $_POST['status'] ?? 'realized';

    // Validasi
    if (empty($investasi_id) || empty($kategori_id) || empty($judul_keuntungan) || $jumlah_keuntungan <= 0 || empty($tanggal_keuntungan)) {
        $error = 'Semua field wajib diisi dengan benar. Jumlah keuntungan harus lebih dari 0.';
    } else {
        try {
            // Auto hitung persentase keuntungan jika belum diisi
            if (is_null($persentase_keuntungan)) {
                $sql_invest = "SELECT jumlah_investasi FROM investasi WHERE id = ?";
                $stmt_invest = $koneksi->prepare($sql_invest);
                $stmt_invest->execute([$investasi_id]);
                $invest_data = $stmt_invest->fetch();
                
                if ($invest_data && $invest_data['jumlah_investasi'] > 0) {
                    $persentase_keuntungan = ($jumlah_keuntungan / $invest_data['jumlah_investasi']) * 100;
                }
            }
            
            $sql = "INSERT INTO keuntungan_investasi 
                        (investasi_id, kategori_id, judul_keuntungan, deskripsi, jumlah_keuntungan, persentase_keuntungan, tanggal_keuntungan, sumber_keuntungan, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $koneksi->prepare($sql);
            $stmt->execute([$investasi_id, $kategori_id, $judul_keuntungan, $deskripsi, $jumlah_keuntungan, $persentase_keuntungan, $tanggal_keuntungan, $sumber_keuntungan, $status]);

            // Redirect setelah sukses untuk reset form
            $_SESSION['success'] = 'Keuntungan investasi berhasil ditambahkan!';
            header("Location: upload_keuntungan.php");
            exit;
        } catch (Exception $e) {
            // Error handling aman - log detail error, tampilkan pesan singkat ke user
            error_log("UPLOAD_KEUNTUNGAN_ERROR: " . $e->getMessage() . " | User ID: " . $_SESSION['user_id'] . " | Data: " . json_encode($_POST));
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.';
        }
    }
}

// Ambil pesan sukses dari session (jika ada)
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 600px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2d3748;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header h1 i {
            color: #667eea;
            margin-right: 10px;
        }

        .header p {
            color: #718096;
            font-size: 16px;
        }

        .form-container {
            margin-top: 20px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }

        .alert.success {
            background: rgba(72, 187, 120, 0.1);
            color: #2f855a;
            border: 1px solid rgba(72, 187, 120, 0.2);
        }

        .alert.error {
            background: rgba(245, 101, 101, 0.1);
            color: #c53030;
            border: 1px solid rgba(245, 101, 101, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group label i {
            color: #667eea;
            margin-right: 8px;
            width: 16px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: #fff;
        }

        .investment-info {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
            color: #2d3748;
            font-size: 14px;
        }

        .investment-info.show {
            display: block;
        }

        .profit-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .profit-type {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .profit-type:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .profit-type.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .profit-type input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .profit-type i {
            font-size: 20px;
            color: #667eea;
            display: block;
            margin-bottom: 8px;
        }

        .profit-type span {
            font-size: 12px;
            color: #4a5568;
            font-weight: 500;
        }

        .status-types {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        .status-type {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .status-type:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .status-type.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .status-type input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .status-type i {
            font-size: 18px;
            color: #667eea;
            display: block;
            margin-bottom: 8px;
        }

        .status-type span {
            font-size: 12px;
            color: #4a5568;
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            min-width: 120px;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: rgba(74, 85, 104, 0.1);
            color: #4a5568;
            border: 2px solid rgba(74, 85, 104, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(74, 85, 104, 0.2);
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            .header h1 {
                font-size: 24px;
            }

            .profit-types {
                grid-template-columns: repeat(2, 1fr);
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
            <h1><i class="fas fa-chart-line-up"></i> Tambah Keuntungan Investasi</h1>
            <p>Catat keuntungan dari investasi Anda</p>
        </div>

        <div class="form-container">
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

            <form method="POST" id="profitForm">
                <div class="form-group">
                    <label for="investasi_id"><i class="fas fa-briefcase"></i> Pilih Investasi</label>
                    <select name="investasi_id" id="investasi_id" class="form-control" required>
                        <option value="">-- Pilih Investasi --</option>
                        <?php foreach ($investasi_list as $inv): ?>
                            <option value="<?= $inv['id'] ?>" 
                                    data-kategori="<?= $inv['kategori_id'] ?>"
                                    data-nama-kategori="<?= htmlspecialchars($inv['nama_kategori']) ?>"
                                    data-jumlah="<?= $inv['jumlah_investasi'] ?>">
                                <?= htmlspecialchars($inv['judul_investasi']) ?> (<?= htmlspecialchars($inv['nama_kategori']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="investment-info" id="investmentInfo">
                    <strong>Kategori:</strong> <span id="selectedCategory"></span><br>
                    <strong>Jumlah Investasi:</strong> Rp <span id="selectedAmount"></span>
                    <input type="hidden" name="kategori_id" id="kategori_id">
                </div>

                <div class="form-group">
                    <label for="judul_keuntungan"><i class="fas fa-tag"></i> Judul Keuntungan</label>
                    <input type="text" name="judul_keuntungan" id="judul_keuntungan" class="form-control" 
                           placeholder="Contoh: Dividen Q1 2024, Capital Gain Saham BBCA" required>
                </div>

                <div class="form-group">
                    <label for="jumlah_keuntungan"><i class="fas fa-money-bill"></i> Jumlah Keuntungan (Rp)</label>
                    <input type="text" name="jumlah_keuntungan" id="jumlah_keuntungan" class="form-control" 
                           placeholder="0" required>
                </div>

                <div class="form-group">
                    <label for="persentase_keuntungan"><i class="fas fa-percentage"></i> Persentase Keuntungan (%)</label>
                    <input type="number" name="persentase_keuntungan" id="persentase_keuntungan" class="form-control" 
                           step="0.01" placeholder="Akan dihitung otomatis">
                </div>

                <div class="form-group">
                    <label for="tanggal_keuntungan"><i class="fas fa-calendar"></i> Tanggal Keuntungan</label>
                    <input type="date" name="tanggal_keuntungan" id="tanggal_keuntungan" class="form-control" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-source"></i> Sumber Keuntungan</label>
                    <div class="profit-types">
                        <div class="profit-type" onclick="selectProfitType(this, 'dividen')">
                            <input type="radio" name="sumber_keuntungan" value="dividen">
                            <i class="fas fa-coins"></i>
                            <span>Dividen</span>
                        </div>
                        <div class="profit-type" onclick="selectProfitType(this, 'capital_gain')">
                            <input type="radio" name="sumber_keuntungan" value="capital_gain">
                            <i class="fas fa-chart-line"></i>
                            <span>Capital Gain</span>
                        </div>
                        <div class="profit-type" onclick="selectProfitType(this, 'bunga')">
                            <input type="radio" name="sumber_keuntungan" value="bunga">
                            <i class="fas fa-percent"></i>
                            <span>Bunga</span>
                        </div>
                        <div class="profit-type selected" onclick="selectProfitType(this, 'lainnya')">
                            <input type="radio" name="sumber_keuntungan" value="lainnya" checked>
                            <i class="fas fa-ellipsis-h"></i>
                            <span>Lainnya</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-flag"></i> Status</label>
                    <div class="status-types">
                        <div class="status-type selected" onclick="selectStatusType(this, 'realized')">
                            <input type="radio" name="status" value="realized" checked>
                            <i class="fas fa-check-circle"></i>
                            <span>Direalisasi</span>
                        </div>
                        <div class="status-type" onclick="selectStatusType(this, 'unrealized')">
                            <input type="radio" name="status" value="unrealized">
                            <i class="fas fa-clock"></i>
                            <span>Belum Direalisasi</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-align-left"></i> Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" 
                              placeholder="Tambahkan catatan atau deskripsi keuntungan..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Keuntungan</button>
                    <a href="../dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Investment selection
        document.getElementById('investasi_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const investmentInfo = document.getElementById('investmentInfo');
            const categorySpan = document.getElementById('selectedCategory');
            const categoryInput = document.getElementById('kategori_id');
            const amountSpan = document.getElementById('selectedAmount');

            if (selected.value) {
                categorySpan.textContent = selected.dataset.namaKategori;
                categoryInput.value = selected.dataset.kategori;
                amountSpan.textContent = parseFloat(selected.dataset.jumlah).toLocaleString('id-ID');
                investmentInfo.classList.add('show');
                
                // Auto calculate percentage if profit amount is already filled
                const profitAmount = document.getElementById('jumlah_keuntungan').value;
                if (profitAmount) {
                    calculatePercentage();
                }
            } else {
                investmentInfo.classList.remove('show');
                categoryInput.value = '';
                amountSpan.textContent = '';
            }
        });

        // Profit type selection
        function selectProfitType(element, value) {
            document.querySelectorAll('.profit-type').forEach(type => type.classList.remove('selected'));
            element.classList.add('selected');
            element.querySelector('input').checked = true;
        }

        // Status type selection
        function selectStatusType(element, value) {
            document.querySelectorAll('.status-type').forEach(type => type.classList.remove('selected'));
            element.classList.add('selected');
            element.querySelector('input').checked = true;
        }

        // Format number input
        document.getElementById('jumlah_keuntungan').addEventListener('input', function() {
            // Remove non-numeric characters except decimal point
            let value = this.value.replace(/[^\d]/g, '');
            
            // Format as currency (Indonesian Rupiah style)
            if (value) {
                this.value = parseInt(value).toLocaleString('id-ID');
                calculatePercentage();
            }
        });

        // Auto calculate percentage
        function calculatePercentage() {
            const investasiSelect = document.getElementById('investasi_id');
            const profitInput = document.getElementById('jumlah_keuntungan');
            const percentageInput = document.getElementById('persentase_keuntungan');
            
            const selectedOption = investasiSelect.options[investasiSelect.selectedIndex];
            const jumlahInvestasi = parseFloat(selectedOption?.dataset.jumlah || 0);
            
            if (jumlahInvestasi > 0 && profitInput.value) {
                // Remove formatting to get actual number
                const profitValue = parseFloat(profitInput.value.replace(/\./g, ''));
                const percentage = (profitValue / jumlahInvestasi * 100).toFixed(2);
                percentageInput.value = percentage;
            }
        }

        // Set today as default date
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_keuntungan').value = today;
            
            // Initialize selected states
            const investasiSelect = document.getElementById('investasi_id');
            if (investasiSelect.value) {
                investasiSelect.dispatchEvent(new Event('change'));
            }
            
            const checkedRadio = document.querySelector('input[name="sumber_keuntungan"]:checked');
            if (checkedRadio) checkedRadio.closest('.profit-type').classList.add('selected');
            
            const checkedStatus = document.querySelector('input[name="status"]:checked');
            if (checkedStatus) checkedStatus.closest('.status-type').classList.add('selected');
        });

        // Form validation before submit
        document.getElementById('profitForm').addEventListener('submit', function(e) {
            const profitAmount = document.getElementById('jumlah_keuntungan').value;
            if (!profitAmount || profitAmount === '0') {
                e.preventDefault();
                alert('Jumlah keuntungan harus diisi dan lebih dari 0');
                return false;
            }
        });
    </script>
</body>
</html>
