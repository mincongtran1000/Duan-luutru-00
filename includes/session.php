<?php
if (session_status() === PHP_SESSION_NONE) {
    session_cache_limiter('nocache,private');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false, // true nếu dùng HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// ⏰ Giới hạn phiên 30 phút
$timeout = 1800; // 30 phút

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

$_SESSION['last_activity'] = time();
