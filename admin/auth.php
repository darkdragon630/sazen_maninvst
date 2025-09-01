<?php
session_start();
require_once "../config.php";

session_regenerate_id(true);

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Variabel pesan
$error = "";
$success = "";

// Pesan sukses register
if (isset($_SESSION['reg_success'])) {
    $success = $_SESSION['reg_success'];
    unset($_SESSION['reg_success']);
}

// Fungsi rotasi CSRF
function rotate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================
   Fungsi Validasi Input
========================= */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function validate_password($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

/* =========================
   PROSES LOGIN DENGAN LOCK
========================= */
if (isset($_POST['login'])) {
    try {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "❌ Token CSRF tidak valid.";
            rotate_csrf_token();
        } else {
            rotate_csrf_token();

            $username = sanitize($_POST['username']);
            $password = $_POST['password'];

            if (!validate_username($username)) {
                $error = "❌ Username tidak valid (3-20 karakter, huruf, angka, underscore).";
            } elseif (empty($password)) {
                $error = "❌ Password tidak boleh kosong.";
            } else {
                $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user) {
                    $current_time = new DateTime();

                    // Cek akun terkunci
                    if ($user['locked_until'] && $current_time < new DateTime($user['locked_until'])) {
                        $error = "❌ Akun terkunci sampai " . date('H:i:s d-m-Y', strtotime($user['locked_until'])) . ".";
                    } elseif (password_verify($password, $user['password'])) {
                        // reset failed attempts
                        $stmt = $koneksi->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
                        $stmt->execute([$user['id']]);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];

                        header("Location: ../dashboard.php");
                        exit;
                    } else {
                        // increment failed attempts
                        $failed_attempts = $user['failed_attempts'] + 1;
                        $locked_until = null;
                        $max_attempts = 5;

                        if ($failed_attempts >= $max_attempts) {
                            $locked_until = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');
                            $error = "❌ Akun terkunci 15 menit karena $max_attempts kali gagal login.";
                            $failed_attempts = 0;
                        } else {
                            $remaining = $max_attempts - $failed_attempts;
                            $error = "❌ Username atau password salah. Sisa percobaan: $remaining.";
                        }

                        $stmt = $koneksi->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
                        $stmt->execute([$failed_attempts, $locked_until, $user['id']]);
                    }
                } else {
                    $error = "❌ Username atau password salah.";
                }
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = "❌ Terjadi kesalahan server. Coba lagi nanti.";
    }
}

/* =========================
   PROSES REGISTER
========================= */
if (isset($_POST['register'])) {
    try {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "❌ Token CSRF tidak valid.";
            rotate_csrf_token();
        } else {
            rotate_csrf_token();

            $username = sanitize($_POST['reg_username']);
            $email = sanitize($_POST['reg_email']);
            $password = $_POST['reg_password'];
            $confirm_password = $_POST['confirm_password'];

            if (!validate_username($username)) {
                $error = "❌ Username tidak valid (3-20 karakter, huruf, angka, underscore).";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "❌ Email tidak valid.";
            } elseif (!validate_password($password)) {
                $error = "❌ Password minimal 8 karakter, kombinasi huruf besar/kecil dan angka.";
            } elseif ($password !== $confirm_password) {
                $error = "❌ Password dan konfirmasi tidak sama.";
            } else {
                $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$username, $email]);
                $existing_user = $stmt->fetch();

                if ($existing_user) {
                    if ($existing_user['username'] === $username) $error = "❌ Username sudah digunakan.";
                    else $error = "❌ Email sudah terdaftar.";
                } else {
                    $options = ['memory_cost' => 1<<17, 'time_cost' => 4, 'threads' => 3];
                    $hashed_password = password_hash($password, PASSWORD_ARGON2ID, $options);

                    $stmt = $koneksi->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
                    if ($stmt->execute([$username, $email, $hashed_password])) {
                        $_SESSION['reg_success'] = "✅ Akun berhasil dibuat! Silakan login.";
                        header("Location: auth.php");
                        exit;
                    } else {
                        $error = "❌ Gagal membuat akun. Coba lagi.";
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = "❌ Terjadi kesalahan server. Coba lagi nanti.";
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
<style>
/* Password strength indicator */
.strength {
    height: 5px;
    width: 100%;
    background: #ddd;
    margin-top: 5px;
}
.strength-bar {
    height: 100%;
    width: 0%;
    background: linear-gradient(to right, red, yellow, green);
}
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

        <div class="tab-content active" id="login">
            <form method="POST" class="auth-form" onsubmit="this.querySelector('button').disabled=true;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" pattern="[a-zA-Z0-9_]{3,20}" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="submit-btn">Masuk →</button>
            </form>
        </div>

        <div class="tab-content" id="register">
            <form method="POST" class="auth-form" onsubmit="this.querySelector('button').disabled=true;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="reg_username">👤 Username</label>
                    <input type="text" id="reg_username" name="reg_username" pattern="[a-zA-Z0-9_]{3,20}" value="<?= isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">📧 Email</label>
                    <input type="email" id="reg_email" name="reg_email" value="<?= isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">🔒 Password</label>
                    <input type="password" id="reg_password" name="reg_password" required>
                    <div class="strength"><div class="strength-bar" id="strength-bar"></div></div>
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
// Tab navigation
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabBtns.forEach(btn => btn.addEventListener('click', function() {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.getAttribute('data-tab')).classList.add('active');
    }));

    // Password confirmation
    const regPassword = document.getElementById('reg_password');
    const confirmPassword = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('strength-bar');

    if (regPassword && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            this.setCustomValidity(this.value !== regPassword.value ? 'Password tidak sama' : '');
        });

        // Real-time password strength
        regPassword.addEventListener('input', function() {
            let val = regPassword.value;
            let strength = 0;
            if (val.length >= 8) strength += 1;
            if (/[A-Z]/.test(val)) strength += 1;
            if (/[a-z]/.test(val)) strength += 1;
            if (/\d/.test(val)) strength += 1;
            strengthBar.style.width = (strength/4*100)+'%';
        });
    }

    // Clear sensitive data saat unload
    window.addEventListener('beforeunload', function() {
        if(regPassword) regPassword.value = '';
        if(confirmPassword) confirmPassword.value = '';
        if(document.getElementById('password')) document.getElementById('password').value = '';
    });
});
</script>
</body>
</html>
