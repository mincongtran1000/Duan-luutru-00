<?php
// Kiểm tra xem session đã khởi tạo chưa, tránh lỗi trùng
if (session_status() === PHP_SESSION_NONE) {

    // ⚙️ Thiết lập session cookie — chỉ sống khi trình duyệt mở
    session_cache_limiter('nocache,private');
    session_set_cookie_params([
        'lifetime' => 0, // 0 = hết khi tắt trình duyệt
        'path' => '/',
        'domain' => '', // để trống nếu dùng localhost
        'secure' => false, // true nếu website dùng HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Bắt đầu session
    session_start();
}
