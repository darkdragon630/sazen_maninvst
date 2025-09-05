</html><!DOCTYPE html>
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
            background: url('pesan_tersembunyi.png') no-repeat center;
            background-size: contain;
            opacity: 0.05;
            z-index: 1;
            filter: brightness(0.1) contrast(0.3);
            mix-blend-mode: screen;
        }

        /* Fallback if image doesn't load */
        .hidden-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8ZGVmcz4KICAgIDxwYXR0ZXJuIGlkPSJoaWRkZW4tcGF0dGVybiIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIj4KICAgICAgPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjMDAxMTAwIi8+CiAgICAgIDxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjE1IiBmaWxsPSIjMDAyMjAwIiBvcGFjaXR5PSIwLjMiLz4KICAgIDwvcGF0dGVybj4KICA8L2RlZnM+CiAgPHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNoaWRkZW4tcGF0dGVybikiLz4KICA8Y2lyY2xlIGN4PSI1MCUiIGN5PSI1MCUiIHI9IjE1JSIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMDAzMzAwIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9IjAuMTUiLz4KICA8Y2lyY2xlIGN4PSI0MyUiIGN5PSI0MCUiIHI9IjMlIiBmaWxsPSIjMDA0NDAwIiBvcGFjaXR5PSIwLjIiLz4KICA8Y2lyY2xlIGN4PSI1NyUiIGN5PSI0MCUiIHI9IjMlIiBmaWxsPSIjMDA0NDAwIiBvcGFjaXR5PSIwLjIiLz4KICA8ZWxsaXBzZSBjeD0iNTAlIiBjeT0iNDclIiByeD0iMiUiIHJ5PSIzJSIgZmlsbD0iIzAwNTUwMCIgb3BhY2l0eT0iMC4xNSIvPgogIDxwYXRoIGQ9Ik0gNDAlIDU4JSBRIDQ5JSA3MCUgNjAlIDU4JSIgc3Ryb2tlPSIjMDA0NDAwIiBzdHJva2Utd2lkdGg9IjIiIGZpbGw9Im5vbmUiIG9wYWNpdHk9IjAuMiIvPgo8L3N2Zz4=') no-repeat center;
            background-size: cover;
            opacity: 0.03;
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

        .decrypt-attempts {
            margin-top: clamp(15px, 3vh, 20px);
            font-size: clamp(0.7em, 2vw, 0.9em);
            color: #888;
        }

        .decrypt-attempts p {
            font-size: inherit;
            margin: clamp(4px, 1vh, 8px) 0;
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

        /* Advanced responsive breakpoints */
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
            
            .warning {
                border-radius: 5px;
            }
            
            .glitch-text {
                display: block;
                margin-top: clamp(8px, 2vh, 15px);
            }

            .matrix-column {
                font-size: clamp(6px, 1.2vw, 10px);
                line-height: clamp(6px, 1.2vw, 10px);
            }
        }

        @media (max-width: 320px) {
            .noise-layer {
                height: min(85vw, 250px);
            }
            
            .warning p {
                font-size: clamp(0.7em, 2.2vw, 0.9em);
            }
        }

        @media (max-height: 600px) {
            .noise-layer {
                max-height: 45vh;
                height: min(50vw, 300px);
            }
            
            h1 {
                margin-bottom: clamp(15px, 3vh, 25px);
            }
        }

        @media (max-height: 500px) {
            body {
                padding: clamp(5px, 1vh, 15px);
            }
            
            .noise-layer {
                max-height: 40vh;
                height: min(45vw, 250px);
            }
            
            .warning {
                margin-top: clamp(10px, 2vh, 20px);
                padding: clamp(10px, 2vh, 15px);
            }
        }

        /* Landscape orientation optimizations */
        @media (orientation: landscape) and (max-height: 500px) {
            h1 {
                font-size: clamp(1.4em, 4vw, 2em);
                margin-bottom: clamp(10px, 2vh, 15px);
            }
            
            .noise-layer {
                height: min(35vw, 200px);
                max-height: 35vh;
            }
            
            .warning {
                margin-top: clamp(10px, 2vh, 15px);
            }
        }

        /* Ultra-wide screen support */
        @media (min-width: 1200px) {
            .container {
                max-width: 1000px;
            }
            
            .noise-layer {
                width: min(70vw, 700px);
                height: min(45vw, 450px);
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .hidden-image {
                image-rendering: crisp-edges;
            }
        }

        /* Reduced motion preferences */
        @media (prefers-reduced-motion: reduce) {
            .glow,
            .staticNoise,
            .matrixFall,
            .glitch {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: light) {
            body {
                background: linear-gradient(45deg, #2a2a3e, #26314e, #1f4470);
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
        
        <div class="warning">
            <h3>⚠️ AKSES TERBATAS ⚠️</h3>
            <p>Gambar tersembunyi di dalam noise layer ini menggunakan enkripsi visual tingkat tinggi.</p>
            <p class="glitch-text">TIDAK DAPAT DI-REVEAL TANPA KUNCI DEKRIPSI</p>
            <div class="decrypt-attempts">
                <p>Percobaan dekripsi: <span style="color: #ff0000;">GAGAL</span></p>
                <p>Status: <span style="color: #ff0000;">TERKUNCI PERMANEN</span></p>
                <p>Target: <span style="color: #ffaa00;">pesan_tersembunyi.png</span></p>
            </div>
        </div>
    </div>

    <script>
        // Matrix rain effect with responsive character count
        function createMatrixRain() {
            const container = document.getElementById('matrixRain');
            const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
            
            // Responsive column count based on screen width
            const screenWidth = window.innerWidth;
            const columnCount = Math.max(10, Math.min(30, Math.floor(screenWidth / 30)));
            
            container.innerHTML = ''; // Clear existing columns
            
            for (let i = 0; i < columnCount; i++) {
                const column = document.createElement('div');
                column.className = 'matrix-column';
                column.style.left = (Math.random() * 100) + '%';
                column.style.animationDuration = (Math.random() * 3 + 2) + 's';
                column.style.animationDelay = Math.random() * 2 + 's';
                
                // Responsive text length
                const textLength = Math.max(10, Math.min(25, Math.floor(window.innerHeight / 25)));
                let text = '';
                for (let j = 0; j < textLength; j++) {
                    text += chars.charAt(Math.floor(Math.random() * chars.length)) + '\n';
                }
                column.textContent = text;
                
                container.appendChild(column);
            }
        }

        // Enhanced security measures
        function preventInspection() {
            // Prevent right-click
            document.addEventListener('contextmenu', e => {
                e.preventDefault();
                return false;
            });

            // Prevent common developer shortcuts
            document.addEventListener('keydown', function(e) {
                // F12, Ctrl+Shift+I, Ctrl+Shift+C, Ctrl+U, Ctrl+Shift+J
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && ['I', 'C', 'J'].includes(e.key)) ||
                    (e.ctrlKey && e.key === 'u')) {
                    e.preventDefault();
                    showWarning();
                    return false;
                }
            });

            // Detect DevTools
            let devtools = {open: false};
            setInterval(() => {
                if (window.outerHeight - window.innerHeight > 200 || 
                    window.outerWidth - window.innerWidth > 200) {
                    if (!devtools.open) {
                        devtools.open = true;
                        showWarning();
                    }
                }
            }, 500);
        }

        function showWarning() {
            const warning = document.querySelector('.warning');
            warning.style.animation = 'glitch 0.5s ease-in-out 3';
            
            // Add extra security message
            const extraWarning = document.createElement('p');
            extraWarning.textContent = '🚨 DETEKSI INTRUSI - AKSES DITOLAK 🚨';
            extraWarning.style.color = '#ff0000';
            extraWarning.style.fontWeight = 'bold';
            extraWarning.style.animation = 'glow 0.5s infinite';
            
            const attempts = document.querySelector('.decrypt-attempts');
            if (!attempts.querySelector('.intrusion-warning')) {
                extraWarning.className = 'intrusion-warning';
                attempts.appendChild(extraWarning);
            }
        }

        // Responsive matrix rain regeneration
        function handleResize() {
            createMatrixRain();
        }

        // Initialize everything
        window.addEventListener('load', () => {
            createMatrixRain();
            preventInspection();
        });

        window.addEventListener('resize', handleResize);
        
        // Regenerate matrix rain periodically with responsive timing
        const regenerateInterval = window.innerWidth < 768 ? 15000 : 10000;
        setInterval(() => {
            createMatrixRain();
        }, regenerateInterval);

        // Image loading fallback
        window.addEventListener('load', () => {
            const hiddenImage = document.querySelector('.hidden-image');
            const img = new Image();
            img.onload = () => {
                hiddenImage.style.backgroundImage = `url('pesan_tersembunyi.png')`;
                console.log('🔒 Gambar tersembunyi berhasil dimuat');
            };
            img.onerror = () => {
                console.log('⚠️ Menggunakan gambar fallback');
                // Fallback SVG sudah ada di CSS
            };
            img.src = 'pesan_tersembunyi.png';
        });
    </script>
</body>
</html>
