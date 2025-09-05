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
      display: none; /* sembunyikan dulu sebelum klik OK */
    }
    /* ======== CSS SAMA DENGAN YANG SEBELUMNYA ======== */
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
      word-wrap: break-word;
    }
    @keyframes glow {
      from { text-shadow: 0 0 10px #00ff41; }
      to   { text-shadow: 0 0 20px #00ff41, 0 0 30px #00ff41; }
    }
    .noise-layer {
      position: relative;
      width: min(90vw, 600px);
      height: min(60vw, 400px);
      max-height: 70vh;
      margin: 0 auto;
      background: #000;
      overflow: hidden;
      border: clamp(1px, 0.3vw, 2px) solid #00ff41;
      box-shadow: 0 0 clamp(10px, 2vw, 20px) rgba(0, 255, 65, 0.3);
      border-radius: clamp(5px, 1vw, 10px);
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
    .hidden-image::after {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8ZGVmcz4KICAgIDxwYXR0ZXJuIGlkPSJoaWRkZW4tcGF0dGVybiIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIj4KICAgICAgPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjMDAxMTAwIi8+CiAgICAgIDxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjE1IiBmaWxsPSIjMDAyMjAwIiBvcGFjaXR5PSIwLjMiLz4KICAgIDwvcGF0dGVybj4KICA8L2RlZnM+CiAgPHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNoaWRkZW4tcGF0dGVybikiLz4KICA8Y2lyY2xlIGN4PSI1MCUiIGN5PSI1MCUiIHI9IjE1JSIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMDAzMzAwIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9IjAuMTUiLz4KICA8Y2lyY2xlIGN4PSI0MyUiIGN5PSI0MCUiIHI9IjMlIiBmaWxsPSIjMDA0NDAwIiBvcGFjaXR5PSIwLjIiLz4KICA8Y2lyY2xlIGN4PSI1NyUiIGN5PSI0MCUiIHI9IjMlIiBmaWxsPSIjMDA0NDAwIiBvcGFjaXR5PSIwLjIiLz4KICA8ZWxsaXBzZSBjeD0iNTAlIiBjeT0iNDclIiByeD0iMiUiIHJ5PSIzJSIgZmlsbD0iIzAwNTUwMCIgb3BhY2l0eT0iMC4xNSIvPgogIDxwYXRoIGQ9Ik0gNDAlIDU4JSBRIDQ5JSA3MCUgNjAlIDU4JSIgc3Ryb2tlPSIjMDA0NDAwIiBzdHJva2Utd2lkdGg9IjIiIGZpbGw9Im5vbmUiIG9wYWNpdHk9IjAuMiIvPgo8L3N2Zz4=') no-repeat center;
      background-size: cover;
      opacity: 0.03;
    }
    .noise { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; pointer-events: none; }
    .static-noise { animation: staticNoise 0.1s infinite; }
    @keyframes staticNoise {
      0% { transform: translate(0,0); }
      25%{ transform: translate(-1px,1px); }
      50%{ transform: translate(1px,-1px); }
      75%{ transform: translate(-1px,-1px); }
      100%{ transform: translate(1px,1px); }
    }
    .matrix-rain { position: absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:3; }
    .matrix-column { position: absolute; top:-100%; color:#00ff41; font-size: clamp(8px,1.5vw,12px); line-height: clamp(8px,1.5vw,12px); white-space:pre; animation: matrixFall linear infinite; }
    @keyframes matrixFall { to { top:100%; } }
    .warning { margin-top: clamp(20px, 4vh, 30px); padding: clamp(15px, 3vw, 20px); background: rgba(0,0,0,0.8); border: 1px solid #ff0000; color: #ff4444; border-radius: 10px; }
    .warning h3 { margin: 0 0 10px 0; text-shadow: 0 0 5px #ff0000; }
  </style>
</head>
<body>
  <div class="container">
    <h1>🔒 PESAN TERSEMBUNYI 🔒</h1>
    <div class="noise-layer">
      <div class="hidden-image"></div>
      <div class="noise">
        <div class="static-noise"></div>
        <div class="matrix-rain" id="matrixRain"></div>
      </div>
    </div>
    <div class="warning">
      <h3>⚠️ AKSES TERBATAS ⚠️</h3>
      <p>Gambar ini dilindungi. Tanpa kunci dekripsi, tidak ada yang bisa kamu lihat.</p>
    </div>
  </div>

  <script>
    function createMatrixRain() {
      const container = document.getElementById('matrixRain');
      const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノ';
      const screenWidth = window.innerWidth;
      const columnCount = Math.max(10, Math.min(30, Math.floor(screenWidth / 30)));
      container.innerHTML = '';
      for (let i = 0; i < columnCount; i++) {
        const column = document.createElement('div');
        column.className = 'matrix-column';
        column.style.left = (Math.random() * 100) + '%';
        column.style.animationDuration = (Math.random() * 3 + 2) + 's';
        column.style.animationDelay = Math.random() * 2 + 's';
        let text = '';
        for (let j = 0; j < 20; j++) {
          text += chars.charAt(Math.floor(Math.random() * chars.length)) + '\n';
        }
        column.textContent = text;
        container.appendChild(column);
      }
    }

    // popup misterius
    window.addEventListener('DOMContentLoaded', () => {
      setTimeout(() => {
        alert("⚠️ Anda mencoba membuka pesan rahasia.\nKlik OK untuk melanjutkan...");
        document.body.style.display = "block";
        createMatrixRain();
      }, 300);
    });
  </script>
</body>
</html>
