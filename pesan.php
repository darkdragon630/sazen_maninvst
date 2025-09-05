<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesan Tersembunyi</title>
  <style>
    body {
      margin: 0;
      padding: clamp(10px, 3vw, 20px);
      font-family: 'Courier New', monospace;
      background: linear-gradient(45deg, #1a1a2e, #16213e, #0f3460);
      color: #00ff41;
      min-height: 100vh;
      overflow-x: hidden;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
      position: relative;
      padding: 0 clamp(10px, 2vw, 20px);
    }
    h1 {
      font-size: clamp(1.8em, 5vw, 2.5em);
      margin-bottom: clamp(20px, 4vh, 30px);
      text-shadow: 0 0 10px #00ff41;
      animation: glow 2s ease-in-out infinite alternate;
    }
    @keyframes glow {
      from { text-shadow: 0 0 10px #00ff41; }
      to { text-shadow: 0 0 20px #00ff41, 0 0 30px #00ff41; }
    }
    .noise-layer {
      position: relative;
      width: min(90vw, 600px);
      height: min(60vw, 400px);
      margin: 0 auto;
      background: #000;
      border: 2px solid #00ff41;
      box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
      border-radius: 10px;
      overflow: hidden;
    }
    .hidden-image {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: url('pesan_tersembunyi.png') no-repeat center;
      background-size: contain;
      opacity: 0.05;
      z-index: 1;
      filter: brightness(0.1) contrast(0.3);
      mix-blend-mode: screen;
    }
    .warning {
      margin-top: 20px;
      padding: 15px;
      background: rgba(0,0,0,0.8);
      border: 1px solid #ff0000;
      color: #ff4444;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(255,0,0,0.3);
      backdrop-filter: blur(5px);
    }
    .warning h3 {
      margin: 0 0 10px 0;
      text-shadow: 0 0 5px #ff0000;
    }
    .decrypt-panel {
      margin-top: 20px;
      padding: 15px;
      border: 1px solid #00ff41;
      border-radius: 8px;
      background: rgba(0,0,0,0.7);
    }
    .decrypt-panel input {
      padding: 8px;
      width: 80%;
      border: none;
      outline: none;
      border-radius: 5px;
      margin-bottom: 10px;
      font-family: monospace;
    }
    .decrypt-panel button {
      padding: 10px 15px;
      background: #00ff41;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }
    .status {
      margin-top: 10px;
      font-size: 0.9em;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🔒 PESAN TERSEMBUNYI 🔒</h1>
    
    <div class="noise-layer">
      <div class="hidden-image"></div>
    </div>

    <div class="warning">
      <h3>⚠️ AKSES TERBATAS ⚠️</h3>
      <p>Masukkan kode dekripsi Base64 untuk membuka pesan tersembunyi.</p>
    </div>

    <div class="decrypt-panel">
      <input type="text" id="decryptCode" placeholder="Masukkan kode Base64 di sini...">
      <br>
      <button id="decryptBtn" onclick="attemptDecrypt()">DEKRIPSI</button>
      <div class="status" id="statusText">MENUNGGU INPUT</div>
      <div class="status">Percobaan: <span id="attemptCount">0</span> | Gagal: <span id="failCount">0</span></div>
    </div>
  </div>

  <script>
    const correctCode = "YWt1IHNlbGFsdSBtZW55dWthaSBtdSBkYWxhbSBkaWFtIHJpa2E=";
    const maxAttempts = 5;

    // Ambil data dari localStorage
    let log = JSON.parse(localStorage.getItem("decryptLog")) || {
      attempts: 0,
      fails: 0,
      success: false
    };

    // Update tampilan awal
    document.getElementById("attemptCount").textContent = log.attempts;
    document.getElementById("failCount").textContent = log.fails;

    if (log.success) {
      document.getElementById("statusText").textContent = "✅ Sudah berhasil sebelumnya!";
      document.getElementById("statusText").style.color = "#00ff41";
    }

    function saveLog() {
      localStorage.setItem("decryptLog", JSON.stringify(log));
    }

    function attemptDecrypt() {
      const input = document.getElementById("decryptCode").value.trim();
      const statusText = document.getElementById("statusText");
      const attemptCountEl = document.getElementById("attemptCount");
      const failCountEl = document.getElementById("failCount");

      log.attempts++;
      attemptCountEl.textContent = log.attempts;

      if (input === correctCode) {
        log.success = true;
        statusText.textContent = "✅ BERHASIL - Pesan berhasil diungkap!";
        statusText.style.color = "#00ff41";

        saveLog();

        alert("🎉 Selamat! Anda menemukan pesan tersembunyi!");
        const decoded = atob(correctCode);
        setTimeout(() => {
          alert("📜 Isi pesan: " + decoded);
          window.location.href = "pesan_tersembunyi.png";
        }, 1000);

      } else {
        log.fails++;
        failCountEl.textContent = log.fails;
        statusText.textContent = "❌ Kode salah!";
        statusText.style.color = "#ff0000";

        if (log.attempts >= maxAttempts) {
          statusText.textContent = "🔒 TERKUNCI PERMANEN!";
          document.getElementById("decryptBtn").disabled = true;
          document.getElementById("decryptCode").disabled = true;
        }

        saveLog();
      }
    }
  </script>
</body>
</html>
