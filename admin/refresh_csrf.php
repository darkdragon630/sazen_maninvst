<?php
// refresh_csrf.php
session_start();
header('Content-Type: application/json');

// Generate new CSRF token
function generate_csrf_token() {
    return bin2hex(random_bytes(32));
}

// Logging function
function log_security_event($event, $details = '') {
    $log_entry = date('Y-m-d H:i:s') . " | " . $_SERVER['REMOTE_ADDR'] . " | $event | $details" . PHP_EOL;
    error_log($log_entry, 3, "../logs/security.log");
}

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Rate limiting - max 10 requests per minute per IP
    $ip = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    $rate_limit_key = "csrf_refresh_$ip";
    
    if (!isset($_SESSION[$rate_limit_key])) {
        $_SESSION[$rate_limit_key] = ['count' => 0, 'time' => $current_time];
    }
    
    $rate_data = $_SESSION[$rate_limit_key];
    
    // Reset counter if more than 1 minute has passed
    if ($current_time - $rate_data['time'] > 60) {
        $_SESSION[$rate_limit_key] = ['count' => 1, 'time' => $current_time];
    } else {
        $_SESSION[$rate_limit_key]['count']++;
        
        if ($_SESSION[$rate_limit_key]['count'] > 10) {
            log_security_event("CSRF_REFRESH_RATE_LIMIT", "IP: $ip");
            http_response_code(429);
            echo json_encode(['error' => 'Rate limit exceeded']);
            exit;
        }
    }

    // Generate new token
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