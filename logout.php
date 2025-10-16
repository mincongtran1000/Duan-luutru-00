<?php
include 'includes/session.php';
session_start();

// Xóa toàn bộ session liên quan đến user
unset($_SESSION['username']);
unset($_SESSION['user_id']);
unset($_SESSION['user_logged_in']);

// Xóa cookie nếu có
setcookie("username", "", time() - 3600, "/");

// Chuyển hướng về trang chủ
header("Location: index.php");
exit;
