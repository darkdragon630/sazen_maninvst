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
            word-wrap: break-word;
        }

        @keyframes glow {
            from { text-shadow: 0 0 10px #00ff41; }
            to { text-shadow: 0 0 20px #00ff41, 0 0 30px #00ff41; }
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8ZGVmcz4KICAgIDxwYXR0ZXJuIGlkPSJoaWRkZW4tcGF0dGVybiIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIj4KICAgICAgPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjMDAxMTAwIi8+CiAgICAgIDxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjE1IiBmaWxsPSIjMDAyMjAwIiBvcGFjaXR5PSIwLjMiLz4KICAgIDwvcGF0dGVybj4KICA8L2RlZnM+CiAgPHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNoaWRkZW4tcGF0dGVybikiLz4KICA8dGV4dCB4PSI1MCUiIHk9IjMwJSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzAwNDQwMCIgb3BhY2l0eT0iMC4yIj7wn5ODPC90ZXh0PgogIDx0ZXh0IHg9IjUwJSIgeT0iNTAlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LXNpemU9IjE4IiBmaWxsPSIjMDA2NjAwIiBvcGFjaXR5PSIwLjMiPlBFU0FOIFRFUlNFTUJVTllJPC90ZXh0PgogIDx0ZXh0IHg9IjUwJSIgeT0iNzAlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LXNpemU9IjE0IiBmaWxsPSIjMDA1NTAwIiBvcGFjaXR5PSIwLjI1Ij5Ib2xvIEthdSEgQWt1IFNlbGFsdSBNZW55dWthaSBNdTwvdGV4dD4KPC9zdmc+') no-repeat center;
            background-size: contain;
            opacity: 0.05;
            z-index: 1;
            filter: brightness(0.1) contrast(0.3);
            mix-blend-mode: screen;
            transition: all 2s ease;
        }

        .hidden-image.revealed-image {
            opacity: 0.8;
            filter: brightness(1) contrast(1);
            mix-blend-mode: normal;
        }

        .noise {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
            transition: opacity 2s ease;
        }

        .static-noise {
            position: absolute;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(0, 255, 65, 0.1) 0%, transparent 2%),
                radial-gradient(circle at 80% 70%, rgba(0, 255, 65, 0.1) 0%, transparent 2%),
                radial-gradient(circle at 40% 80%, rgba(0, 255, 65, 0.1) 0%, transparent 2%),
                radial-gradient(circle at 60% 20%, rgba(0, 255, 65, 0.1) 0%, transparent 2%),
                radial-gradient(circle at 90% 40%, rgba(0, 255, 65, 0.1) 0%, transparent 2%),
                linear-gradient(90deg, transparent 50%, rgba(0, 255, 65, 0.05) 50%);
            background-size: clamp(2px, 0.5vw, 3px) clamp(2px, 0.5vw, 3px), 
                           clamp(3px, 0.7vw, 4px) clamp(3px, 0.7vw, 4px), 
                           clamp(1px, 0.3vw, 2px) clamp(1px, 0.3vw, 2px), 
                           clamp(4px, 0.8vw, 5px) clamp(4px, 0.8vw, 5px), 
                           clamp(2px, 0.5vw, 3px) clamp(2px, 0.5vw, 3px), 
                           clamp(1px, 0.3vw, 2px) clamp(1px, 0.3vw, 2px);
            animation: staticNoise 0.1s infinite;
        }

        @keyframes staticNoise {
            0% { transform: translate(0, 0); }
            25% { transform: translate(-1px, 1px); }
            50% { transform: translate(1px, -1px); }
            75% { transform: translate(-1px, -1px); }
            100% { transform: translate(1px, 1px); }
        }

        .matrix-rain {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 3;
        }

        .matrix-column {
            position: absolute;
            top: -100%;
            color: #00ff41;
            font-size: clamp(8px, 1.5vw, 12px);
            line-height: clamp(8px, 1.5vw, 12px);
            white-space: pre;
            animation: matrixFall linear infinite;
            opacity: clamp(0.2, 0.5vw, 0.4);
        }

        @keyframes matrixFall {
            to { top: 100%; }
        }

        .decrypt-panel {
            margin-top: clamp(20px, 4vh, 30px);
            padding: clamp(15px, 3vw, 20px);
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid #00ff41;
            color: #00ff41;
            border-radius: clamp(5px, 1vw, 10px);
            box-shadow: 0 0 clamp(10px, 2vw, 15px) rgba(0, 255, 65, 0.3);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .decrypt-panel.success-reveal {
            border-color: #00ff41;
            background: rgba(0, 50, 0, 0.9);
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.6);
        }

        .decrypt-panel.failed-attempt {
            border-color: #ff0000;
            background: rgba(50, 0, 0, 0.9);
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .decrypt-panel h3 {
            font-size: clamp(1.1em, 3.5vw, 1.4em);
            margin: 0 0 clamp(15px, 3vh, 20px) 0;
            text-shadow: 0 0 5px #00ff41;
        }

        .input-group {
            margin-bottom: clamp(15px, 3vh, 20px);
        }

        .input-group label {
            display: block;
            margin-bottom: clamp(8px, 2vh, 10px);
            font-size: clamp(0.9em, 2.5vw, 1em);
            color: #ffaa00;
        }

        #decryptCode {
            width: 100%;
            padding: clamp(10px, 2.5vw, 15px);
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #00ff41;
            color: #00ff41;
            font-family: 'Courier New', monospace;
            font-size: clamp(0.9em, 2.5vw, 1em);
            border-radius: clamp(3px, 0.5vw, 5px);
            box-sizing: border-box;
        }

        #decryptCode:focus {
            outline: none;
            border-color: #ffaa00;
            box-shadow: 0 0 10px rgba(255, 170, 0, 0.3);
        }

        #decryptCode:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #decryptBtn {
            width: 100%;
            padding: clamp(12px, 3vw, 15px);
            background: linear-gradient(45deg, #00ff41, #00cc33);
            border: none;
            color: #000;
            font-family: 'Courier New', monospace;
            font-size: clamp(0.9em, 2.5vw, 1.1em);
            font-weight: bold;
            border-radius: clamp(3px, 0.5vw, 5px);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #decryptBtn:hover:not(:disabled) {
            background: linear-gradient(45deg, #00cc33, #00ff41);
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.5);
        }

        #decryptBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .status-display {
            margin-top: clamp(15px, 3vh, 20px);
            padding: clamp(10px, 2vw, 15px);
            background: rgba(0, 0, 0, 0.7);
            border: 1px solid #333;
            border-radius: clamp(3px, 0.5vw, 5px);
        }

        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: clamp(5px, 1vh, 8px) 0;
            font-size: clamp(0.8em, 2vw, 0.9em);
        }

        .status-label {
            color: #888;
        }

        .status-value {
            font-weight: bold;
        }

        #statusText {
            color: #ffaa00;
        }

        .warning {
            margin-top: clamp(20px, 4vh, 30px);
            padding: clamp(15px, 3vw, 20px);
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #ff0000;
            color: #ff4444;
            border-radius: clamp(5px, 1vw, 10px);
            box-shadow: 0 0 clamp(10px, 2vw, 15px) rgba(255, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }

        .warning h3 {
            font-size: clamp(1.1em, 3.5vw, 1.4em);
            margin: 0 0 clamp(8px, 2vh, 15px) 0;
            text-shadow: 0 0 5px #ff0000;
        }

        .warning p {
            font-size: clamp(0.8em, 2.5vw, 1em);
            margin: clamp(6px, 1.5vh, 10px) 0;
            line-height: 1.4;
        }

        .glitch-text {
            position: relative;
            display: inline-block;
            animation: glitch 1s infinite;
            text-shadow: 0 0 5px #ff0000;
        }

        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
        }

        /* Responsive breakpoints */
        @media (max-width: 768px) {
            body {
                padding: clamp(8px, 2vw, 15px);
            }
            
            .noise-layer {
                border-width: 1px;
                height: min(70vw, 350px);
            }
            
            .matrix-column {
                opacity: 0.25;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: clamp(5px, 1.5vw, 10px);
                font-size: clamp(12px, 3vw, 16px);
            }
            
            .container {
                padding: 0 clamp(5px, 1vw, 15px);
            }
            
            .noise-layer {
                height: min(80vw, 300px);
                border-radius: 5px;
            }

            .matrix-column {
                font-size: clamp(6px, 1.2vw, 10px);
                line-height: clamp(6px, 1.2vw, 10px);
            }
        }
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
        
        <div class="decrypt-panel">
            <h3>🔑 PANEL DEKRIPSI</h3>
            <div class="input-group">
                <label for="decryptCode">Masukkan Kode Dekripsi:</label>
                <input type="text" id="decryptCode" placeholder="Ketik kode rahasia di sini..." maxlength="100">
            </div>
            <button id="decryptBtn" onclick="attemptDecrypt()">DEKRIPSI</button>
            
            <div class="status-display">
                <div class="status-row">
                    <span class="status-label">Status:</span>
                    <span class="status-value" id="statusText">MENUNGGU INPUT</span>
                </div>
                <div class="status-row">
                    <span class="status-label">Percobaan:</span>
                    <span class="status-value"><span id="attemptCount">0</span>/5</span>
                </div>
                <div class="status-row">
                    <span class="status-label">Hasil:</span>
                    <span class="status-value" id="failCount" style="color: #ff0000;">BELUM MENCOBA</span>
                </div>
            </div>
        </div>
        
        <div class="warning">
            <h3>⚠️ AKSES TERBATAS ⚠️</h3>
            <p>Gambar tersembunyi di dalam noise layer ini menggunakan enkripsi visual tingkat tinggi.</p>
            <p class="glitch-text">TIDAK DAPAT DI-REVEAL TANPA KUNCI DEKRIPSI</p>
            <p><small><strong>Petunjuk:</strong> Kode rahasia tersembunyi dalam pesan yang sudah kamu baca... 🤔</small></p>
        </div>
    </div>

    <script>
        // Matrix rain effect with responsive character count
        function createMatrixRain() {
            const container = document.getElementById('matrixRain');
            const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
            
            const screenWidth = window.innerWidth;
            const columnCount = Math.max(10, Math.min(30, Math.floor(screenWidth / 30)));
            
            container.innerHTML = '';
            
            for (let i = 0; i < columnCount; i++) {
                const column = document.createElement('div');
                column.className = 'matrix-column';
                column.style.left = (Math.random() * 100) + '%';
                column.style.animationDuration = (Math.random() * 3 + 2) + 's';
                column.style.animationDelay = Math.random() * 2 + 's';
                
                const textLength = Math.max(10, Math.min(25, Math.floor(window.innerHeight / 25)));
                let text = '';
                for (let j = 0; j < textLength; j++) {
                    text += chars.charAt(Math.floor(Math.random() * chars.length)) + '\n';
                }
                column.textContent = text;
                
                container.appendChild(column);
            }
        }

        // Decrypt functionality
        let attemptCount = 0;
        const maxAttempts = 5;
        const correctCode = 'Holo Kamu! Aku Selalu Menyukai Mu';
        
        function attemptDecrypt() {
            const input = document.getElementById('decryptCode');
            const btn = document.getElementById('decryptBtn');
            const statusText = document.getElementById('statusText');
            const attemptCountEl = document.getElementById('attemptCount');
            const failCount = document.getElementById('failCount');
            const decryptPanel = document.querySelector('.decrypt-panel');
            const hiddenImage = document.querySelector('.hidden-image');
            
            const inputCode = input.value.trim();
            
            if (!inputCode) {
                showInputError('Masukkan kode dekripsi!');
                return;
            }
            
            attemptCount++;
            attemptCountEl.textContent = attemptCount;
            
            btn.disabled = true;
            btn.textContent = 'MEMPROSES...';
            statusText.textContent = 'MEMVERIFIKASI KODE...';
            statusText.style.color = '#ffaa00';
            
            setTimeout(() => {
                if (inputCode === correctCode) {
                    statusText.textContent = 'BERHASIL - AKSES DIBERIKAN!';
                    statusText.style.color = '#00ff41';
                    
                    decryptPanel.classList.add('success-reveal');
                    hiddenImage.classList.add('revealed-image');
                    
                    setTimeout(() => {
                        document.querySelector('.static-noise').style.opacity = '0.3';
                        document.querySelector('.matrix-rain').style.opacity = '0.2';
                    }, 500);
                    
                    btn.textContent = '✅ BERHASIL';
                    btn.style.background = 'linear-gradient(45deg, #00ff41, #00cc33)';
                    input.disabled = true;
                    failCount.textContent = 'BERHASIL!';
                    failCount.style.color = '#00ff41';
                    
                    showSuccessMessage();
                    
                } else {
                    statusText.textContent = 'KODE SALAH - AKSES DITOLAK!';
                    statusText.style.color = '#ff0000';
                    failCount.textContent = `GAGAL (${attemptCount}x)`;
                    
                    decryptPanel.classList.add('failed-attempt');
                    setTimeout(() => decryptPanel.classList.remove('failed-attempt'), 500);
                    
                    if (attemptCount >= maxAttempts) {
                        btn.textContent = '🔒 TERKUNCI';
                        btn.disabled = true;
                        input.disabled = true;
                        statusText.textContent = 'SISTEM TERKUNCI - TERLALU BANYAK PERCOBAAN!';
                        showLockdownEffect();
                    } else {
                        btn.textContent = 'DEKRIPSI';
                        btn.disabled = false;
                        const remaining = maxAttempts - attemptCount;
                        showAttemptWarning(remaining);
                    }
                }
                
                input.value = '';
            }, 1500);
        }
        
        function showInputError(message) {
            const statusText = document.getElementById('statusText');
            const originalText = statusText.textContent;
            const originalColor = statusText.style.color;
            
            statusText.textContent = message;
            statusText.style.color = '#ff0000';
            
            setTimeout(() => {
                statusText.textContent = originalText;
                statusText.style.color = originalColor;
            }, 2000);
        }
        
        function showSuccessMessage() {
            const successMsg = document.createElement('div');
            successMsg.innerHTML = `
                <div style="
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: linear-gradient(45deg, rgba(0,255,65,0.95), rgba(0,200,50,0.95));
                    color: #000;
                    padding: 25px 35px;
                    border-radius: 15px;
                    font-size: 1.2em;
                    font-weight: bold;
                    z-index: 1000;
                    box-shadow: 0 0 40px rgba(0,255,65,0.8);
                    text-align: center;
                    border: 3px solid #00ff41;
                    max-width: 90vw;
                ">
                    🎉 PESAN TERSEMBUNYI BERHASIL DIUNGKAP! 🎉<br>
                    <div style="font-size: 0.9em; margin-top: 15px; padding: 10px; background: rgba(0,0,0,0.2); border-radius: 8px;">
                        "Holo Kamu! Aku Selalu Menyukai Mu"
                    </div>
                    <div style="font-size: 0.7em; margin-top: 10px; color: #333;">
                        Sekarang kamu bisa melihat pesan tersembunyinya! 💚
                    </div>
                </div>
            `;
            document.body.appendChild(successMsg);
            
            setTimeout(() => {
                successMsg.remove();
            }, 6000);
        }
        
        function showAttemptWarning(remaining) {
            const statusText = document.getElementById('statusText');
            statusText.textContent = `TERSISA ${remaining} PERCOBAAN!`;
            statusText.style.color = '#ffaa00';
            
            setTimeout(() => {
                statusText.textContent = 'MENUNGGU INPUT';
                statusText.style.color = '#ffaa00';
            }, 3000);
        }
        
        function showLockdownEffect() {
            const noiseLayer = document.querySelector('.noise-layer');
            const lockdownOverlay = document.createElement('div');
            lockdownOverlay.innerHTML = `
                <div style="
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255,0,0,0.3);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #ff0000;
                    font-size: 2em;
                    font-weight: bold;
                    text-shadow: 0 0 10px #ff0000;
                    z-index: 100;
                    animation: glitch 0.5s infinite;
                ">
                    🚨 LOCKDOWN 🚨
                </div>
            `;
            noiseLayer.appendChild(lockdownOverlay);
        }
        
        // Allow Enter key to submit
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && document.activeElement.id === 'decryptCode') {
                attemptDecrypt();
            }
        });
        
        // Enhanced security measures (simplified for demo)
        function preventInspection() {
            document.addEventListener('contextmenu', e => e.preventDefault());
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && ['I', 'C', 'J'].includes(e.key)) ||
                    (e.ctrlKey && e.key === 'u')) {
                    e.preventDefault();
                    const warning = document.querySelector('.warning');
                    warning.style.animation = 'glitch 0.5s ease-in-out 3';
                    return false;
                }
            });
        }
        
        function handleResize() {
            createMatrixRain();
        }

        // Initialize everything
        window.addEventListener('load', () => {
            createMatrixRain();
            preventInspection();
            initializeDecryptPanel(); // Initialize UI based on saved state
        });

        window.addEventListener('resize', handleResize);
        
        const regenerateInterval = window.innerWidth < 768 ? 15000 : 10000;
        setInterval(() => {
            createMatrixRain();
        }, regenerateInterval);
    </script>
</body>
</html>
