<?php
session_start();
require_once "config.php";

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: admin/auth.php");
    exit;
}

// Ambil data user
$username = $_SESSION['username'];

// Query untuk mengambil semua data investasi
$sql = "
    SELECT i.id, i.judul_investasi, i.deskripsi, i.jumlah, i.tanggal_investasi, k.nama_kategori
    FROM investasi i
    JOIN kategori k ON i.kategori_id = k.id
    ORDER BY i.tanggal_investasi DESC
";
$stmt = $koneksi->query($sql);
$investasi = $stmt->fetchAll();

// Query untuk statistik dengan keuntungan
$sql_stats = "
    SELECT 
        COUNT(DISTINCT i.id) as total_investasi,
        COALESCE(SUM(i.jumlah), 0) as total_investasi_nilai,
        COALESCE(SUM(ki.jumlah_keuntungan), 0) as total_keuntungan,
        (COALESCE(SUM(i.jumlah), 0) + COALESCE(SUM(ki.jumlah_keuntungan), 0)) as total_nilai,
        COUNT(DISTINCT i.kategori_id) as total_kategori,
        COUNT(DISTINCT ki.id) as total_keuntungan_records
    FROM investasi i
    LEFT JOIN keuntungan_investasi ki ON i.id = ki.investasi_id
";
$stmt_stats = $koneksi->query($sql_stats);
$stats = $stmt_stats->fetch();

// Query untuk investasi per kategori dengan keuntungan
$sql_kategori = "
    SELECT 
        k.id as kategori_id,
        k.nama_kategori, 
        COUNT(DISTINCT i.id) as jumlah, 
        COALESCE(SUM(i.jumlah), 0) as total_investasi,
        COALESCE(SUM(ki.jumlah_keuntungan), 0) as total_keuntungan,
        (COALESCE(SUM(i.jumlah), 0) + COALESCE(SUM(ki.jumlah_keuntungan), 0)) as total_nilai
    FROM kategori k
    LEFT JOIN investasi i ON k.id = i.kategori_id
    LEFT JOIN keuntungan_investasi ki ON i.id = ki.investasi_id
    GROUP BY k.id, k.nama_kategori
    HAVING jumlah > 0
    ORDER BY total_nilai DESC
";
$stmt_kategori = $koneksi->query($sql_kategori);
$kategori_stats = $stmt_kategori->fetchAll();

// Query untuk keuntungan terbaru
$sql_keuntungan = "
    SELECT 
        ki.id,
        ki.judul_keuntungan,
        ki.jumlah_keuntungan,
        ki.persentase_keuntungan,
        ki.tanggal_keuntungan,
        ki.sumber_keuntungan,
        ki.status,
        i.judul_investasi,
        kat.nama_kategori
    FROM keuntungan_investasi ki
    JOIN investasi i ON ki.investasi_id = i.id
    JOIN kategori kat ON ki.kategori_id = kat.id
    ORDER BY ki.tanggal_keuntungan DESC
    LIMIT 6
";
$stmt_keuntungan = $koneksi->query($sql_keuntungan);
$keuntungan_list = $stmt_keuntungan->fetchAll();

// Query untuk statistik keuntungan per sumber
$sql_sumber = "
    SELECT 
        sumber_keuntungan,
        COUNT(*) as jumlah,
        SUM(jumlah_keuntungan) as total
    FROM keuntungan_investasi
    GROUP BY sumber_keuntungan
    ORDER BY total DESC
";
$stmt_sumber = $koneksi->query($sql_sumber);
$sumber_stats = $stmt_sumber->fetchAll();

// Proses logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: admin/auth.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - SAZEN Investment</title>
    <link rel="stylesheet" href="assets/css/dashboard.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-container">
            <div class="loading-logo">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="loading-spinner"></div>
            <div class="loading-text">Memuat Dashboard...</div>
        </div>
    </div>

    <!-- Background Elements -->
    <div class="background-elements">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <!-- Success Toast -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div id="toast" class="toast success show">
            <div class="toast-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message"><?= isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Data berhasil disimpan' ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="header" role="banner">
        <div class="header-content container">
            <div class="header-left">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-chart-line"></i>
                        <div class="logo-pulse"></div>
                    </div>
                    <div class="logo-text">
                        <span class="brand-name">SAZEN</span>
                        <span class="brand-subtitle">Investment</span>
                    </div>
                </div>
                <div class="header-info">
                    <h1 class="page-title">
                        <span class="title-main">Dashboard</span>
                        <span class="title-accent">Investasi</span>
                    </h1>
                    <p class="welcome-text">
                        <i class="fas fa-user-circle"></i>
                        Selamat datang, <strong><?= htmlspecialchars($username) ?></strong>
                    </p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <form method="POST" class="logout-form">
                        <button type="submit" name="logout" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="header-wave">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
                <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
            </svg>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content container" role="main">
        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Ringkasan Portofolio
                </h2>
                <p class="section-subtitle">Gambaran umum investasi dan keuntungan Anda</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card card-primary" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon-container">
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-glow"></div>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Investasi</div>
                        <div class="stat-number" data-count="<?= $stats['total_investasi'] ?>"><?= $stats['total_investasi'] ?></div>
                        <div class="stat-sublabel">Portofolio Aktif</div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12%</span>
                    </div>
                </div>

                <div class="stat-card card-success" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon-container">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-glow"></div>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Nilai</div>
                        <div class="stat-number" data-amount="<?= $stats['total_nilai'] ?>">Rp <?= number_format($stats['total_nilai'], 0, ',', '.') ?></div>
                        <div class="stat-sublabel">Investasi + Keuntungan</div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+<?= $stats['total_investasi_nilai'] > 0 ? number_format(($stats['total_keuntungan']/$stats['total_investasi_nilai'])*100, 1) : 0 ?>%</span>
                    </div>
                </div>

                <div class="stat-card card-info" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon-container">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line-up"></i>
                        </div>
                        <div class="stat-glow"></div>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Keuntungan</div>
                        <div class="stat-number">Rp <?= number_format($stats['total_keuntungan'], 0, ',', '.') ?></div>
                        <div class="stat-sublabel"><?= $stats['total_keuntungan_records'] ?> Transaksi</div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8.5%</span>
                    </div>
                </div>

                <div class="stat-card card-warning" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-icon-container">
                        <div class="stat-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stat-glow"></div>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">ROI Rata-rata</div>
                        <div class="stat-number">
                            <?php if ($stats['total_investasi_nilai'] > 0): ?>
                                <?= number_format(($stats['total_keuntungan']/$stats['total_investasi_nilai'])*100, 1) ?>%
                            <?php else: ?>
                                0%
                            <?php endif; ?>
                        </div>
                        <div class="stat-sublabel">Return on Investment</div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-chart-line"></i>
                        <span>Profit</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profit Summary -->
        <?php if ($sumber_stats): ?>
            <section class="profit-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-money-bill-trend-up"></i>
                        Analisis Keuntungan
                    </h2>
                    <p class="section-subtitle">Breakdown keuntungan berdasarkan sumber</p>
                </div>
                
                <div class="profit-summary">
                    <?php foreach ($sumber_stats as $sumber): ?>
                        <div class="profit-item" data-aos="zoom-in" data-aos-delay="100">
                            <h4>
                                <i class="fas <?= $sumber['sumber_keuntungan'] == 'dividen' ? 'fa-coins' : 
                                                ($sumber['sumber_keuntungan'] == 'capital_gain' ? 'fa-chart-line' : 
                                                ($sumber['sumber_keuntungan'] == 'bunga' ? 'fa-percent' : 
                                                ($sumber['sumber_keuntungan'] == 'bonus' ? 'fa-gift' : 'fa-ellipsis-h'))) ?>"></i>
                                <?= ucfirst(str_replace('_', ' ', $sumber['sumber_keuntungan'])) ?>
                            </h4>
                            <div class="amount">Rp <?= number_format($sumber['total'], 0, ',', '.') ?></div>
                            <small><?= $sumber['jumlah'] ?> transaksi</small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Category Overview -->
        <?php if ($kategori_stats): ?>
            <section class="category-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-chart-pie"></i>
                        Analisis Kategori
                    </h2>
                    <p class="section-subtitle">Distribusi investasi dan keuntungan berdasarkan kategori</p>
                </div>
                
                <div class="category-grid">
                    <?php foreach ($kategori_stats as $index => $kat): ?>
                        <div class="category-card" data-aos="zoom-in" data-aos-delay="<?= ($index + 1) * 100 ?>">
                            <div class="category-header">
                                <div class="category-icon">
                                    <i class="fas <?= $index % 4 == 0 ? 'fa-chart-line' : ($index % 4 == 1 ? 'fa-coins' : ($index % 4 == 2 ? 'fa-gem' : 'fa-building')) ?>"></i>
                                </div>
                                <div class="category-info">
                                    <h3 class="category-name"><?= htmlspecialchars($kat['nama_kategori']) ?></h3>
                                    <div class="category-count">
                                        <i class="fas fa-cube"></i>
                                        <?= $kat['jumlah'] ?> investasi
                                    </div>
                                </div>
                            </div>
                            <div class="category-value">
                                <div class="value-amount">Rp <?= number_format($kat['total_nilai'], 0, ',', '.') ?></div>
                                <div class="value-breakdown">
                                    <small>
                                        Investasi: Rp <?= number_format($kat['total_investasi'], 0, ',', '.') ?><br>
                                        Keuntungan: Rp <?= number_format($kat['total_keuntungan'], 0, ',', '.') ?>
                                    </small>
                                </div>
                                <div class="value-percentage"><?= $stats['total_nilai'] > 0 ? number_format(($kat['total_nilai'] / $stats['total_nilai'] * 100), 1) : 0 ?>%</div>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar">
                                    <div class="progress-fill" data-width="<?= $stats['total_nilai'] > 0 ? ($kat['total_nilai'] / $stats['total_nilai'] * 100) : 0 ?>"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Recent Profits -->
        <?php if ($keuntungan_list): ?>
            <section class="recent-profits-section">
                <div class="section-header">
                    <div class="header-content">
                        <h2 class="section-title">
                            <i class="fas fa-chart-line-up"></i>
                            Keuntungan Terbaru
                        </h2>
                        <p class="section-subtitle">Catatan keuntungan investasi terkini</p>
                    </div>
                    <div class="header-actions">
                        <a href="admin/upload_keuntungan.php" class="add-btn">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Keuntungan</span>
                        </a>
                    </div>
                </div>

                <div class="profit-list">
                    <?php foreach ($keuntungan_list as $profit): ?>
                        <div class="keuntungan-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="keuntungan-header">
                                <div>
                                    <h4 class="keuntungan-title"><?= htmlspecialchars($profit['judul_keuntungan']) ?></h4>
                                    <p class="keuntungan-investment">
                                        <i class="fas fa-briefcase"></i>
                                        <?= htmlspecialchars($profit['judul_investasi']) ?>
                                        <span class="sumber-badge sumber-<?= $profit['sumber_keuntungan'] ?>">
                                            <?= ucfirst($profit['sumber_keuntungan']) ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="card-menu">
                                    <button class="menu-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="menu-dropdown">
                                        <a href="admin/edit_keuntungan.php?id=<?= $profit['id'] ?>" class="menu-item">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="admin/delete_keuntungan.php?id=<?= $profit['id'] ?>" class="menu-item delete" onclick="return confirm('Apakah Anda yakin ingin menghapus keuntungan ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="keuntungan-amount">
                                <i class="fas fa-money-bill-wave"></i>
                                Rp <?= number_format($profit['jumlah'], 0, ',', '.') ?>
                                <?php if ($profit['persentase_keuntungan']): ?>
                                    <small>(<?= $profit['persentase_keuntungan'] ?>%)</small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="keuntungan-details">
                                <div>
                                    <i class="fas fa-calendar"></i>
                                    <?= date("d M Y", strtotime($profit['tanggal_keuntungan'])) ?>
                                </div>
                                <span class="status-badge status-<?= $profit['status'] ?>">
                                    <?= $profit['status'] == 'realized' ? 'Direalisasi' : 'Belum Direalisasi' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Recent Investments -->
        <section class="investments-section">
            <div class="section-header">
                <div class="header-content">
                    <h2 class="section-title">
                        <i class="fas fa-briefcase"></i>
                        Manajemen Portofolio
                    </h2>
                    <p class="section-subtitle">Kelola dan pantau semua investasi Anda</p>
                </div>
                <div class="header-actions">
                    <a href="admin/upload.php" class="add-btn">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Investasi</span>
                    </a>
                </div>
            </div>

            <?php if ($investasi): ?>
                <div class="investments-grid">
                    <?php foreach ($investasi as $index => $item): ?>
                        <div class="investment-card" data-aos="fade-up" data-aos-delay="<?= ($index % 3 + 1) * 100 ?>">
                            <div class="card-glow"></div>
                            
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="fas fa-chart-area"></i>
                                </div>
                                <div class="card-menu">
                                    <button class="menu-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="menu-dropdown">
                                        <a href="admin/edit.php?id=<?= urlencode($item['id']) ?>" class="menu-item">
                                            <i class="fas fa-edit"></i>
                                            Edit
                                        </a>
                                        <a href="admin/delete.php?id=<?= urlencode($item['id']) ?>" class="menu-item delete" onclick="return confirm('Apakah Anda yakin ingin menghapus investasi ini?');">
                                            <i class="fas fa-trash"></i>
                                            Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <h3 class="card-title"><?= htmlspecialchars($item['judul_investasi']) ?></h3>
                                <div class="category-badge">
                                    <i class="fas fa-tag"></i>
                                    <?= htmlspecialchars($item['nama_kategori']) ?>
                                </div>

                                <div class="amount-section">
                                    <div class="amount-label">
                                        <i class="fas fa-money-bill-wave"></i>
                                        Nilai Investasi
                                    </div>
                                    <div class="amount-value">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></div>
                                    <div class="amount-trend positive">
                                        <i class="fas fa-trending-up"></i>
                                        <span>+4.2%</span>
                                    </div>
                                </div>

                                <div class="date-section">
                                    <div class="date-info">
                                        <i class="fas fa-calendar-alt"></i>
                                        <div class="date-content">
                                            <span class="date-label">Tanggal Investasi</span>
                                            <span class="date-value"><?= date("d M Y", strtotime($item['tanggal_investasi'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="time-badge">
                                        <?php 
                                        $days = (time() - strtotime($item['tanggal_investasi'])) / (60 * 60 * 24);
                                        $status = $days < 30 ? 'new' : ($days < 365 ? 'active' : 'mature');
                                        $statusText = $days < 30 ? 'Baru' : ($days < 365 ? 'Aktif' : 'Matang');
                                        ?>
                                        <div class="status-indicator <?= $status ?>"></div>
                                        <?= $statusText ?>
                                    </div>
                                </div>

                                <?php if (!empty($item['deskripsi'])): ?>
                                    <div class="description-section">
                                        <div class="description-header">
                                            <i class="fas fa-info-circle"></i>
                                            <span>Deskripsi</span>
                                        </div>
                                        <p class="description-text"><?= nl2br(htmlspecialchars($item['deskripsi'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <div class="performance-metrics">
                                    <div class="metric">
                                        <i class="fas fa-chart-line"></i>
                                        <span>ROI: +15.2%</span>
                                    </div>
                                    <div class="metric">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Risk: Low</span>
                                    </div>
                                </div>
                                <div class="investment-id">#INV-<?= str_pad($item['id'], 4, '0', STR_PAD_LEFT) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" data-aos="fade-up">
                    <div class="empty-animation">
                        <div class="empty-icon">
                            <i class="fas fa-chart-line"></i>
                            <div class="empty-pulse"></div>
                        </div>
                    </div>
                    <h3 class="empty-title">Belum Ada Investasi</h3>
                    <p class="empty-description">Mulai perjalanan investasi Anda dengan menambahkan investasi pertama</p>
                    <a href="admin/upload.php" class="empty-btn">
                        <i class="fas fa-rocket"></i>
                        <span>Tambah Investasi Pertama</span>
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Quick Analytics -->
        <section class="analytics-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-analytics"></i>
                    Analytics Dashboard
                </h2>
                <p class="section-subtitle">Insight mendalam tentang performa investasi</p>
            </div>
            
            <div class="analytics-grid">
                <div class="analytics-card" data-aos="slide-up" data-aos-delay="100">
                    <div class="analytics-header">
                        <h4>Performance YTD</h4>
                        <div class="analytics-value positive">
                            +<?= $stats['total_investasi_nilai'] > 0 ? number_format(($stats['total_keuntungan']/$stats['total_investasi_nilai'])*100, 1) : 0 ?>%
                        </div>
                    </div>
                    <div class="mini-chart">
                        <div class="chart-bar" style="height: 60%"></div>
                        <div class="chart-bar" style="height: 80%"></div>
                        <div class="chart-bar" style="height: 45%"></div>
                        <div class="chart-bar" style="height: 90%"></div>
                        <div class="chart-bar" style="height: 70%"></div>
                    </div>
                </div>

                <div class="analytics-card" data-aos="slide-up" data-aos-delay="200">
                    <div class="analytics-header">
                        <h4>Risk Score</h4>
                        <div class="analytics-value neutral">Medium</div>
                    </div>
                    <div class="risk-meter">
                        <div class="meter-fill" data-level="65"></div>
                        <span class="meter-label">65/100</span>
                    </div>
                </div>

                <div class="analytics-card" data-aos="slide-up" data-aos-delay="300">
                    <div class="analytics-header">
                        <h4>Profit Ratio</h4>
                        <div class="analytics-value info">
                            <?= $stats['total_nilai'] > 0 ? number_format(($stats['total_keuntungan']/$stats['total_nilai'])*100, 1) : 0 ?>%
                        </div>
                    </div>
                    <div class="countdown">
                        <div class="countdown-item">
                            <span class="countdown-number"><?= $stats['total_keuntungan_records'] ?></span>
                            <span class="countdown-label">Transaksi</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="footer-content container">
            <div class="footer-left">
                <div class="footer-logo">
                    <i class="fas fa-chart-line"></i>
                    <span>SAZEN Investment Manager version 2.0</span>
                </div>
                <p class="footer-text">&copy; <?= date("Y") ?> SAZEN. All rights reserved.</p>
            </div>
            <div class="footer-links">
                <a href="index.php" class="footer-link">
                    <i class="fas fa-home"></i>
                    Portfolio Public
                </a>
                <a href="admin/upload.php" class="footer-link">
                    <i class="fas fa-plus"></i>
                    Tambah Investasi
                </a>
                <a href="admin/upload_keuntungan.php" class="footer-link">
                    <i class="fas fa-chart-line-up"></i>
                    Tambah Keuntungan
                </a>
            </div>
        </div>
    </footer>

    <!-- Quick Actions FAB -->
    <div class="quick-actions">
        <a href="admin/upload_keuntungan.php" class="fab fab-success" title="Tambah Keuntungan">
            <i class="fas fa-chart-line-up"></i>
        </a>
        <a href="admin/upload.php" class="fab fab-primary" title="Tambah Investasi">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
        // Loading Screen Animation
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loadingScreen').classList.add('hide');
            }, 1500);
        });

        // Auto-hide toast
        function closeToast() {
            document.getElementById('toast').classList.remove('show');
        }

        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.remove('show');
            }
        }, 4000);

        // Counter Animation
        function animateCounters() {
            const counters = document.querySelectorAll('[data-count]');
            counters.forEach(counter => {
                const target = parseInt(counter.dataset.count);
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = Math.floor(current);
                }, 16);
            });
        }

        // Progress Bar Animation
        function animateProgressBars() {
            const bars = document.querySelectorAll('.progress-fill');
            bars.forEach(bar => {
                const width = bar.dataset.width;
                setTimeout(() => {
                    bar.style.width = width + '%';
                }, 500);
            });
        }

        // Card Menu Toggle
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.nextElementSibling;
                
                // Close all other dropdowns
                document.querySelectorAll('.menu-dropdown').forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('show');
                    }
                });
                
                dropdown.classList.toggle('show');
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.menu-dropdown').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        });

        // Scroll to Top Button
        const scrollBtn = document.getElementById('scrollToTop');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollBtn.classList.add('show');
            } else {
                scrollBtn.classList.remove('show');
            }
        });

        scrollBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Initialize animations when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                animateCounters();
                animateProgressBars();
            }, 800);
        });

        // Simple AOS (Animate On Scroll) implementation
        function observeElements() {
            const elements = document.querySelectorAll('[data-aos]');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('aos-animate');
                    }
                });
            }, { threshold: 0.1 });

            elements.forEach(el => observer.observe(el));
        }

        observeElements();
    </script>
</body>
</html>
