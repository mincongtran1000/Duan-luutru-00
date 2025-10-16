<?php
// includes/session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// nếu session đã mất nhưng cookie còn -> phục hồi session từ cookie (tuỳ bạn có muốn)
if (!isset($_SESSION['username']) && isset($_COOKIE['username'])) {
    // (nếu bạn tin cookie là an toàn để restore)
    $_SESSION['username'] = $_COOKIE['username'];
}
// Ngăn cache để không bị back
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
