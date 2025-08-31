<?php 
session_start();
require_once "../config.php"; // file koneksi

// Kalau sudah login langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
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
   PROSES LOGIN
========================= */
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "❌ Username dan password tidak boleh kosong.";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // simpan session login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // Redirect ke dashboard di luar folder admin
            header("Location: ../dashboard.php");
            exit;
        } else {
            $error = "❌ Username atau password salah.";
        }
    }
}

/* =========================
   PROSES REGISTER
========================= */
if (isset($_POST['register'])) {
    $username         = trim($_POST['reg_username']);
    $email            = trim($_POST['reg_email']);
    $password         = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "❌ Semua field harus diisi.";
    } elseif (strlen($username) < 3) {
        $error = "❌ Username minimal 3 karakter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "❌ Password minimal 6 karakter.";
    } elseif ($password !== $confirm_password) {
        $error = "❌ Password dan konfirmasi tidak sama.";
    } else {
        // Cek user / email sudah ada
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
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
            // hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $koneksi->prepare($sql);
            
            if ($stmt->execute([$username, $email, $hashed_password])) {
                // simpan pesan sukses lalu redirect ke login
                $_SESSION['reg_success'] = "✅ Akun berhasil dibuat! Silakan login.";
                header("Location: auth.php");
                exit;
            } else {
                $error = "❌ Gagal membuat akun. Coba lagi.";
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
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="username">👤 Username</label>
                        <input type="text" id="username" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">🔒 Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="submit-btn">Masuk →</button>
                </form>
            </div>

            <!-- Form Register -->
            <div class="tab-content" id="register">
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="reg_username">👤 Username</label>
                        <input type="text" id="reg_username" name="reg_username" value="<?= isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : '' ?>" minlength="3" required>
                    </div>
                    <div class="form-group">
                        <label for="reg_email">📧 Email</label>
                        <input type="email" id="reg_email" name="reg_email" value="<?= isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="reg_password">🔒 Password</label>
                        <input type="password" id="reg_password" name="reg_password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">🔒 Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
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

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.getAttribute('data-tab')).classList.add('active');
        });
    });

    // Validasi password konfirmasi
    const regPassword = document.getElementById('reg_password');
    const confirmPassword = document.getElementById('confirm_password');
    if (regPassword && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            this.setCustomValidity(this.value !== regPassword.value ? 'Password tidak sama' : '');
        });
    }
});
</script>
</body>
</html>