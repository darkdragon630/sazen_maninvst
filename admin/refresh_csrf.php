<?php
// refresh_csrf.php
session_start();
header('Content-Type: application/json');

/* =========================
   FUNGSIONAL CSRF
========================= */
function generate_csrf_token() {
    return bin2hex(random_bytes(32)); // 32-byte = 64 hex chars
}

/* =========================
   LOGGING SECURITY
========================= */
function log_security_event($event, $details = '') {
    $log_file = __DIR__ . "/../logs/security.log";
    $log_entry = date('Y-m-d H:i:s') . " | " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN') . " | $event | $details" . PHP_EOL;
    error_log($log_entry, 3, $log_file);
}

try {
    // Hanya POST request yang diizinkan
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        log_security_event("CSRF_REFRESH_METHOD_NOT_ALLOWED", "Method: {$_SERVER['REQUEST_METHOD']}");
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    /* =========================
       RATE LIMITING PER IP
    ========================= */
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $current_time = time();
    $rate_limit_key = "csrf_refresh_$ip";

    if (!isset($_SESSION[$rate_limit_key])) {
        $_SESSION[$rate_limit_key] = ['count' => 0, 'time' => $current_time];
    }

    $rate_data = $_SESSION[$rate_limit_key];

    // Reset counter jika lebih dari 60 detik
    if ($current_time - $rate_data['time'] > 60) {
        $_SESSION[$rate_limit_key] = ['count' => 1, 'time' => $current_time];
    } else {
        $_SESSION[$rate_limit_key]['count']++;
        if ($_SESSION[$rate_limit_key]['count'] > 10) {
            log_security_event("CSRF_REFRESH_RATE_LIMIT", "IP: $ip");
            http_response_code(429);
            echo json_encode(['error' => 'Rate limit exceeded. Coba lagi nanti.']);
            exit;
        }
    }

    /* =========================
       GENERATE TOKEN BARU
    ========================= */
    $_SESSION['csrf_token'] = generate_csrf_token();
    log_security_event("CSRF_TOKEN_REFRESHED", "IP: $ip");

    echo json_encode([
        'success' => true,
        'token' => $_SESSION['csrf_token']
    ]);

} catch (Exception $e) {
    log_security_event("CSRF_REFRESH_ERROR", $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
