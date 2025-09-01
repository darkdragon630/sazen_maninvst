<?php 
session_start();
require_once "../config.php"; // file koneksi

// Regenerate session ID untuk keamanan
session_regenerate_id(true);

// Set secure session cookie parameters
if (!headers_sent()) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Security headers
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Kalau sudah login langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

// Generate CSRF token jika belum ada
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Variabel pesan
$error = "";
$success = "";

// Tampilkan pesan sukses dari register
if (isset($_SESSION['reg_success'])) {
    $success = $_SESSION['reg_success'];
    unset($_SESSION['reg_success']); // hapus biar tidak muncul lagi setelah refresh
}

/* =========================
   FUNGSI VALIDASI CSRF
========================= */
function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* =========================
   FUNGSI VALIDASI INPUT
========================= */
function validateUsername($username) {
    // Username: 3-20 karakter, hanya alfanumerik dan underscore
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function validatePassword($password) {
    // Password minimal 8 karakter, harus ada huruf besar, kecil, dan angka
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/* =========================
   RATE LIMITING
========================= */
function checkRateLimit($action) {
    $key = $action . '_' . $_SERVER['REMOTE_ADDR'];
    $max_attempts = 5; // maksimal 5 percobaan
    $time_window = 900; // dalam 15 menit (900 detik)
    
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $current_time = time();
    
    // Bersihkan data lama
    foreach ($_SESSION['rate_limit'] as $k => $data) {
        if ($current_time - $data['first_attempt'] > $time_window) {
            unset($_SESSION['rate_limit'][$k]);
        }
    }
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [
            'attempts' => 1,
            'first_attempt' => $current_time
        ];
        return true;
    }
    
    $rate_data = $_SESSION['rate_limit'][$key];
    
    if ($current_time - $rate_data['first_attempt'] > $time_window) {
        // Reset jika sudah lewat time window
        $_SESSION['rate_limit'][$key] = [
            'attempts' => 1,
            'first_attempt' => $current_time
        ];
        return true;
    }
    
    if ($rate_data['attempts'] >= $max_attempts) {
        return false; // Rate limit exceeded
    }
    
    $_SESSION['rate_limit'][$key]['attempts']++;
    return true;
}

/* =========================
   PROSES LOGIN
========================= */
if (isset($_POST['login'])) {
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRF($_POST['csrf_token'])) {
        $error = "❌ Token keamanan tidak valid. Silakan refresh halaman.";
    } 
    // Cek rate limiting
    elseif (!checkRateLimit('login')) {
        $error = "❌ Terlalu banyak percobaan login. Coba lagi dalam 15 menit.";
    } 
    else {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = "❌ Username dan password tidak boleh kosong.";
        } elseif (!validateUsername($username)) {
            $error = "❌ Format username tidak valid.";
        } else {
            try {
                $sql = "SELECT id, username, email, password, failed_attempts, locked_until FROM users WHERE username = ? LIMIT 1";
                $stmt = $koneksi->prepare($sql);
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Cek apakah akun terkunci
                    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                        $error = "❌ Akun terkunci sementara. Coba lagi nanti.";
                    } elseif (password_verify($password, $user['password'])) {
                        // Login berhasil - reset failed attempts
                        $resetSql = "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?";
                        $resetStmt = $koneksi->prepare($resetSql);
                        $resetStmt->execute([$user['id']]);
                        
                        // Regenerate session ID untuk keamanan
                        session_regenerate_id(true);
                        
                        // Simpan session login
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['login_time'] = time();
                        
                        // Generate CSRF token baru
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        // Redirect ke dashboard
                        header("Location: ../dashboard.php");
                        exit;
                    } else {
                        // Password salah - increment failed attempts
                        $failed_attempts = $user['failed_attempts'] + 1;
                        $locked_until = null;
                        
                        // Lock account setelah 5 percobaan gagal
                        if ($failed_attempts >= 5) {
                            $locked_until = date('Y-m-d H:i:s', time() + 1800); // lock 30 menit
                        }
                        
                        $updateSql = "UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?";
                        $updateStmt = $koneksi->prepare($updateSql);
                        $updateStmt->execute([$failed_attempts, $locked_until, $user['id']]);
                        
                        if ($locked_until) {
                            $error = "❌ Akun terkunci karena terlalu banyak percobaan gagal. Coba lagi dalam 30 menit.";
                        } else {
                            $remaining = 5 - $failed_attempts;
                            $error = "❌ Username atau password salah. Sisa percobaan: {$remaining}";
                        }
                    }
                } else {
                    $error = "❌ Username atau password salah.";
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "❌ Terjadi kesalahan sistem. Coba lagi nanti.";
            }
        }
    }
}

/* =========================
   PROSES REGISTER
========================= */
if (isset($_POST['register'])) {
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRF($_POST['csrf_token'])) {
        $error = "❌ Token keamanan tidak valid. Silakan refresh halaman.";
    }
    // Cek rate limiting
    elseif (!checkRateLimit('register')) {
        $error = "❌ Terlalu banyak percobaan registrasi. Coba lagi dalam 15 menit.";
    }
    else {
        $username         = sanitizeInput($_POST['reg_username']);
        $email            = sanitizeInput($_POST['reg_email']);
        $password         = $_POST['reg_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "❌ Semua field harus diisi.";
        } elseif (!validateUsername($username)) {
            $error = "❌ Username hanya boleh huruf, angka, underscore (3-20 karakter).";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "❌ Format email tidak valid.";
        } elseif (!validatePassword($password)) {
            $error = "❌ Password minimal 8 karakter dengan huruf besar, kecil, dan angka.";
        } elseif ($password !== $confirm_password) {
            $error = "❌ Password dan konfirmasi tidak sama.";
        } else {
            try {
                // Cek user / email sudah ada
                $sql = "SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1";
                $stmt = $koneksi->prepare($sql);
                $stmt->execute([$username, $email]);
                $existing_user = $stmt->fetch();
                
                if ($existing_user) {
                    if ($existing_user['username'] === $username) {
                        $error = "❌ Username sudah digunakan.";
                    } else {
                        $error = "❌ Email sudah terdaftar.";
                    }
                } else {
                    // Hash password dengan cost yang tinggi
                    $hashed_password = password_hash($password, PASSWORD_ARGON2ID, [
                        'memory_cost' => 65536, // 64 MB
                        'time_cost' => 4,       // 4 iterations
                        'threads' => 3          // 3 threads
                    ]);
                    
                    $sql = "INSERT INTO users (username, email, password, created_at, failed_attempts) VALUES (?, ?, ?, NOW(), 0)";
                    $stmt = $koneksi->prepare($sql);
                    
                    if ($stmt->execute([$username, $email, $hashed_password])) {
                        // Generate CSRF token baru
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        
                        // Simpan pesan sukses lalu redirect ke login
                        $_SESSION['reg_success'] = "✅ Akun berhasil dibuat! Silakan login.";
                        header("Location: auth.php");
                        exit;
                    } else {
                        $error = "❌ Gagal membuat akun. Coba lagi.";
                    }
                }
            } catch (Exception $e) {
                error_log("Register error: " . $e->getMessage());
                $error = "❌ Terjadi kesalahan sistem. Coba lagi nanti.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register - SAAZ</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">💼</div>
                <h1>SAAZ Investment</h1>
                <p>Kelola portofolio investasi Anda</p>
            </div>

            <!-- Tab -->
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="login">🔑 Masuk</button>
                <button class="tab-btn" data-tab="register">👤 Daftar</button>
            </div>

            <!-- Pesan -->
            <?php if ($error): ?>
                <div class="message error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="message success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Form Login -->
            <div class="tab-content active" id="login">
                <form method="POST" class="auth-form" id="loginForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="form-group">
                        <label for="username">👤 Username</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                               pattern="^[a-zA-Z0-9_]{3,20}$"
                               title="Username: 3-20 karakter, hanya huruf, angka, dan underscore"
                               autocomplete="username"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="password">🔒 Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               autocomplete="current-password"
                               required>
                    </div>
                    <button type="submit" name="login" class="submit-btn">Masuk →</button>
                </form>
            </div>

            <!-- Form Register -->
            <div class="tab-content" id="register">
                <form method="POST" class="auth-form" id="registerForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="form-group">
                        <label for="reg_username">👤 Username</label>
                        <input type="text" 
                               id="reg_username" 
                               name="reg_username" 
                               value="<?= isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : '' ?>" 
                               pattern="^[a-zA-Z0-9_]{3,20}$"
                               title="Username: 3-20 karakter, hanya huruf, angka, dan underscore"
                               autocomplete="username"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="reg_email">📧 Email</label>
                        <input type="email" 
                               id="reg_email" 
                               name="reg_email" 
                               value="<?= isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : '' ?>" 
                               autocomplete="email"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="reg_password">🔒 Password</label>
                        <input type="password" 
                               id="reg_password" 
                               name="reg_password" 
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                               title="Password minimal 8 karakter dengan huruf besar, kecil, dan angka"
                               autocomplete="new-password"
                               required>
                        <small class="password-hint">Minimal 8 karakter, harus ada huruf besar, kecil, dan angka</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">🔒 Konfirmasi Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               autocomplete="new-password"
                               required>
                    </div>
                    <button type="submit" name="register" class="submit-btn">Buat Akun →</button>
                </form>
            </div>

            <div class="auth-footer">
                <p>&copy; <?= date('Y') ?> SAAZ Investment Manager</p>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    // Tab switching functionality
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.getAttribute('data-tab')).classList.add('active');
        });
    });

    // Password validation dan konfirmasi
    const regPassword = document.getElementById('reg_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (regPassword && confirmPassword) {
        // Real-time password strength indicator
        regPassword.addEventListener('input', function() {
            const password = this.value;
            const hasLower = /[a-z]/.test(password);
            const hasUpper = /[A-Z]/.test(password);
            const hasDigit = /[0-9]/.test(password);
            const minLength = password.length >= 8;
            
            let strength = 0;
            if (hasLower) strength++;
            if (hasUpper) strength++;
            if (hasDigit) strength++;
            if (minLength) strength++;
            
            // Update visual feedback
            this.style.borderColor = strength >= 4 ? '#10b981' : (strength >= 2 ? '#f59e0b' : '#ef4444');
        });
        
        // Password confirmation validation
        confirmPassword.addEventListener('input', function() {
            if (this.value !== regPassword.value) {
                this.setCustomValidity('Password tidak sama');
                this.style.borderColor = '#ef4444';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '#10b981';
            }
        });
    }

    // Username validation
    const usernameFields = document.querySelectorAll('#username, #reg_username');
    usernameFields.forEach(field => {
        field.addEventListener('input', function() {
            const username = this.value;
            const isValid = /^[a-zA-Z0-9_]{3,20}$/.test(username);
            this.style.borderColor = isValid || username.length === 0 ? '' : '#ef4444';
        });
    });

    // CSRF Token refresh pada form submit (tambahan keamanan)
    const forms = document.querySelectorAll('#loginForm, #registerForm');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const hiddenToken = this.querySelector('input[name="csrf_token"]');
                if (hiddenToken) {
                    hiddenToken.value = csrfToken.getAttribute('content');
                }
            }
        });
    });

    // Prevent multiple form submissions
    let isSubmitting = false;
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            isSubmitting = true;
            
            const submitBtn = this.querySelector('.submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Memproses...';
            }
            
            // Re-enable setelah 3 detik untuk kasus error
            setTimeout(() => {
                isSubmitting = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = submitBtn.name === 'login' ? 'Masuk →' : 'Buat Akun →';
                }
            }, 3000);
        });
    });

    // Auto-clear error messages setelah 5 detik
    const errorMessage = document.querySelector('.error-message');
    if (errorMessage) {
        setTimeout(() => {
            errorMessage.style.opacity = '0';
            setTimeout(() => errorMessage.remove(), 500);
        }, 5000);
    }

    // Auto-clear success messages setelah 3 detik
    const successMessage = document.querySelector('.success-message');
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.opacity = '0';
            setTimeout(() => successMessage.remove(), 500);
        }, 3000);
    }
});

// Security: Clear sensitive data saat halaman di-unload
window.addEventListener('beforeunload', function() {
    // Clear form data
    document.querySelectorAll('input[type="password"]').forEach(input => {
        input.value = '';
    });
});

// Security: Detect dan cegah tampering dengan CSRF token
setInterval(function() {
    const csrfInputs = document.querySelectorAll('input[name="csrf_token"]');
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    
    if (metaToken) {
        csrfInputs.forEach(input => {
            if (input.value !== metaToken.getAttribute('content')) {
                // Token mismatch detected - refresh halaman
                console.warn('CSRF token mismatch detected. Refreshing page...');
                window.location.reload();
            }
        });
    }
}, 30000); // Cek setiap 30 detik
</script>

<style>
.password-hint {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
    display: block;
}

.form-group input:invalid {
    border-color: #ef4444;
}

.form-group input:valid {
    border-color: #10b981;
}

.message {
    transition: opacity 0.5s ease;
}

.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
</body>
</html>
