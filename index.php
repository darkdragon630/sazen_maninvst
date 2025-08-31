<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Portofolio Investasi - SAAZ</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-icon-container">
                <i class="fas fa-chart-line header-icon"></i>
            </div>
            <h1 class="header-title">
                Portofolio Investasi <span class="title-highlight">SAAZ</span>
            </h1>
            <p class="header-subtitle">
                <i class="fas fa-sync-alt"></i> Data portofolio diperbarui otomatis
            </p>

            <!-- Stats Cards -->
            <div class="header-stats">
                <div class="stat-card total-investment">
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Total Investasi</div>
                        <div id="totalInvestasi" class="stat-value">Rp 0</div>
                    </div>
                </div>
                <div class="stat-card portfolio-count">
                    <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Jumlah Investasi</div>
                        <div id="jumlahItem" class="stat-value">0 Item</div>
                    </div>
                </div>
                <div class="stat-card performance">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Status</div>
                        <div class="stat-value status-active">
                            <i class="fas fa-circle status-indicator"></i> Aktif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Controls -->
    <section class="portfolio-controls">
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
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari investasi...">
            </div>
        </div>
    </section>

    <!-- Grid -->
    <main>
        <section class="investments-grid" id="investmentsGrid">
            <!-- Card dari AJAX -->
        </section>
    </main>

    <script>
    async function loadInvestasi() {
        try {
            const res = await fetch("fetch_investasi.php");
            const data = await res.json();

            renderStats(data);
            renderInvestasi(data);
        } catch (err) {
            document.getElementById("investmentsGrid").innerHTML =
                "<p>❌ Gagal memuat data</p>";
        }
    }

    function renderStats(data) {
        const total = data.reduce((sum, item) => sum + parseFloat(item.jumlah), 0);
        document.getElementById("totalInvestasi").textContent =
            "Rp " + total.toLocaleString("id-ID");
        document.getElementById("jumlahItem").textContent =
            data.length + " Item";
    }

    function renderInvestasi(data) {
        const grid = document.getElementById("investmentsGrid");
        grid.innerHTML = "";

        data.forEach(item => {
            const days = (Date.now() - new Date(item.tanggal_investasi)) / (1000*60*60*24);
            let status = days < 30 ? "Baru" : (days < 365 ? "Aktif" : "Matang");

            const card = document.createElement("div");
            card.className = "investment-card";
            card.dataset.title = item.judul_investasi.toLowerCase();
            card.dataset.amount = item.jumlah;
            card.dataset.date = item.tanggal_investasi;

            card.innerHTML = `
                <div class="card-header">
                    <h2>${item.judul_investasi}</h2>
                    <span class="category-badge"><i class="fas fa-tag"></i> ${item.nama_kategori ?? "-"}</span>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-coins"></i> Rp ${Number(item.jumlah).toLocaleString("id-ID")}</p>
                    <p><i class="fas fa-calendar-alt"></i> ${new Date(item.tanggal_investasi).toLocaleDateString("id-ID")}</p>
                    <span class="badge">${status}</span>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    // Search
    document.getElementById("searchInput").addEventListener("input", function() {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll(".investment-card").forEach(card => {
            const title = card.dataset.title;
            card.style.display = title.includes(keyword) ? "block" : "none";
        });
    });

    // Sort
    document.getElementById("sortSelect").addEventListener("change", function() {
        const cards = Array.from(document.querySelectorAll(".investment-card"));
        const grid = document.getElementById("investmentsGrid");

        let sorted;
        if (this.value === "date-desc") {
            sorted = cards.sort((a, b) => new Date(b.dataset.date) - new Date(a.dataset.date));
        } else if (this.value === "date-asc") {
            sorted = cards.sort((a, b) => new Date(a.dataset.date) - new Date(b.dataset.date));
        } else if (this.value === "amount-desc") {
            sorted = cards.sort((a, b) => b.dataset.amount - a.dataset.amount);
        } else if (this.value === "amount-asc") {
            sorted = cards.sort((a, b) => a.dataset.amount - b.dataset.amount);
        }

        grid.innerHTML = "";
        sorted.forEach(card => grid.appendChild(card));
    });

    // Auto refresh tiap 30 detik
    setInterval(loadInvestasi, 30000);

    // Load awal
    loadInvestasi();
    </script>
</body>
</html>
