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
    }
    h1 {
      font-size: clamp(1.8em, 5vw, 2.5em);
      margin-bottom: 20px;
      text-shadow: 0 0 10px #00ff41;
      animation: glow 2s ease-in-out infinite alternate;
    }
    @keyframes glow {
      from { text-shadow: 0 0 10px #00ff41; }
      to { text-shadow: 0 0 20px #00ff41, 0 0 30px #00ff41; }
    }
    .noise-layer {
      width: min(90vw, 600px);
      height: min(60vw, 400px);
      margin: 0 auto;
      border: 2px solid #00ff41;
      box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
      border-radius: 10px;
      overflow: hidden;
      position: relative;
    }
    .hidden-image {
      width: 100%; height: 100%;
      background: url('pesan_tersembunyi.png') no-repeat center;
      background-size: cover;
      opacity: 0.3;
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
    /* Modal Popup */
    .modal {
      display: none;
      position: fixed;
      z-index: 100;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.8);
      backdrop-filter: blur(5px);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: #111;
      border: 2px solid #00ff41;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      color: #00ff41;
      box-shadow: 0 0 30px #00ff41;
      animation: pop 0.5s ease;
      max-width: 600px;
    }
    @keyframes pop {
      from { transform: scale(0.5); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    #decodedMessage {
      margin-top: 15px;
      font-size: 1.1em;
      white-space: pre-wrap;
      min-height: 40px;
    }
    /* Animasi layar berguncang saat terkunci */
    @keyframes shake {
      0% { transform: translate(2px, 2px) rotate(0deg); }
      10% { transform: translate(-2px, -4px) rotate(-1deg); }
      20% { transform: translate(-6px, 0px) rotate(1deg); }
      30% { transform: translate(6px, 4px) rotate(0deg); }
      40% { transform: translate(2px, -2px) rotate(1deg); }
      50% { transform: translate(-2px, 4px) rotate(-1deg); }
      60% { transform: translate(-6px, 2px) rotate(0deg); }
      70% { transform: translate(6px, 2px) rotate(-1deg); }
      80% { transform: translate(-2px, -2px) rotate(1deg); }
      90% { transform: translate(2px, 4px) rotate(0deg); }
      100% { transform: translate(2px, -4px) rotate(-1deg); }
    }
    .locked {
      animation: shake 0.5s;
      animation-iteration-count: 3;
      background: rgba(255,0,0,0.2);
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🔒 PESAN TERSEMBUNYI 🔒</h1>
    
    <div class="noise-layer">
      <div class="hidden-image"></div>
    </div>

    <div class="decrypt-panel">
      <input type="text" id="decryptCode" placeholder="Masukkan kode Base64 di sini...">
      <br>
      <button id="decryptBtn" onclick="attemptDecrypt()">DEKRIPSI</button>
      <div class="status" id="statusText">MENUNGGU INPUT</div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal" id="successModal">
    <div class="modal-content">
      <h2>🎉 Selamat!</h2>
      <p>Anda menemukan pesan tersembunyi!</p>
      <p id="decodedMessage"></p>
    </div>
  </div>

  <script>
    const correctCode = "YWt1IHNlbGFsdSBtZW55dWthaSBtdSBkYWxhbSBkaWFtIHJpa2E=";
    const maxAttempts = 3;
    let log = { attempts: 0 };

    function attemptDecrypt() {
      const input = document.getElementById("decryptCode").value.trim();
      const statusText = document.getElementById("statusText");
      const panel = document.querySelector(".decrypt-panel");

      // Jika sudah terkunci
      if (log.attempts >= maxAttempts) {
        statusText.textContent = "🔒 TERKUNCI PERMANEN!";
        statusText.style.color = "#ff0000";
        document.getElementById("decryptBtn").disabled = true;
        document.getElementById("decryptCode").disabled = true;
        panel.classList.add("locked");
        return;
      }

      if (input === correctCode) {
        statusText.textContent = "✅ BERHASIL - Pesan berhasil diungkap!";
        statusText.style.color = "#00ff41";

        // Efek typewriter
        const decoded = "📜 Isi pesan: " + atob(correctCode);
        let i = 0;
        const speed = 80;
        const msgEl = document.getElementById("decodedMessage");
        msgEl.textContent = "";
        document.getElementById("successModal").style.display = "flex";

        function typeWriter() {
          if (i < decoded.length) {
            msgEl.textContent += decoded.charAt(i);
            i++;
            setTimeout(typeWriter, speed);
          }
        }
        typeWriter();

      } else {
        log.attempts++;
        if (log.attempts >= maxAttempts) {
          statusText.textContent = "🔒 TERKUNCI PERMANEN!";
          statusText.style.color = "#ff0000";
          document.getElementById("decryptBtn").disabled = true;
          document.getElementById("decryptCode").disabled = true;
          panel.classList.add("locked");
        } else {
          statusText.textContent = `❌ Kode salah! Percobaan ${log.attempts}/${maxAttempts}`;
          statusText.style.color = "#ff0000";
        }
      }
    }
  </script>
</body>
</html>
