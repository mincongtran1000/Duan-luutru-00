<?php
// includes/session.php

if (session_status() === PHP_SESSION_NONE) {
    // Cho session tự xoá khi tắt trình duyệt
    ini_set('session.cookie_lifetime', 0);

    // Cho session hết hạn sau 30 phút (1800 giây) không hoạt động
    ini_set('session.gc_maxlifetime', 1800);
    session_set_cookie_params(0);

    session_start();
}

// Kiểm tra nếu người dùng không hoạt động quá lâu -> tự động đăng xuất
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // Quá 30 phút không hoạt động -> huỷ session
    session_unset();
    session_destroy();
    header("Location: logout.php"); // hoặc về login.php tuỳ bạn
    exit;
}

// Cập nhật lại thời gian hoạt động cuối cùng
$_SESSION['LAST_ACTIVITY'] = time();

// Không dùng cookie restore lại session nữa
// (bỏ hoàn toàn phần khôi phục từ cookie để tránh tự giữ đăng nhập)

// Ngăn cache để không bị back
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
