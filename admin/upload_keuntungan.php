<?php
session_start();
require_once "../config.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Ambil data investasi untuk dropdown
try {
    $check_columns = $koneksi->query("DESCRIBE investasi")->fetchAll();
    $column_names = array_column($check_columns, 'Field');
    
    // Tentukan kolom jumlah investasi
    $amount_column = 'jumlah'; // default
    if (in_array('jumlah_investasi', $column_names)) {
        $amount_column = 'jumlah_investasi';
    } elseif (in_array('nilai_investasi', $column_names)) {
        $amount_column = 'nilai_investasi';
    } elseif (in_array('amount', $column_names)) {
        $amount_column = 'amount';
    }
    
    $sql_investasi = "SELECT i.id, i.judul_investasi, 
                             COALESCE(i.{$amount_column}, 0) as jumlah_investasi, 
                             k.nama_kategori, i.kategori_id 
                      FROM investasi i 
                      JOIN kategori k ON i.kategori_id = k.id 
                      ORDER BY i.judul_investasi";
    $stmt_investasi = $koneksi->query($sql_investasi);
    $investasi_list = $stmt_investasi->fetchAll();
} catch (Exception $e) {
    error_log("INVESTASI_QUERY_ERROR: " . $e->getMessage());
    $investasi_list = [];
}

$error = '';
$success = '';

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $investasi_id = $_POST['investasi_id'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $judul_keuntungan = trim($_POST['judul_keuntungan'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    // Parsing jumlah keuntungan
    if (isset($_POST['jumlah_keuntungan_parsed']) && is_numeric($_POST['jumlah_keuntungan_parsed'])) {
        $jumlah_keuntungan = floatval($_POST['jumlah_keuntungan_parsed']);
    } else {
        $jumlah_keuntungan_raw = trim($_POST['jumlah_keuntungan'] ?? '0');
        $jumlah_keuntungan = parseInputValuePHP($jumlah_keuntungan_raw);
    }

    // Persentase: jika diisi manual, konversi ke desimal
    $persentase_input = $_POST['persentase_keuntungan'] ?? null;
    $persentase_keuntungan = null;
    if (!empty($persentase_input) && is_numeric($persentase_input)) {
        $persentase_keuntungan = floatval($persentase_input) / 100; // input % → simpan sebagai desimal
    }

    $tanggal_keuntungan = $_POST['tanggal_keuntungan'] ?? '';
    $sumber_keuntungan = $_POST['sumber_keuntungan'] ?? 'lainnya';
    $status = $_POST['status'] ?? 'realized';

    // Validasi
    if (empty($investasi_id) || empty($kategori_id) || empty($judul_keuntungan) || $jumlah_keuntungan < 0 || empty($tanggal_keuntungan)) {
        $error = 'Semua field wajib diisi. Jumlah keuntungan harus ≥ 0.';
    } else {
        try {
            // Auto-calculate persentase jika belum diisi
            if (is_null($persentase_keuntungan)) {
                try {
                    $sql_invest = "SELECT {$amount_column} as jumlah_investasi FROM investasi WHERE id = ?";
                    $stmt_invest = $koneksi->prepare($sql_invest);
                    $stmt_invest->execute([$investasi_id]);
                    $invest_data = $stmt_invest->fetch();
                    
                    if ($invest_data && $invest_data['jumlah_investasi'] > 0) {
                        $persentase_keuntungan = $jumlah_keuntungan / $invest_data['jumlah_investasi']; // sudah desimal
                    }
                } catch (Exception $e) {
                    error_log("PERCENTAGE_CALC_ERROR: " . $e->getMessage());
                }
            }
            
            // Simpan ke database
            $sql = "INSERT INTO keuntungan_investasi 
                        (investasi_id, kategori_id, judul_keuntungan, deskripsi, jumlah_keuntungan, persentase_keuntungan, tanggal_keuntungan, sumber_keuntungan, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $koneksi->prepare($sql);
            $stmt->execute([
                $investasi_id,
                $kategori_id,
                $judul_keuntungan,
                $deskripsi,
                $jumlah_keuntungan,
                $persentase_keuntungan,
                $tanggal_keuntungan,
                $sumber_keuntungan,
                $status
            ]);

            $_SESSION['success'] = 'Keuntungan berhasil ditambahkan!';
            header("Location: upload_keuntungan.php");
            exit;
        } catch (Exception $e) {
            error_log("UPLOAD_KEUNTUNGAN_ERROR: " . $e->getMessage() . " | Data: " . json_encode($_POST));
            $error = 'Gagal menyimpan data. Cek struktur database.';
        }
    }
}

// Ambil pesan sukses dari session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Fungsi parsing nilai (PHP)
function parseInputValuePHP($value) {
    $value = preg_replace('/[^\d\.\,]/', '', $value); // bersihkan
    if (empty($value)) return 0;

    $lastComma = strrpos($value, ',');
    $lastDot = strrpos($value, '.');

    if ($lastComma !== false && $lastDot !== false) {
        if ($lastComma > $lastDot) {
            return floatval(str_replace(['.', ','], ['', '.'], $value));
        } else {
            return floatval(str_replace(',', '', $value));
        }
    } elseif ($lastComma !== false) {
        $parts = explode(',', $value);
        if (count($parts) == 2 && strlen($parts[1]) <= 2) {
            return floatval(str_replace(',', '.', $value));
        } else {
            return floatval(str_replace(',', '', $value));
        }
    } elseif ($lastDot !== false) {
        $parts = explode('.', $value);
        if (count($parts) == 2 && strlen($parts[1]) <= 2 && strlen($parts[0]) < 4) {
            return floatval($value);
        } else {
            return floatval(str_replace('.', '', $value));
        }
    } else {
        return floatval($value);
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

        .profit-types, .status-types {
            display: grid;
            gap: 10px;
            margin-top: 10px;
        }

        .profit-types { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
        .status-types { grid-template-columns: 1fr 1fr; }

        .profit-type, .status-type {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .profit-type:hover, .status-type:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .profit-type.selected, .status-type.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .profit-type input, .status-type input {
            position: absolute;
            opacity: 0;
            width: 0;
        }

        .profit-type i, .status-type i {
            font-size: 18px;
            color: #667eea;
            display: block;
            margin-bottom: 8px;
        }

        .profit-type span, .status-type span {
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
            .container { padding: 20px; margin: 10px; }
            .header h1 { font-size: 24px; }
            .form-actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line-up"></i> Tambah Keuntungan</h1>
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
                    <div id="investmentAmountContainer">
                        <strong>Jumlah Investasi:</strong> Rp <span id="selectedAmount"></span>
                    </div>
                    <input type="hidden" name="kategori_id" id="kategori_id">
                </div>

                <div class="form-group">
                    <label for="judul_keuntungan"><i class="fas fa-tag"></i> Judul Keuntungan</label>
                    <input type="text" name="judul_keuntungan" id="judul_keuntungan" class="form-control"
                           placeholder="Contoh: Dividen Q1, Capital Gain" required>
                </div>

                <div class="form-group">
                    <label for="jumlah_keuntungan"><i class="fas fa-money-bill"></i> Jumlah Keuntungan (Rp)</label>
                    <input type="text" name="jumlah_keuntungan" id="jumlah_keuntungan" class="form-control"
                           placeholder="Contoh: 0.82, 1.500,75, 10.000" required>
                    <small style="color: #718096; font-size: 12px; margin-top: 5px; display: block;">
                        <i class="fas fa-info-circle"></i> Dukung desimal: 0.82, 1.000,75
                    </small>
                </div>

                <div class="form-group">
                    <label for="persentase_keuntungan"><i class="fas fa-percentage"></i> Persentase (%)</label>
                    <input type="number" name="persentase_keuntungan" id="persentase_keuntungan"
                           class="form-control" step="0.000001" min="0"
                           placeholder="Opsional — akan dihitung otomatis">
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
                            <span>Belum</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-align-left"></i> Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"
                              placeholder="Catatan tambahan..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="../dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Update info investasi
        document.getElementById('investasi_id').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const info = document.getElementById('investmentInfo');
            if (selected.value) {
                document.getElementById('selectedCategory').textContent = selected.dataset.namaKategori;
                document.getElementById('kategori_id').value = selected.dataset.kategori;
                
                const amount = parseFloat(selected.dataset.jumlah || 0);
                if (amount > 0) {
                    document.getElementById('selectedAmount').textContent = amount.toLocaleString('id-ID');
                    document.getElementById('investmentAmountContainer').style.display = 'block';
                } else {
                    document.getElementById('investmentAmountContainer').style.display = 'none';
                }
                info.classList.add('show');
                calculatePercentage();
            } else {
                info.classList.remove('show');
            }
        });

        function selectProfitType(el, value) {
            document.querySelectorAll('.profit-type').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }

        function selectStatusType(el, value) {
            document.querySelectorAll('.status-type').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }

        function parseInputValue(value) {
            value = value.replace(/Rp\s*/gi, '').trim();
            if (!value) return 0;

            const lastComma = value.lastIndexOf(',');
            const lastDot = value.lastIndexOf('.');

            if (lastComma > lastDot && lastComma !== -1) {
                return parseFloat(value.replace(/\./g, '').replace(',', '.'));
            } else if (lastDot > lastComma && lastDot !== -1) {
                return parseFloat(value.replace(/,/g, ''));
            } else if (lastComma !== -1) {
                return parseFloat(value.replace(',', '.'));
            } else if (lastDot !== -1) {
                return parseFloat(value);
            } else {
                return parseFloat(value);
            }
        }

        function calculatePercentage() {
            const select = document.getElementById('investasi_id');
            const profitInput = document.getElementById('jumlah_keuntungan');
            const pctInput = document.getElementById('persentase_keuntungan');
            if (pctInput.value.trim() !== '') return; // jangan timpa

            const selected = select.options[select.selectedIndex];
            const amount = parseFloat(selected?.dataset.jumlah || 0);
            const profitRaw = profitInput.value.trim();
            if (!profitRaw || amount <= 0) return;

            const profit = parseInputValue(profitRaw);
            if (profit >= 0) {
                const percentage = (profit / amount) * 100;
                pctInput.value = percentage < 0.01 
                    ? percentage.toFixed(6) 
                    : (percentage < 1 ? percentage.toFixed(4) : percentage.toFixed(2));
            }
        }

        document.getElementById('jumlah_keuntungan').addEventListener('input', calculatePercentage);
        document.getElementById('jumlah_keuntungan').addEventListener('blur', function () {
            const val = this.value.trim();
            if (val && !isNaN(parseInputValue(val))) {
                // Valid, biarkan
            }
        });

        document.getElementById('profitForm').addEventListener('submit', function (e) {
            const raw = document.getElementById('jumlah_keuntungan').value.trim();
            if (!raw) {
                e.preventDefault();
                alert('Isi jumlah keuntungan');
                return;
            }
            const parsed = parseInputValue(raw);
            if (isNaN(parsed) || parsed < 0) {
                e.preventDefault();
                alert('Jumlah tidak valid');
                return;
            }

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'jumlah_keuntungan_parsed';
            hidden.value = parsed;
            this.appendChild(hidden);
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('tanggal_keuntungan').value = new Date().toISOString().split('T')[0];
            if (document.getElementById('investasi_id').value) {
                document.getElementById('investasi_id').dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>
