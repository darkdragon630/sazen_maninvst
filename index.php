<?php 
require_once 'config.php';

$sql = "
    SELECT i.id, i.judul_investasi, i.deskripsi, i.jumlah, i.tanggal_investasi, k.nama_kategori
    FROM investasi i
    JOIN kategori k ON i.kategori_id = k.id
    ORDER BY i.tanggal_investasi DESC
";

$stmt = $koneksi->query($sql);
$investasi = $stmt->fetchAll();

//hitung total investasi
$total_investasi = 0;
foreach ($investasi as $item) {
    $total_investasi += $item['jumlah'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Portofolio Investasi - SAAZ</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Loading Animation -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <div class="loading-text">Memuat Portofolio...</div>
        </div>
    </div>

    <!-- Background Particles -->
    <div class="particles-container" id="particlesContainer"></div>

    <!-- Header -->
    <header class="header">
        <div class="header-background">
            <div class="gradient-orb orb-1"></div>
            <div class="gradient-orb orb-2"></div>
            <div class="gradient-orb orb-3"></div>
        </div>
        <div class="header-content">
            <div class="header-icon-container">
                <i class="fas fa-chart-line header-icon"></i>
                <div class="icon-pulse"></div>
            </div>
            <h1 class="header-title">
                <span class="title-text">Portofolio Investasi</span>
                <span class="title-highlight">SAAZ</span>
            </h1>
            <p class="header-subtitle">
                <i class="fas fa-sync-alt subtitle-icon"></i>
                Data portofolio diperbarui otomatis dari dashboard admin
            </p>
            
            <!-- Stats Cards in Header -->
            <div class="header-stats">
                <div class="stat-card total-investment">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Investasi</div>
                        <div class="stat-value" data-value="<?= $total_investasi ?>">
                            Rp <?= number_format($total_investasi, 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card portfolio-count">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Jumlah Investasi</div>
                        <div class="stat-value"><?= count($investasi); ?> Item</div>
                    </div>
                </div>
                
                <div class="stat-card performance">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Status</div>
                        <div class="stat-value status-active">
                            <i class="fas fa-circle status-indicator"></i>
                            Aktif
                        </div>
                    </div>
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
    <main class="main-content">
        <!-- Filter & Sort Section -->
        <section class="portfolio-controls">
            <div class="controls-header">
                <h2 class="controls-title">
                    <i class="fas fa-filter"></i>
                    Manajemen Portofolio
                </h2>
                <div class="view-toggle">
                    <button class="toggle-btn active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="toggle-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <div class="controls-filters">
                <div class="filter-group">
                    <label for="sortSelect">Urutkan:</label>
                    <select id="sortSelect" class="filter-select">
                        <option value="date-desc">Terbaru</option>
                        <option value="date-asc">Terlama</option>
                        <option value="amount-desc">Nilai Tertinggi</option>
                        <option value="amount-asc">Nilai Terendah</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="categoryFilter">Kategori:</label>
                    <select id="categoryFilter" class="filter-select">
                        <option value="all">Semua Kategori</option>
                        <?php 
                        $categories = array_unique(array_column($investasi, 'nama_kategori'));
                        foreach($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Cari investasi...">
                </div>
            </div>
        </section>

        <?php if ($investasi): ?>
            <section class="investments-grid" id="investmentsGrid">
                <?php foreach ($investasi as $index => $item): ?>
                    <div class="investment-card" 
                         data-category="<?= htmlspecialchars($item['nama_kategori']) ?>"
                         data-amount="<?= $item['jumlah'] ?>"
                         data-date="<?= $item['tanggal_investasi'] ?>"
                         data-title="<?= htmlspecialchars($item['judul_investasi']) ?>"
                         style="--animation-delay: <?= $index * 0.1 ?>s">
                        
                        <div class="card-glow"></div>
                        
                        <div class="card-header">
                            <div class="card-header-content">
                                <h2 class="card-title">
                                    <i class="fas fa-chart-pie card-title-icon"></i>
                                    <?= htmlspecialchars($item['judul_investasi']); ?>
                                </h2>
                                <div class="category-badge">
                                    <i class="fas fa-tag"></i>
                                    <?= htmlspecialchars($item['nama_kategori']); ?>
                                </div>
                            </div>
                            <div class="card-menu">
                                <button class="menu-btn">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="amount-section">
                                <div class="amount-header">
                                    <span class="amount-label">
                                        <i class="fas fa-coins"></i>
                                        Nilai Investasi
                                    </span>
                                    <div class="amount-trend positive">
                                        <i class="fas fa-arrow-up"></i>
                                        <span>+2.5%</span>
                                    </div>
                                </div>
                                <div class="amount-value-container">
                                    <span class="amount-value" data-amount="<?= $item['jumlah'] ?>">
                                        Rp <?= number_format($item['jumlah'], 0, ',', '.'); ?>
                                    </span>
                                    <div class="amount-progress">
                                        <div class="progress-bar" style="width: <?= min(($item['jumlah'] / $total_investasi) * 100, 100) ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="date-section">
                                <div class="date-info">
                                    <i class="fas fa-calendar-alt date-icon"></i>
                                    <div class="date-content">
                                        <span class="date-label">Tanggal Investasi</span>
                                        <span class="date-value"><?= date("d M Y", strtotime($item['tanggal_investasi'])); ?></span>
                                    </div>
                                </div>
                                <div class="time-badge">
                                    <?php 
                                    $days = (time() - strtotime($item['tanggal_investasi'])) / (60 * 60 * 24);
                                    echo $days < 30 ? 'Baru' : ($days < 365 ? 'Aktif' : 'Matang');
                                    ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($item['deskripsi'])): ?>
                                <div class="description-section">
                                    <div class="description-header">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Deskripsi</span>
                                    </div>
                                    <p class="description-text"><?= nl2br(htmlspecialchars($item['deskripsi'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-actions">
                                <button class="action-btn primary">
                                    <i class="fas fa-eye"></i>
                                    Detail
                                </button>
                                <button class="action-btn secondary">
                                    <i class="fas fa-share-alt"></i>
                                    Bagikan
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <div class="performance-indicator">
                                <div class="indicator-dot positive"></div>
                                <span class="performance-text">Performa Baik</span>
                            </div>
                            <div class="investment-id">#INV-<?= str_pad($item['id'], 4, '0', STR_PAD_LEFT) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-animation">
                    <div class="empty-icon">
                        <i class="fas fa-chart-line-up"></i>
                        <div class="icon-pulse-empty"></div>
                    </div>
                </div>
                <h3 class="empty-title">Belum Ada Data Investasi</h3>
                <p class="empty-description">Data investasi akan muncul di sini setelah ditambahkan melalui dashboard admin</p>
                <button class="empty-action-btn">
                    <i class="fas fa-plus"></i>
                    Tambah Investasi
                </button>
            </div>
        <?php endif; ?>
        
        <!-- Quick Stats Summary -->
        <section class="quick-stats">
            <div class="stats-container">
                <div class="quick-stat-item">
                    <i class="fas fa-trending-up stat-icon"></i>
                    <div class="stat-info">
                        <div class="stat-number">+15.2%</div>
                        <div class="stat-desc">Pertumbuhan YTD</div>
                    </div>
                </div>
                
                <div class="quick-stat-item">
                    <i class="fas fa-shield-alt stat-icon"></i>
                    <div class="stat-info">
                        <div class="stat-number">92%</div>
                        <div class="stat-desc">Risk Score</div>
                    </div>
                </div>
                
                <div class="quick-stat-item">
                    <i class="fas fa-clock stat-icon"></i>
                    <div class="stat-info">
                        <div class="stat-number"><?= count($investasi) ?></div>
                        <div class="stat-desc">Active Positions</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-info">
                <div class="footer-logo">
                    <i class="fas fa-chart-line"></i>
                    <span>SAAZ</span>
                </div>
                <p class="footer-text">&copy; <?= date("Y"); ?> SAZEN Manajer Investasi version 1. Semua hak dilindungi.</p>
            </div>
            <div class="footer-links">
                <a href="#" class="footer-link">
                    <i class="fas fa-shield-alt"></i>
                    Keamanan
                </a>
                <a href="#" class="footer-link">
                    <i class="fas fa-headset"></i>
                    Dukungan
                </a>
                <a href="#" class="footer-link">
                    <i class="fas fa-chart-bar"></i>
                    Laporan
                </a>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Loading Animation
        window.addEventListener('load', function() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            setTimeout(() => {
                loadingOverlay.classList.add('fade-out');
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 500);
            }, 1500);
        });

        // Particle Animation
        function createParticles() {
            const container = document.getElementById('particlesContainer');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + 'vw';
                particle.style.animationDelay = Math.random() * 10 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 2) + 's';
                container.appendChild(particle);
            }
        }
        
        createParticles();

        // Investment Card Interactions
        document.querySelectorAll('.investment-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.querySelector('.card-glow').style.opacity = '1';
            });
            
            card.addEventListener('mouseleave', function() {
                this.querySelector('.card-glow').style.opacity = '0';
            });
        });

        // Counter Animation for Values
        function animateCounters() {
            document.querySelectorAll('[data-value]').forEach(counter => {
                const target = parseInt(counter.dataset.value);
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = 'Rp ' + Math.floor(current).toLocaleString('id-ID');
                }, 16);
            });
        }

        // Filter and Search Functionality
        document.getElementById('sortSelect').addEventListener('change', function() {
            // Sort implementation here
        });

        document.getElementById('categoryFilter').addEventListener('change', function() {
            // Filter implementation here
        });

        document.getElementById('searchInput').addEventListener('input', function() {
            // Search implementation here
            keyword = this.value.toLowerCase();
            document.querySelectorALL('.investment-card').foreach(card => {
                const title = card.dataset.title.toLowerCase();
                if (title.includes(keyword)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            })
        });

        // View Toggle
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const view = this.dataset.view;
                const grid = document.getElementById('investmentsGrid');
                grid.className = view === 'list' ? 'investments-list' : 'investments-grid';
            });
        });

        // Scroll to Top
        const scrollBtn = document.getElementById('scrollToTop');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollBtn.style.display = 'block';
            } else {
                scrollBtn.style.display = 'none';
            }
        });

        scrollBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Initialize animations
        setTimeout(() => {
            animateCounters();
        }, 1000);

        // Intersection Observer for card animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.investment-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>