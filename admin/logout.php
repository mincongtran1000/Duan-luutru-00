<?php
require '../includes/session.php';
session_name("ADMINSESSID");
session_start();

// Chỉ xóa session liên quan tới admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_id']);

// Không dùng session_destroy()
// Không reset $_SESSION = [] hoặc session_unset()

// Tùy chọn: nếu bạn set cookie riêng cho admin session thì có thể xóa cookie đó ở đây

// Chuyển hướng về trang đăng nhập admin
header("Location: login.php");
exit;
