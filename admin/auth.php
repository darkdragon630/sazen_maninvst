<?php
// ============================
// INIT & SECURITY HEADER
// ============================
ob_start(); // output buffering

// Session secure config
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
session_start();
session_regenerate_id(true);

require_once "../config.php";

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Messages
$error = "";
$success = $_SESSION['reg_success'] ?? "";
unset($_SESSION['reg_success']);

// ============================
// FUNCTION
// ============================

// CSRF validation
function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Username: 3-20 karakter, alfanumerik + underscore
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Password: min 8 karakter, huruf besar & kecil, angka
function validatePassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

// Sanitasi input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Rate limiting
function checkRateLimit($action) {
    $key = $action . '_' . $_SERVER['REMOTE_ADDR'];
    $max_attempts = 5;
    $time_window = 900; // 15 menit

    if (!isset($_SESSION['rate_limit'])) $_SESSION['rate_limit'] = [];
    $current_time = time();

    // Clear old attempts
    foreach ($_SESSION['rate_limit'] as $k => $data) {
        if ($current_time - $data['first_attempt'] > $time_window) unset($_SESSION['rate_limit'][$k]);
    }

    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['attempts' => 1, 'first_attempt' => $current_time];
        return true;
    }

    $rate_data = $_SESSION['rate_limit'][$key];

    if ($current_time - $rate_data['first_attempt'] > $time_window) {
        $_SESSION['rate_limit'][$key] = ['attempts' => 1, 'first_attempt' => $current_time];
        return true;
    }

    if ($rate_data['attempts'] >= $max_attempts) return false;

    $_SESSION['rate_limit'][$key]['attempts']++;
    return true;
}

// ============================
// LOGIN PROCESS
// ============================
if (isset($_POST['login'])) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = "❌ Token keamanan tidak valid.";
    } elseif (!checkRateLimit('login')) {
        $error = "❌ Terlalu banyak percobaan login. Coba lagi dalam 15 menit.";
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "❌ Username dan password tidak boleh kosong.";
        } elseif (!validateUsername($username)) {
            $error = "❌ Format username tidak valid.";
        } else {
            try {
                $sql = "SELECT id, username, email, password, failed_attempts, locked_until FROM users WHERE username=? LIMIT 1";
                $stmt = $koneksi->prepare($sql);
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user) {
                    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                        $error = "❌ Akun terkunci sementara.";
                    } elseif (password_verify($password, $user['password'])) {
                        $resetSql = "UPDATE users SET failed_attempts=0, locked_until=NULL WHERE id=?";
                        $koneksi->prepare($resetSql)->execute([$user['id']]);

                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['login_time'] = time();
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        header("Location: ../dashboard.php");
                        exit;
                    } else {
                        $failed_attempts = $user['failed_attempts'] + 1;
                        $locked_until = $failed_attempts >= 5 ? date('Y-m-d H:i:s', time()+1800) : null;
                        $updateSql = "UPDATE users SET failed_attempts=?, locked_until=? WHERE id=?";
                        $koneksi->prepare($updateSql)->execute([$failed_attempts, $locked_until, $user['id']]);
                        $remaining = max(0, 5 - $failed_attempts);
                        $error = $locked_until ? "❌ Akun terkunci 30 menit." : "❌ Username atau password salah. Sisa percobaan: $remaining";
                    }
                } else {
                    $error = "❌ Username atau password salah.";
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "❌ Terjadi kesalahan sistem.";
            }
        }
    }
}

// ============================
// REGISTER PROCESS
// ============================
if (isset($_POST['register'])) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = "❌ Token keamanan tidak valid.";
    } elseif (!checkRateLimit('register')) {
        $error = "❌ Terlalu banyak percobaan registrasi. Coba lagi dalam 15 menit.";
    } else {
        $username = sanitizeInput($_POST['reg_username'] ?? '');
        $email = sanitizeInput($_POST['reg_email'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "❌ Semua field harus diisi.";
        } elseif (!validateUsername($username)) {
            $error = "❌ Username 3-20 karakter, hanya huruf, angka, underscore.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "❌ Format email tidak valid.";
        } elseif (!validatePassword($password)) {
            $error = "❌ Password minimal 8 karakter, huruf besar, kecil, angka.";
        } elseif ($password !== $confirm_password) {
            $error = "❌ Password tidak sama.";
        } else {
            try {
                $sql = "SELECT username, email FROM users WHERE username=? OR email=? LIMIT 1";
                $stmt = $koneksi->prepare($sql);
                $stmt->execute([$username, $email]);
                $existing_user = $stmt->fetch();

                if ($existing_user) {
                    $error = $existing_user['username']===$username ? "❌ Username sudah digunakan." : "❌ Email sudah terdaftar.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost'=>65536,'time_cost'=>4,'threads'=>3]);
                    $sql = "INSERT INTO users (username,email,password,created_at,failed_attempts) VALUES (?,?,?,NOW(),0)";
                    $stmt = $koneksi->prepare($sql);
                    if ($stmt->execute([$username,$email,$hashed_password])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        $_SESSION['reg_success'] = "✅ Akun berhasil dibuat! Silakan login.";
                        header("Location: auth.php");
                        exit;
                    } else $error = "❌ Gagal membuat akun.";
                }
            } catch (Exception $e) {
                error_log("Register error: " . $e->getMessage());
                $error = "❌ Terjadi kesalahan sistem.";
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

        <div class="tab-navigation">
            <button class="tab-btn active" data-tab="login">🔑 Masuk</button>
            <button class="tab-btn" data-tab="register">👤 Daftar</button>
        </div>

        <?php if($error): ?>
            <div class="message error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="message success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- LOGIN FORM -->
        <div class="tab-content active" id="login">
            <form method="POST" class="auth-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" 
                        value="<?= $_POST['username']??'' ?>"
                        pattern="^[a-zA-Z0-9_]{3,20}$"
                        title="3-20 karakter, huruf, angka, underscore" required>
                </div>
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="submit-btn">Masuk →</button>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div class="tab-content" id="register">
            <form method="POST" class="auth-form" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="reg_username">👤 Username</label>
                    <input type="text" id="reg_username" name="reg_username"
                        value="<?= $_POST['reg_username']??'' ?>"
                        pattern="^[a-zA-Z0-9_]{3,20}$"
                        title="3-20 karakter, huruf, angka, underscore" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">📧 Email</label>
                    <input type="email" id="reg_email" name="reg_email" value="<?= $_POST['reg_email']??'' ?>" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">🔒 Password</label>
                    <input type="password" id="reg_password" name="reg_password"
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                        title="Minimal 8 karakter, huruf besar/kecil, angka" required>
                    <small class="password-hint">Minimal 8 karakter, huruf besar/kecil, angka</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">🔒 Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
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
document.addEventListener('DOMContentLoaded', () => {
    // Tab switch
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(b => b.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.getAttribute('data-tab')).classList.add('active');
        });
    });

    // Password strength & confirm
    const regPass = document.getElementById('reg_password');
    const confirmPass = document.getElementById('confirm_password');
    if(regPass && confirmPass) {
        regPass.addEventListener('input', () => {
            const p = regPass.value;
            const strength = (/[a-z]/.test(p)?1:0) + (/[A-Z]/.test(p)?1:0) + (/\d/.test(p)?1:0) + (p.length>=8?1:0);
            regPass.style.borderColor = strength>=4?'#10b981':(strength>=2?'#f59e0b':'#ef4444');
        });
        confirmPass.addEventListener('input', () => {
            if(confirmPass.value !== regPass.value) {
                confirmPass.setCustomValidity('Password tidak sama'); confirmPass.style.borderColor='#ef4444';
            } else { confirmPass.setCustomValidity(''); confirmPass.style.borderColor='#10b981'; }
        });
    }

    // Username visual feedback
    document.querySelectorAll('#username,#reg_username').forEach(f=>{
        f.addEventListener('input', ()=>{ f.style.borderColor=/^[a-zA-Z0-9_]{3,20}$/.test(f.value)||f.value===''?'':'#ef4444'; });
    });

    // Prevent multiple submit
    let submitting=false;
    document.querySelectorAll('#loginForm,#registerForm').forEach(form=>{
        form.addEventListener('submit', e=>{
            if(submitting){ e.preventDefault(); return; }
            submitting=true;
            const btn=form.querySelector('.submit-btn');
            if(btn){ btn.disabled=true; btn.innerHTML='Memproses...'; }
            setTimeout(()=>{ submitting=false; if(btn){ btn.disabled=false; btn.innerHTML=btn.name==='login'?'Masuk →':'Buat Akun →'; } },3000);
        });
    });

    // Auto-clear messages
    setTimeout(()=>{ document.querySelectorAll('.message').forEach(m=>{ m.style.opacity=0; setTimeout(()=>m.remove(),500); }); },5000);
    
    // Clear password on unload
    window.addEventListener('beforeunload', ()=>{ document.querySelectorAll('input[type="password"]').forEach(i=>i.value=''); });
});
</script>

<style>
.password-hint{font-size:.75rem;color:#6b7280;margin-top:.25rem;display:block;}
.form-group input:invalid{border-color:#ef4444;}
.form-group input:valid{border-color:#10b981;}
.message{transition:opacity .5s ease;}
.submit-btn:disabled{opacity:.7;cursor:not-allowed;}
</style>
</body>
</html>
