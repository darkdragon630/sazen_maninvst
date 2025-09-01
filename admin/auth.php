<?php
session_start();
require_once "../config.php";

// Regenerate session ID untuk keamanan
session_regenerate_id(true);

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

/* =========================
   CSRF TOKEN GENERATION & VALIDATION
========================= */
function generate_csrf_token() {
    return bin2hex(random_bytes(32));
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generate_csrf_token();
}

/* =========================
   PESAN ERROR / SUCCESS
========================= */
$error = "";
$success = "";

if (isset($_SESSION['reg_success'])) {
    $success = $_SESSION['reg_success'];
    unset($_SESSION['reg_success']);
}

if (isset($_SESSION['temp_error'])) {
    $error = $_SESSION['temp_error'];
    unset($_SESSION['temp_error']);
}

/* =========================
   FUNGSIONAL VALIDASI
========================= */
function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function validate_password($password) {
    return strlen($password) >= 8 &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function log_security_event($event, $details = '') {
    $log_entry = date('Y-m-d H:i:s') . " | " . $_SERVER['REMOTE_ADDR'] . " | $event | $details" . PHP_EOL;
    error_log($log_entry, 3, "../logs/security.log");
}

/* =========================
   PROSES LOGIN
========================= */
if (isset($_POST['login'])) {
    try {
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            $error = "❌ Token CSRF tidak valid.";
            log_security_event("CSRF_INVALID", "Login attempt");
            $_SESSION['csrf_token'] = generate_csrf_token();
        } else {
            $_SESSION['csrf_token'] = generate_csrf_token();

            $username = sanitize_input($_POST['username']);
            $password = $_POST['password'];

            if (empty($username) || empty($password)) {
                $error = "❌ Username dan password tidak boleh kosong.";
            } elseif (!validate_username($username)) {
                $error = "❌ Username hanya boleh huruf, angka, dan underscore (3-20 karakter).";
            } else {
                $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user) {
                    $current_time = new DateTime();

                    if ($user['locked_until'] && $current_time < new DateTime($user['locked_until'])) {
                        $error = "❌ Akun terkunci sampai " . date('H:i:s d-m-Y', strtotime($user['locked_until'])) . ".";
                        log_security_event("LOGIN_LOCKED", "User: $username");
                    } elseif (password_verify($password, $user['password'])) {
                        $stmt = $koneksi->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?");
                        $stmt->execute([$user['id']]);

                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['login_time'] = time();

                        log_security_event("LOGIN_SUCCESS", "User: $username");
                        header("Location: ../dashboard.php");
                        exit;
                    } else {
                        $failed_attempts = $user['failed_attempts'] + 1;
                        $locked_until = null;
                        $max_attempts = 5;

                        if ($failed_attempts >= $max_attempts) {
                            $locked_until = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');
                            $error = "❌ Akun terkunci 15 menit karena $max_attempts kali gagal login.";
                            $failed_attempts = 0;
                            log_security_event("ACCOUNT_LOCKED", "User: $username");
                        } else {
                            $remaining = $max_attempts - $failed_attempts;
                            $error = "❌ Username atau password salah. Sisa percobaan: $remaining.";
                            log_security_event("LOGIN_FAILED", "User: $username, Attempts: $failed_attempts");
                        }

                        $stmt = $koneksi->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
                        $stmt->execute([$failed_attempts, $locked_until, $user['id']]);
                    }
                } else {
                    $error = "❌ Username atau password salah.";
                    log_security_event("LOGIN_INVALID_USER", "Username: $username");
                }
            }
        }
    } catch (Exception $e) {
        $error = "❌ Terjadi kesalahan sistem.";
        log_security_event("LOGIN_ERROR", $e->getMessage());
    }
}

/* =========================
   PROSES REGISTER
========================= */
if (isset($_POST['register'])) {
    try {
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            $error = "❌ Token CSRF tidak valid.";
            log_security_event("CSRF_INVALID", "Register attempt");
            $_SESSION['csrf_token'] = generate_csrf_token();
        } else {
            $_SESSION['csrf_token'] = generate_csrf_token();

            $username = sanitize_input($_POST['reg_username']);
            $email = sanitize_input($_POST['reg_email']);
            $password = $_POST['reg_password'];
            $confirm_password = $_POST['confirm_password'];

            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "❌ Semua field harus diisi.";
            } elseif (!validate_username($username)) {
                $error = "❌ Username hanya boleh huruf, angka, dan underscore (3-20 karakter).";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "❌ Format email tidak valid.";
            } elseif (!validate_password($password)) {
                $error = "❌ Password minimal 8 karakter dengan huruf besar, kecil, dan angka.";
            } elseif ($password !== $confirm_password) {
                $error = "❌ Password dan konfirmasi tidak sama.";
            } else {
                $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$username, $email]);
                $existing_user = $stmt->fetch();

                if ($existing_user) {
                    $error = ($existing_user['username'] === $username) ? "❌ Username sudah digunakan." : "❌ Email sudah terdaftar.";
                    log_security_event("REGISTER_DUPLICATE", "Username: $username, Email: $email");
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $koneksi->prepare("INSERT INTO users (username, email, password, created_at, failed_attempts, locked_until) VALUES (?, ?, ?, NOW(), 0, NULL)");
                    if ($stmt->execute([$username, $email, $hashed_password])) {
                        $_SESSION['reg_success'] = "✅ Akun berhasil dibuat! Silakan login.";
                        log_security_event("REGISTER_SUCCESS", "User: $username");
                        header("Location: auth.php");
                        exit;
                    } else {
                        $error = "❌ Gagal membuat akun. Coba lagi.";
                        log_security_event("REGISTER_ERROR", "Database error for user: $username");
                    }
                }
            }
        }
    } catch (Exception $e) {
        $error = "❌ Terjadi kesalahan sistem.";
        log_security_event("REGISTER_ERROR", $e->getMessage());
    }
}
?>

<!-- ========================================
     HTML LOGIN & REGISTER FORM
======================================== -->
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login & Register - SAAZ</title>
<link rel="stylesheet" href="../assets/css/auth.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
.password-strength { margin-top: 5px; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden; transition: all 0.3s ease; }
.strength-bar { height: 100%; transition: all 0.3s ease; border-radius: 2px; }
.strength-weak { background: #ff4444; width: 25%; }
.strength-fair { background: #ffaa00; width: 50%; }
.strength-good { background: #00aa00; width: 75%; }
.strength-strong { background: #008800; width: 100%; }
.strength-text { font-size: 12px; margin-top: 2px; font-weight: 500; }
.message { animation: slideIn 0.3s ease; }
@keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
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

        <?php if ($error): ?>
            <div class="message error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <div class="tab-content active" id="login">
            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                           pattern="[a-zA-Z0-9_]{3,20}" required>
                </div>
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="submit-btn">Masuk →</button>
            </form>
        </div>

        <!-- Register Form -->
        <div class="tab-content" id="register">
            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="reg_username">👤 Username</label>
                    <input type="text" id="reg_username" name="reg_username" 
                           value="<?= isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : '' ?>" 
                           pattern="[a-zA-Z0-9_]{3,20}" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">📧 Email</label>
                    <input type="email" id="reg_email" name="reg_email" 
                           value="<?= isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">🔒 Password</label>
                    <input type="password" id="reg_password" name="reg_password" minlength="8" required>
                    <div class="password-strength" id="passwordStrength"><div class="strength-bar" id="strengthBar"></div></div>
                    <div class="strength-text" id="strengthText"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">🔒 Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
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
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabBtns.forEach(btn => btn.addEventListener('click', function() {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.getAttribute('data-tab')).classList.add('active');
    }));

    // Password strength checker
    const regPassword = document.getElementById('reg_password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');

    function checkPasswordStrength(password) {
        let strength = 0, feedback = [];
        if (password.length >= 8) strength++; else feedback.push('minimal 8 karakter');
        if (/[a-z]/.test(password)) strength++; else feedback.push('huruf kecil');
        if (/[A-Z]/.test(password)) strength++; else feedback.push('huruf besar');
        if (/[0-9]/.test(password)) strength++; else feedback.push('angka');
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
        return { strength, feedback };
    }

    if (regPassword) {
        regPassword.addEventListener('input', function() {
            const result = checkPasswordStrength(this.value);
            strengthBar.className = 'strength-bar';
            if (this.value.length === 0) {
                strengthBar.style.width = '0%'; strengthText.textContent = '';
            } else if (result.strength <= 2) {
                strengthBar.classList.add('strength-weak'); strengthText.textContent = 'Lemah - Butuh: ' + result.feedback.join(', ');
            } else if (result.strength === 3) {
                strengthBar.classList.add('strength-fair'); strengthText.textContent = 'Sedang - Butuh: ' + result.feedback.join(', ');
            } else if (result.strength === 4) {
                strengthBar.classList.add('strength-good'); strengthText.textContent = 'Bagus';
            } else {
                strengthBar.classList.add('strength-strong'); strengthText.textContent = 'Sangat Kuat';
            }
        });
    }

    // Auto-clear messages
    const messages = document.querySelectorAll('.message');
    messages.forEach(msg => setTimeout(() => msg.remove(), 5000));
});
</script>
</body>
</html>
