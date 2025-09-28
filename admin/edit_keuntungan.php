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
$success = '';

// Fungsi parsing rupiah yang diperbaiki
function parseRupiah($value) {
    if (!$value) return 0;
    
    // Hapus semua karakter kecuali angka, koma, dan titik
    $value = preg_replace('/[^\d\,\.]/', '', $value);
    
    $lastComma = strrpos($value, ',');
    $lastDot = strrpos($value, '.');
    
    if ($lastComma === false && $lastDot === false) {
        return floatval($value);
    } elseif ($lastComma !== false && $lastDot !== false) {
        if ($lastComma > $lastDot) {
            // Koma sebagai desimal
            $integerPart = substr($value, 0, $lastComma);
            $decimalPart = substr($value, $lastComma + 1);
            return floatval(str_replace(['.', ','], ['', '.'], $integerPart . '.' . $decimalPart));
        } else {
            // Titik sebagai desimal
            return floatval(str_replace(',', '', $value));
        }
    } elseif ($lastComma !== false) {
        // Hanya koma - anggap sebagai desimal
        return floatval(str_replace(',', '.', $value));
    } else {
        // Hanya titik - bisa pemisah ribuan atau desimal
        if (strlen(substr($value, $lastDot + 1)) <= 2) {
            // Kemungkinan desimal
            return floatval($value);
        } else {
            // Kemungkinan pemisah ribuan
            return floatval(str_replace('.', '', $value));
        }
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
            
            $success = 'Keuntungan berhasil diperbarui!';
            // Refresh data after update
            $stmt->execute([$keuntungan_id]);
            $keuntungan = $stmt->fetch();
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
    <title>Edit Keuntungan - SAZEN Investment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modern Dashboard CSS - Enhanced Version */

        /* CSS Variables for consistent theming */
        :root {
          /* Primary Colors */
          --primary-color: #667eea;
          --primary-dark: #5a6fd8;
          --primary-light: #a5b4fc;
          --secondary-color: #f093fb;
          --accent-color: #4facfe;
          
          /* Status Colors */
          --success-color: #10b981;
          --warning-color: #f59e0b;
          --error-color: #ef4444;
          --info-color: #3b82f6;
          
          /* Neutral Colors */
          --background-primary: #0f172a;
          --background-secondary: #1e293b;
          --background-tertiary: #334155;
          --surface-primary: #1e293b;
          --surface-secondary: #334155;
          --surface-hover: #475569;
          
          /* Text Colors */
          --text-primary: #f8fafc;
          --text-secondary: #cbd5e1;
          --text-muted: #94a3b8;
          --text-accent: #a5b4fc;
          
          /* Border & Shadow */
          --border-color: #334155;
          --border-light: #475569;
          --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
          --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
          --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
          --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
          
          /* Spacing */
          --spacing-xs: 0.25rem;
          --spacing-sm: 0.5rem;
          --spacing-md: 1rem;
          --spacing-lg: 1.5rem;
          --spacing-xl: 2rem;
          --spacing-2xl: 3rem;
          
          /* Border Radius */
          --radius-sm: 0.375rem;
          --radius-md: 0.5rem;
          --radius-lg: 0.75rem;
          --radius-xl: 1rem;
          
          /* Transitions */
          --transition-fast: 150ms ease-in-out;
          --transition-normal: 250ms ease-in-out;
          --transition-slow: 350ms ease-in-out;
        }

        /* Global Styles */
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        html {
          font-size: 16px;
          scroll-behavior: smooth;
        }

        body {
          font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background: linear-gradient(135deg, var(--background-primary) 0%, var(--background-secondary) 100%);
          color: var(--text-primary);
          line-height: 1.6;
          min-height: 100vh;
          overflow-x: hidden;
        }

        /* Container */
        .container {
          max-width: 1200px;
          margin: 0 auto;
          padding: var(--spacing-xl) var(--spacing-lg);
          min-height: 100vh;
        }

        /* Investments Section */
        .investments-section {
          background: var(--surface-primary);
          border-radius: var(--radius-xl);
          padding: var(--spacing-2xl);
          box-shadow: var(--shadow-xl);
          border: 1px solid var(--border-color);
          position: relative;
          overflow: hidden;
        }

        .investments-section::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          height: 4px;
          background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
          border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        /* Section Header */
        .section-header {
          margin-bottom: var(--spacing-2xl);
          text-align: center;
          position: relative;
        }

        .header-content {
          position: relative;
          z-index: 1;
        }

        .section-title {
          font-size: 2rem;
          font-weight: 700;
          background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
          background-clip: text;
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          margin-bottom: var(--spacing-sm);
          display: flex;
          align-items: center;
          justify-content: center;
          gap: var(--spacing-md);
        }

        .section-title i {
          background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
          background-clip: text;
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          font-size: 1.8rem;
        }

        .section-subtitle {
          color: var(--text-secondary);
          font-size: 1.1rem;
          font-weight: 400;
        }

        /* Current Data Display */
        .current-data {
          background: linear-gradient(135deg, var(--surface-secondary), var(--surface-primary));
          border: 1px solid var(--border-light);
          border-radius: var(--radius-lg);
          padding: var(--spacing-lg);
          margin-bottom: var(--spacing-xl);
          box-shadow: var(--shadow-md);
          position: relative;
          overflow: hidden;
          line-height: 1.8;
        }

        .current-data::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          width: 4px;
          height: 100%;
          background: linear-gradient(180deg, var(--info-color), var(--primary-color));
        }

        .current-data strong {
          color: var(--text-accent);
          font-weight: 600;
        }

        /* Alert Styles */
        .alert {
          padding: var(--spacing-lg);
          border-radius: var(--radius-lg);
          margin-bottom: var(--spacing-xl);
          display: flex;
          align-items: center;
          gap: var(--spacing-md);
          font-weight: 500;
          box-shadow: var(--shadow-md);
          border: 1px solid transparent;
          animation: slideInDown 0.3s ease-out;
        }

        @keyframes slideInDown {
          from {
            opacity: 0;
            transform: translateY(-20px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        .alert.error {
          background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
          color: #fca5a5;
          border-color: rgba(239, 68, 68, 0.2);
        }

        .alert.success {
          background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
          color: #86efac;
          border-color: rgba(16, 185, 129, 0.2);
        }

        .alert.info {
          background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05));
          color: #93c5fd;
          border-color: rgba(59, 130, 246, 0.2);
        }

        /* Form Styles */
        .form-container {
          display: flex;
          flex-direction: column;
          gap: var(--spacing-xl);
        }

        .form-group {
          display: flex;
          flex-direction: column;
          gap: var(--spacing-sm);
        }

        .form-group label {
          font-weight: 600;
          color: var(--text-primary);
          display: flex;
          align-items: center;
          gap: var(--spacing-sm);
          font-size: 0.95rem;
        }

        .form-group label i {
          color: var(--primary-color);
          width: 20px;
          text-align: center;
        }

        .form-control {
          background: var(--surface-secondary);
          border: 2px solid var(--border-color);
          border-radius: var(--radius-lg);
          padding: var(--spacing-md) var(--spacing-lg);
          color: var(--text-primary);
          font-size: 1rem;
          transition: all var(--transition-normal);
          width: 100%;
        }

        .form-control:focus {
          outline: none;
          border-color: var(--primary-color);
          background: var(--surface-primary);
          box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
          transform: translateY(-1px);
        }

        .form-control:hover {
          border-color: var(--border-light);
          background: var(--surface-primary);
        }

        .form-control::placeholder {
          color: var(--text-muted);
        }

        /* Form Row */
        .form-row {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: var(--spacing-lg);
        }

        @media (max-width: 768px) {
          .form-row {
            grid-template-columns: 1fr;
          }
        }

        /* Investment Info */
        .investment-info {
          background: linear-gradient(135deg, var(--surface-secondary), var(--background-tertiary));
          border: 1px solid var(--border-light);
          border-radius: var(--radius-lg);
          padding: var(--spacing-lg);
          margin: var(--spacing-md) 0;
          opacity: 0;
          transform: translateY(-10px);
          transition: all var(--transition-normal);
          box-shadow: var(--shadow-sm);
        }

        .investment-info.show {
          opacity: 1;
          transform: translateY(0);
        }

        .investment-info strong {
          color: var(--text-accent);
          font-weight: 600;
        }

        /* Profit Types */
        .profit-types {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
          gap: var(--spacing-md);
          margin-top: var(--spacing-md);
        }

        .profit-type {
          background: var(--surface-secondary);
          border: 2px solid var(--border-color);
          border-radius: var(--radius-lg);
          padding: var(--spacing-lg);
          text-align: center;
          cursor: pointer;
          transition: all var(--transition-normal);
          position: relative;
          overflow: hidden;
          user-select: none;
        }

        .profit-type::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
          transition: left var(--transition-slow);
        }

        .profit-type:hover {
          border-color: var(--primary-color);
          background: var(--surface-primary);
          transform: translateY(-2px);
          box-shadow: var(--shadow-lg);
        }

        .profit-type:hover::before {
          left: 100%;
        }

        .profit-type.selected {
          background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(240, 147, 251, 0.1));
          border-color: var(--primary-color);
          color: var(--text-accent);
          transform: translateY(-2px);
          box-shadow: var(--shadow-lg);
        }

        .profit-type i {
          font-size: 1.5rem;
          color: var(--primary-color);
          margin-bottom: var(--spacing-sm);
          display: block;
        }

        .profit-type input[type="radio"] {
          display: none;
        }

        /* Buttons */
        .btn {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: var(--spacing-sm);
          padding: var(--spacing-md) var(--spacing-xl);
          border: none;
          border-radius: var(--radius-lg);
          font-size: 1rem;
          font-weight: 600;
          text-decoration: none;
          cursor: pointer;
          transition: all var(--transition-normal);
          position: relative;
          overflow: hidden;
          min-width: 140px;
          box-shadow: var(--shadow-md);
        }

        .btn::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
          transition: left var(--transition-slow);
        }

        .btn:hover::before {
          left: 100%;
        }

        .btn-warning {
          background: linear-gradient(135deg, var(--warning-color), #f97316);
          color: white;
        }

        .btn-warning:hover {
          background: linear-gradient(135deg, #f97316, var(--warning-color));
          transform: translateY(-2px);
          box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
          background: linear-gradient(135deg, var(--surface-secondary), var(--surface-tertiary));
          color: var(--text-primary);
          border: 1px solid var(--border-light);
        }

        .btn-secondary:hover {
          background: linear-gradient(135deg, var(--surface-tertiary), var(--surface-secondary));
          transform: translateY(-2px);
          box-shadow: var(--shadow-lg);
        }

        .btn:active {
          transform: translateY(0);
        }

        .btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
          transform: none;
        }

        .btn:disabled:hover {
          transform: none;
          box-shadow: var(--shadow-md);
        }

        /* Form Actions */
        .form-actions {
          display: flex;
          gap: var(--spacing-md);
          justify-content: flex-start;
          flex-wrap: wrap;
          margin-top: var(--spacing-lg);
          padding-top: var(--spacing-lg);
          border-top: 1px solid var(--border-color);
        }

        /* Textarea */
        textarea.form-control {
          resize: vertical;
          min-height: 100px;
          font-family: inherit;
        }

        /* Select Dropdown */
        select.form-control {
          background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
          background-position: right 12px center;
          background-repeat: no-repeat;
          background-size: 16px;
          padding-right: 40px;
          cursor: pointer;
        }

        select.form-control option {
          background: var(--surface-secondary);
          color: var(--text-primary);
          padding: var(--spacing-sm);
        }

        /* Input Number */
        input[type="number"].form-control::-webkit-outer-spin-button,
        input[type="number"].form-control::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
        }

        input[type="number"].form-control {
          -moz-appearance: textfield;
        }

        /* Date Input */
        input[type="date"].form-control {
          position: relative;
        }

        input[type="date"].form-control::-webkit-calendar-picker-indicator {
          background: transparent;
          bottom: 0;
          color: transparent;
          cursor: pointer;
          height: auto;
          left: 0;
          position: absolute;
          right: 0;
          top: 0;
          width: auto;
        }

        /* Loading Animation */
        .loading {
          display: inline-block;
          width: 20px;
          height: 20px;
          border: 2px solid rgba(255, 255, 255, 0.3);
          border-radius: 50%;
          border-top-color: var(--primary-color);
          animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        /* Currency Input */
        .currency-input {
          position: relative;
        }

        .currency-input::before {
          content: 'Rp';
          position: absolute;
          left: 15px;
          top: 50%;
          transform: translateY(-50%);
          color: var(--text-muted);
          font-weight: 500;
          z-index: 1;
          pointer-events: none;
        }

        .currency-input input {
          padding-left: 45px;
        }

        /* Form Validation */
        .form-control:invalid {
          border-color: var(--error-color);
        }

        .form-control:valid {
          border-color: var(--success-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
          .container {
            padding: var(--spacing-lg) var(--spacing-md);
          }
          
          .investments-section {
            padding: var(--spacing-xl);
          }
          
          .section-title {
            font-size: 1.75rem;
            flex-direction: column;
            text-align: center;
          }
          
          .profit-types {
            grid-template-columns: repeat(2, 1fr);
          }
          
          .form-actions {
            flex-direction: column;
          }
          
          .btn {
            width: 100%;
          }
        }

        @media (max-width: 480px) {
          .profit-types {
            grid-template-columns: 1fr;
          }
          
          .section-title {
            font-size: 1.5rem;
          }
          
          .investments-section {
            padding: var(--spacing-lg);
          }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
          * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
          }
        }

        /* Focus visible for better accessibility */
        .btn:focus-visible,
        .form-control:focus-visible,
        .profit-type:focus-visible {
          outline: 2px solid var(--primary-color);
          outline-offset: 2px;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
          width: 8px;
        }

        ::-webkit-scrollbar-track {
          background: var(--background-secondary);
        }

        ::-webkit-scrollbar-thumb {
          background: var(--surface-tertiary);
          border-radius: var(--radius-md);
        }

        ::-webkit-scrollbar-thumb:hover {
          background: var(--border-light);
        }

        /* Success message auto hide */
        .alert.success {
          animation: slideInDown 0.3s ease-out, fadeOut 0.3s ease-out 3s forwards;
        }

        @keyframes fadeOut {
          to {
            opacity: 0;
            transform: translateY(-20px);
          }
        }

        /* Ripple Animation */
        @keyframes ripple {
          to {
            transform: scale(2);
            opacity: 0;
          }
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="investments-section">
            <div class="section-header">
                <div class="header-content">
                    <h2 class="section-title">
                        <i class="fas fa-edit"></i> Edit Keuntungan Investasi
                    </h2>
                    <p class="section-subtitle">Perbarui informasi keuntungan Anda dengan mudah</p>
                </div>
            </div>

            <!-- Current Data -->
            <div class="current-data">
                <strong>📊 Data Saat Ini:</strong><br>
                <strong>💼 Investasi:</strong> <?= htmlspecialchars($keuntungan['judul_investasi']) ?><br>
                <strong>🏷️ Kategori:</strong> <?= htmlspecialchars($keuntungan['nama_kategori']) ?><br>
                <strong>📈 Keuntungan:</strong> <?= htmlspecialchars($keuntungan['judul_keuntungan']) ?><br>
                <strong>💰 Jumlah:</strong> Rp <?= number_format($keuntungan['jumlah_keuntungan'], 2, ',', '.') ?><br>
                <strong>📅 Tanggal:</strong> <?= date('d/m/Y', strtotime($keuntungan['tanggal_keuntungan'])) ?>
            </div>

            <!-- Success Alert -->
            <?php if ($success): ?>
                <div class="alert success" id="successAlert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="alert error" id="errorAlert">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-container" id="editForm" novalidate>
                <!-- Investasi -->
                <div class="form-group">
                    <label for="investasi_id"><i class="fas fa-briefcase"></i> Pilih Investasi *</label>
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
                    <strong>🏷️ Kategori:</strong> <span id="selectedCategory"><?= htmlspecialchars($keuntungan['nama_kategori']) ?></span>
                    <input type="hidden" name="kategori_id" id="kategori_id" value="<?= $keuntungan['kategori_id'] ?>">
                </div>

                <!-- Judul Keuntungan -->
                <div class="form-group">
                    <label for="judul_keuntungan"><i class="fas fa-tag"></i> Judul Keuntungan *</label>
                    <input type="text" name="judul_keuntungan" id="judul_keuntungan"
                           class="form-control" placeholder="Contoh: Dividen Q1 2024"
                           value="<?= htmlspecialchars($keuntungan['judul_keuntungan']) ?>" required>
                </div>

                <!-- Jumlah & Persentase -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_keuntungan"><i class="fas fa-money-bill-wave"></i> Jumlah Keuntungan *</label>
                        <div class="currency-input">
                            <input type="text" name="jumlah_keuntungan" id="jumlah_keuntungan"
                                   class="form-control" placeholder="0,00"
                                   value="<?= number_format($keuntungan['jumlah_keuntungan'], 2, ',', '.') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="persentase_keuntungan"><i class="fas fa-percentage"></i> Persentase (%)</label>
                        <input type="number" name="persentase_keuntungan" id="persentase_keuntungan"
                               class="form-control" step="0.01" min="0" max="1000"
                               placeholder="Opsional"
                               value="<?= $keuntungan['persentase_keuntungan'] ? number_format($keuntungan['persentase_keuntungan'] * 100, 6) : '' ?>">
                    </div>
                </div>

                <!-- Tanggal -->
                <div class="form-group">
                    <label for="tanggal_keuntungan"><i class="fas fa-calendar-alt"></i> Tanggal Keuntungan *</label>
                    <input type="date" name="tanggal_keuntungan" id="tanggal_keuntungan"
                           class="form-control" value="<?= $keuntungan['tanggal_keuntungan'] ?>" required>
                </div>

                <!-- Sumber Keuntungan -->
                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Sumber Keuntungan *</label>
                    <div class="profit-types">
                        <?php 
                        $sources = ['dividen', 'capital_gain', 'bunga', 'bonus', 'lainnya'];
                        $source_labels = [
                            'dividen' => 'Dividen',
                            'capital_gain' => 'Capital Gain',
                            'bunga' => 'Bunga',
                            'bonus' => 'Bonus',
                            'lainnya' => 'Lainnya'
                        ];
                        $ikon_map = [
                            'dividen' => 'fa-coins',
                            'capital_gain' => 'fa-chart-line',
                            'bunga' => 'fa-percent',
                            'bonus' => 'fa-gift',
                            'lainnya' => 'fa-ellipsis-h'
                        ];
                        ?>
                        <?php foreach ($sources as $src): ?>
                            <div class="profit-type" onclick="selectProfitType(this, '<?= $src ?>')">
                                <input type="radio" name="sumber_keuntungan" value="<?= $src ?>"
                                       <?= $keuntungan['sumber_keuntungan'] == $src ? 'checked' : '' ?> required>
                                <i class="fas <?= $ikon_map[$src] ?>"></i><br>
                                <?= $source_labels[$src] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="status"><i class="fas fa-flag"></i> Status *</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="realized" <?= $keuntungan['status'] === 'realized' ? 'selected' : '' ?>>
                                ✅ Sudah Direalisasi
                            </option>
                            <option value="unrealized" <?= $keuntungan['status'] === 'unrealized' ? 'selected' : '' ?>>
                                ⏳ Belum Direalisasi
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-file-alt"></i> Deskripsi Tambahan</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"
                              placeholder="Tambahkan catatan atau deskripsi detail tentang keuntungan ini..."><?= htmlspecialchars($keuntungan['deskripsi']) ?></textarea>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-warning" id="submitBtn">
                        <i class="fas fa-save"></i> 
                        <span>Update Keuntungan</span>
                        <div class="loading" id="loadingSpinner" style="display: none;"></div>
                    </button>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
        // Global variables
        let isSubmitting = false;
        const form = document.getElementById('editForm');
        const submitBtn = document.getElementById('submitBtn');
        const loadingSpinner = document.getElementById('loadingSpinner');

        // Fungsi formatting currency yang disederhanakan
        function formatCurrencySimple(value) {
            // Hanya bersihkan karakter yang tidak diinginkan, biarkan user mengetik bebas
            return value.replace(/[^\d\,\.]/g, '');
        }

        // Fungsi untuk validasi dan format akhir
        function formatCurrencyFinal(value) {
            if (!value) return '';
            
            // Hapus karakter non-digit kecuali koma dan titik
            value = value.replace(/[^\d\,\.]/g, '');
            
            // Jika value kosong atau hanya separator, return as is untuk memungkinkan user mengetik
            if (!value || value === ',' || value === '.') return value;
            
            // Cari posisi koma dan titik terakhir
            const lastComma = value.lastIndexOf(',');
            const lastDot = value.lastIndexOf('.');
            
            let numValue = 0;
            
            try {
                if (lastComma === -1 && lastDot === -1) {
                    // Tidak ada separator desimal
                    numValue = parseFloat(value) || 0;
                } else if (lastComma > lastDot) {
                    // Koma adalah separator desimal (format Indonesia)
                    const integerPart = value.substring(0, lastComma).replace(/[\.\,]/g, '');
                    const decimalPart = value.substring(lastComma + 1);
                    numValue = parseFloat(integerPart + '.' + decimalPart) || 0;
                } else if (lastDot > lastComma) {
                    // Titik adalah separator desimal (format internasional)
                    const integerPart = value.substring(0, lastDot).replace(/[\.\,]/g, '');
                    const decimalPart = value.substring(lastDot + 1);
                    numValue = parseFloat(integerPart + '.' + decimalPart) || 0;
                } else if (lastComma !== -1) {
                    // Hanya ada koma - biarkan user selesai mengetik
                    return value;
                } else {
                    // Hanya ada titik - biarkan user selesai mengetik  
                    return value;
                }
            } catch(e) {
                return value; // Return original jika ada error
            }
            
            return value; // Return input langsung, tidak di-format ulang
        }

        let isFormatting = false;

        // Event listener yang lebih sederhana
        document.getElementById('jumlah_keuntungan').addEventListener('input', function(e) {
            if (isFormatting) return;
            
            isFormatting = true;
            const cursorPosition = e.target.selectionStart;
            const originalValue = e.target.value;
            
            // Hanya bersihkan karakter yang tidak diinginkan
            const cleanedValue = formatCurrencySimple(originalValue);
            
            // Update nilai hanya jika berbeda
            if (cleanedValue !== originalValue) {
                e.target.value = cleanedValue;
                // Pertahankan posisi kursor
                const newPosition = cursorPosition - (originalValue.length - cleanedValue.length);
                e.target.setSelectionRange(Math.max(0, newPosition), Math.max(0, newPosition));
            }
            
            setTimeout(() => {
                isFormatting = false;
            }, 10);
        });

        // Handle paste event
        document.getElementById('jumlah_keuntungan').addEventListener('paste', function(e) {
            setTimeout(() => {
                const cleanedValue = formatCurrencySimple(e.target.value);
                e.target.value = cleanedValue;
            }, 10);
        });

        // Format saat focus hilang (blur)
        document.getElementById('jumlah_keuntungan').addEventListener('blur', function(e) {
            const value = e.target.value;
            if (value) {
                // Hanya format display saat blur, bukan saat mengetik
                const formatted = formatCurrencyFinal(value);
                e.target.value = formatted;
            }
        });

        // Fungsi untuk mengonversi nilai yang diformat kembali ke angka untuk form submission
        function parseFormattedCurrency(value) {
            if (!value) return 0;
            
            // Hapus semua karakter kecuali angka dan koma
            value = value.replace(/[^\d\,]/g, '');
            
            // Ganti koma dengan titik untuk parsing
            return parseFloat(value.replace(',', '.')) || 0;
        }

        // Investment selection handler
        document.getElementById('investasi_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const categorySpan = document.getElementById('selectedCategory');
            const categoryInput = document.getElementById('kategori_id');
            const investmentInfo = document.getElementById('investmentInfo');
            
            if (selected.value) {
                categorySpan.textContent = selected.dataset.namaKategori;
                categoryInput.value = selected.dataset.kategori;
                investmentInfo.classList.add('show');
                
                // Add animation effect
                investmentInfo.style.transform = 'translateY(-10px)';
                investmentInfo.style.opacity = '0';
                setTimeout(() => {
                    investmentInfo.style.transform = 'translateY(0)';
                    investmentInfo.style.opacity = '1';
                }, 100);
            } else {
                investmentInfo.classList.remove('show');
                categoryInput.value = '';
            }
        });

        // Profit type selection handler
        function selectProfitType(element, value) {
            // Remove selected class from all profit types
            document.querySelectorAll('.profit-type').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Add selected class to clicked element
            element.classList.add('selected');
            
            // Check the radio button
            element.querySelector('input[type="radio"]').checked = true;
            
            // Add ripple effect
            const ripple = document.createElement('div');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(102, 126, 234, 0.3)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.left = '50%';
            ripple.style.top = '50%';
            ripple.style.width = '100px';
            ripple.style.height = '100px';
            ripple.style.marginLeft = '-50px';
            ripple.style.marginTop = '-50px';
            
            element.style.position = 'relative';
            element.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }

        // Form validation
        function validateForm() {
            const investasiId = document.getElementById('investasi_id').value;
            const judulKeuntungan = document.getElementById('judul_keuntungan').value.trim();
            const jumlahKeuntungan = document.getElementById('jumlah_keuntungan').value.trim();
            const tanggalKeuntungan = document.getElementById('tanggal_keuntungan').value;
            const sumberKeuntungan = document.querySelector('input[name="sumber_keuntungan"]:checked');
            const status = document.getElementById('status').value;

            let errors = [];

            if (!investasiId) errors.push('Pilih investasi');
            if (!judulKeuntungan) errors.push('Isi judul keuntungan');
            if (!jumlahKeuntungan || jumlahKeuntungan === '0,00' || parseFormattedCurrency(jumlahKeuntungan) <= 0) {
                errors.push('Isi jumlah keuntungan dengan benar');
            }
            if (!tanggalKeuntungan) errors.push('Pilih tanggal keuntungan');
            if (!sumberKeuntungan) errors.push('Pilih sumber keuntungan');
            if (!status) errors.push('Pilih status');

            return errors;
        }

        // Form submission handler
        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            const errors = validateForm();
            if (errors.length > 0) {
                e.preventDefault();
                showAlert('error', 'Harap lengkapi field berikut: ' + errors.join(', '));
                return;
            }

            // Convert formatted currency back to proper decimal format for server
            const jumlahInput = document.getElementById('jumlah_keuntungan');
            const rawValue = parseFormattedCurrency(jumlahInput.value);
            
            // Create hidden input with proper decimal format for server
            let hiddenInput = document.querySelector('input[name="jumlah_keuntungan_raw"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'jumlah_keuntungan_raw';
                form.appendChild(hiddenInput);
            }
            hiddenInput.value = rawValue.toString();

            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.querySelector('span').textContent = 'Menyimpan...';
            loadingSpinner.style.display = 'inline-block';

            // Re-enable after 5 seconds as failsafe
            setTimeout(() => {
                if (isSubmitting) {
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    submitBtn.querySelector('span').textContent = 'Update Keuntungan';
                    loadingSpinner.style.display = 'none';
                }
            }, 5000);
        });

        // Reset form function
        function resetForm() {
            if (confirm('Apakah Anda yakin ingin mereset form? Semua perubahan akan hilang.')) {
                // Reset to original values
                form.reset();
                
                // Reset profit type selections
                document.querySelectorAll('.profit-type').forEach(el => {
                    el.classList.remove('selected');
                });
                
                // Re-select original profit type
                const originalSource = '<?= $keuntungan["sumber_keuntungan"] ?>';
                if (originalSource) {
                    const originalElement = document.querySelector(`input[name="sumber_keuntungan"][value="${originalSource}"]`);
                    if (originalElement) {
                        originalElement.checked = true;
                        originalElement.closest('.profit-type').classList.add('selected');
                    }
                }
                
                // Reset investment info
                document.getElementById('selectedCategory').textContent = '<?= htmlspecialchars($keuntungan["nama_kategori"]) ?>';
                document.getElementById('kategori_id').value = '<?= $keuntungan["kategori_id"] ?>';
                document.getElementById('investmentInfo').classList.add('show');
                
                // Reset currency field to original format
                document.getElementById('jumlah_keuntungan').value = '<?= number_format($keuntungan["jumlah_keuntungan"], 2, ",", ".") ?>';
                
                showAlert('info', 'Form telah direset ke nilai semula');
            }
        }

        // Show alert function
        function showAlert(type, message) {
            const existingAlert = document.querySelector('.alert:not([id])');
            if (existingAlert) {
                existingAlert.remove();
            }

            const alert = document.createElement('div');
            alert.className = `alert ${type}`;
            alert.innerHTML = `
                <i class="fas ${type === 'error' ? 'fa-exclamation-triangle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
                ${message}
            `;
            
            const form = document.querySelector('.form-container');
            form.parentNode.insertBefore(alert, form);

            // Auto remove after 5 seconds
            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            }
        }

        // Auto-hide success/error alerts
        function autoHideAlerts() {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    successAlert.style.transform = 'translateY(-20px)';
                    setTimeout(() => successAlert.remove(), 300);
                }, 5000);
            }
            
            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.style.opacity = '0';
                    errorAlert.style.transform = 'translateY(-20px)';
                    setTimeout(() => errorAlert.remove(), 300);
                }, 10000); // Error alerts stay longer
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize profit type selection
            const checkedProfitType = document.querySelector('input[name="sumber_keuntungan"]:checked');
            if (checkedProfitType) {
                checkedProfitType.closest('.profit-type').classList.add('selected');
            }

            // Auto-hide alerts
            autoHideAlerts();

            // Add smooth scrolling for form validation errors
            const firstError = document.querySelector('.form-control:invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Add enter key handler for form
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    if (!submitBtn.disabled) {
                        form.dispatchEvent(new Event('submit', { cancelable: true }));
                    }
                }
            });

            // Add unsaved changes warning
            let formChanged = false;
            const formElements = form.querySelectorAll('input, select, textarea');
            
            formElements.forEach(element => {
                element.addEventListener('change', () => {
                    formChanged = true;
                });
                
                element.addEventListener('input', () => {
                    formChanged = true;
                });
            });

            window.addEventListener('beforeunload', function(e) {
                if (formChanged && !isSubmitting) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Mark form as unchanged after successful submission
            form.addEventListener('submit', function() {
                formChanged = false;
            });
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+S to save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                if (!isSubmitting) {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
            
            // Escape to go back
            if (e.key === 'Escape') {
                const backBtn = document.querySelector('.btn-secondary[href="../dashboard.php"]');
                if (backBtn && !document.querySelector('.alert')) {
                    window.location.href = backBtn.href;
                }
            }
        });

        // Add tooltips for better UX
        const tooltips = {
            'investasi_id': 'Pilih investasi yang menghasilkan keuntungan ini',
            'judul_keuntungan': 'Berikan nama yang jelas untuk keuntungan ini',
            'jumlah_keuntungan': 'Masukkan jumlah dalam Rupiah (contoh: 0,32 atau 1.000,50)',
            'persentase_keuntungan': 'Opsional: persentase keuntungan dari modal',
            'tanggal_keuntungan': 'Tanggal ketika keuntungan diperoleh',
            'status': 'Apakah keuntungan sudah Anda terima atau belum',
            'deskripsi': 'Informasi tambahan tentang keuntungan ini'
        };

        Object.keys(tooltips).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.title = tooltips[id];
            }
        });

        console.log('✅ SAZEN Investment - Edit Keuntungan initialized successfully');
    </script>
</body>
</html>
