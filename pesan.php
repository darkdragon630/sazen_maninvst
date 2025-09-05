<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tersembunyi</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Courier New', monospace;
            background: linear-gradient(45deg, #1a1a2e, #16213e, #0f3460);
            color: #00ff41;
            min-height: 100vh;
            overflow: hidden;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            position: relative;
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 30px;
            text-shadow: 0 0 10px #00ff41;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { text-shadow: 0 0 10px #00ff41; }
            to { text-shadow: 0 0 20px #00ff41, 0 0 30px #00ff41; }
        }

        .noise-layer {
            position: relative;
            width: 600px;
            height: 400px;
            margin: 0 auto;
            background: #000;
            overflow: hidden;
            border: 2px solid #00ff41;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
        }

        .hidden-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('pesan-tersembunyi') no-repeat center;
            background-size: cover;
            opacity: 0.03;
            z-index: 1;
        }

        .noise {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
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
            background-size: 3px 3px, 4px 4px, 2px 2px, 5px 5px, 3px 3px, 2px 2px;
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
            opacity: 0.3;
        }

        /* Responsive breakpoints */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .noise-layer {
                border-width: 1px;
            }
            
            .static-noise {
                background-size: 2px 2px, 3px 3px, 1px 1px, 3px 3px, 2px 2px, 1px 1px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 5px;
                font-size: 14px;
            }
            
            .container {
                padding: 0 5px;
            }
            
            .warning {
                border-radius: 5px;
            }
            
            .glitch-text {
                display: block;
                margin-top: 10px;
            }
        }

        @media (max-height: 600px) {
            .noise-layer {
                max-height: 50vh;
            }
        }

        /* Orientation handling */
        @media (orientation: landscape) and (max-height: 500px) {
            h1 {
                font-size: clamp(1.4em, 4vw, 2em);
                margin-bottom: 15px;
            }
            
            .noise-layer {
                height: min(40vw, 250px);
                max-height: 40vh;
            }
            
            .warning {
                margin-top: 15px;
            }
        }

        @keyframes matrixFall {
            to { top: 100%; }
        }

        .warning {
            margin-top: 30px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #ff0000;
            color: #ff4444;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.3);
        }

        .decrypt-attempts {
            margin-top: 20px;
            font-size: 0.9em;
            color: #888;
        }

        .glitch-text {
            position: relative;
            display: inline-block;
            animation: glitch 1s infinite;
        }

        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
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
        
        <div class="warning">
            <h3>⚠️ AKSES TERBATAS ⚠️</h3>
            <p>Gambar tersembunyi di dalam noise layer ini menggunakan enkripsi visual tingkat tinggi.</p>
            <p class="glitch-text">TIDAK DAPAT DI-REVEAL TANPA KUNCI DEKRIPSI</p>
            <div class="decrypt-attempts">
                <p>Percobaan dekripsi: <span style="color: #ff0000;">GAGAL</span></p>
                <p>Status: <span style="color: #ff0000;">TERKUNCI PERMANEN</span></p>
            </div>
        </div>
    </div>

    <script>
        // Matrix rain effect
        function createMatrixRain() {
            const container = document.getElementById('matrixRain');
            const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
            
            for (let i = 0; i < 20; i++) {
                const column = document.createElement('div');
                column.className = 'matrix-column';
                column.style.left = Math.random() * 100 + '%';
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

        // Prevent right-click and common shortcuts
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || 
                (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                (e.ctrlKey && e.shiftKey && e.key === 'C') ||
                (e.ctrlKey && e.key === 'u')) {
                e.preventDefault();
                return false;
            }
        });

        // Initialize effects
        createMatrixRain();
        
        // Regenerate matrix rain periodically
        setInterval(() => {
            document.getElementById('matrixRain').innerHTML = '';
            createMatrixRain();
        }, 10000);
    </script>
</body>
</html>
