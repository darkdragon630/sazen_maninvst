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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Keuntungan Investasi - SAZEN</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Reset dan dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
            color: #2c3e50;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Container utama */
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.25);
            overflow: hidden;
            padding: 40px 50px;
            transition: box-shadow 0.3s ease;
        }
        .container:hover {
            box-shadow: 0 35px 70px rgba(102, 126, 234, 0.35);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #fff;
            padding: 40px 30px;
            border-radius: 24px 24px 0 0;
            text-align: center;
            box-shadow: 0 8px 20px rgba(230, 126, 34, 0.3);
        }
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 1.2px;
            text-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .header p {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
        }

        /* Current data box */
        .current-data {
            background: #fff8e1;
            border-left: 6px solid #f39c12;
            padding: 20px 25px;
            margin: 30px 0 40px;
            border-radius: 16px;
            font-weight: 600;
            color: #7a5e00;
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.1);
            line-height: 1.5;
            user-select: none;
        }

        /* Alert messages */
        .alert {
            padding: 18px 25px;
            margin-bottom: 30px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .alert.error {
            background: #ffe6e6;
            color: #cc1f1f;
            border-left: 6px solid #cc1f1f;
        }
        .alert.success {
            background: #e6f4ea;
            color: #2d7a2d;
            border-left: 6px solid #2d7a2d;
        }

        /* Form groups */
        .form-group {
            margin-bottom: 28px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: #34495e;
            font-size: 1.05rem;
            user-select: none;
        }
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2.5px solid #d1d9e6;
            border-radius: 16px;
            font-size: 1.1rem;
            background: #f9fbff;
            color: #34495e;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
        }
        .form-control::placeholder {
            color: #aab7c4;
            font-weight: 400;
        }
        .form-control:focus {
            outline: none;
            border-color: #f39c12;
            background: #fff;
            box-shadow: 0 0 12px rgba(243, 156, 18, 0.3);
        }

        /* Form row grid */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px 30px;
        }

        /* Buttons */
        .btn {
            padding: 14px 36px;
            border: none;
            border-radius: 20px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            user-select: none;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
        }
        .btn i {
            font-size: 1.2rem;
        }
        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #fff;
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
        }
        .btn-warning:hover,
        .btn-warning:focus {
            background: linear-gradient(135deg, #e67e22, #d35400);
            box-shadow: 0 10px 30px rgba(211, 84, 0, 0.5);
            transform: translateY(-3px);
            outline: none;
        }
        .btn-secondary {
            background: #6c757d;
            color: #fff;
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.3);
        }
        .btn-secondary:hover,
        .btn-secondary:focus {
            background: #5a6268;
            box-shadow: 0 10px 25px rgba(90, 98, 104, 0.5);
            transform: translateY(-3px);
            outline: none;
        }

        /* Investment info */
        .investment-info {
            background: #f0f4ff;
            padding: 18px 25px;
            border-radius: 16px;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            color: #34495e;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            user-select: none;
        }
        .investment-info.show {
            display: block;
        }

        /* Profit types */
        .profit-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 18px;
        }
        .profit-type {
            padding: 18px 12px;
            border: 2.5px solid #d1d9e6;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            font-weight: 600;
            color: #34495e;
            user-select: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .profit-type i {
            font-size: 2.2rem;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }
        .profit-type:hover {
            border-color: #f39c12;
            background: #fff8e1;
            box-shadow: 0 8px 20px rgba(243, 156, 18, 0.25);
        }
        .profit-type.selected {
            border-color: #f39c12;
            background: #fff8e1;
            color: #f39c12;
            box-shadow: 0 10px 25px rgba(243, 156, 18, 0.4);
        }
        .profit-type.selected i {
            color: #f39c12;
        }
        .profit-type input {
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 30px 25px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .profit-types {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .header h1 {
                font-size: 2rem;
            }
            .header p {
                font-size: 1rem;
            }
        }
        @media (max-width: 480px) {
            .container {
                padding: 25px 20px;
                border-radius: 20px;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main">
        <header class="header">
            <h1><i class="fas fa-edit" aria-hidden="true"></i> Edit Keuntungan Investasi</h1>
            <p>Perbarui data keuntungan investasi</p>
        </header>

        <section class="form-container" aria-label="Form edit keuntungan investasi">
            <div class="current-data" aria-live="polite" aria-atomic="true">
                <strong>Data Saat Ini:</strong><br />
                <strong>Investasi:</strong> <?= htmlspecialchars($keuntungan['judul_investasi']) ?><br />
                <strong>Kategori:</strong> <?= htmlspecialchars($keuntungan['nama_kategori']) ?><br />
                <strong>Keuntungan:</strong> <?= htmlspecialchars($keuntungan['judul_keuntungan']) ?><br />
                <strong>Jumlah:</strong> Rp <?= number_format($keuntungan['jumlah_keuntungan'], 2, ',', '.') ?>
            </div>

            <?php if ($error): ?>
                <div class="alert error" role="alert">
                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="editProfitForm" novalidate>
                <div class="form-group">
                    <label for="investasi_id"><i class="fas fa-briefcase" aria-hidden="true"></i> Pilih Investasi</label>
                    <select name="investasi_id" id="investasi_id" class="form-control" required aria-required="true" aria-describedby="investasiHelp">
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
                    <small id="investasiHelp" class="sr-only">Pilih investasi untuk memperbarui kategori secara otomatis</small>
                </div>

                <div class="investment-info show" id="investmentInfo" aria-live="polite" aria-atomic="true">
                    <strong>Kategori:</strong> <span id="selectedCategory"><?= htmlspecialchars($keuntungan['nama_kategori']) ?></span>
                    <input type="hidden" name="kategori_id" id="kategori_id" value="<?= $keuntungan['kategori_id'] ?>" />
                </div>

                <div class="form-group">
                    <label for="judul_keuntungan"><i class="fas fa-tag" aria-hidden="true"></i> Judul Keuntungan</label>
                    <input type="text" name="judul_keuntungan" id="judul_keuntungan" 
                           class="form-control" value="<?= htmlspecialchars($keuntungan['judul_keuntungan']) ?>" required aria-required="true" />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_keuntungan"><i class="fas fa-money-bill-wave" aria-hidden="true"></i> Jumlah Keuntungan (Rp)</label>
                        <input type="text" name="jumlah_keuntungan" id="jumlah_keuntungan" 
                               class="form-control" placeholder="Contoh: 0.87, 1.500,75, 2.500.000,75"
                               value="<?= number_format($keuntungan['jumlah_keuntungan'], 2, ',', '.') ?>" required aria-required="true" />
                    </div>

                    <div class="form-group">
                        <label for="persentase_keuntungan"><i class="fas fa-percentage" aria-hidden="true"></i> Persentase (%)</label>
                        <input type="number" name="persentase_keuntungan" id="persentase_keuntungan" 
                               class
