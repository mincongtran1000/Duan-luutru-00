<?php
// require 'layouts/session.php';

// Kiểm tra admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #212529;
            color: #fff;
        }

        .sidebar a {
            color: #adb5bd;
            display: block;
            padding: 12px;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #343a40;
            color: #fff;
        }

        .content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h3 class="p-3 text-center border-bottom">Admin</h3>
        <a href="../index.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="../users/index.php"><i class="fa-solid fa-users"></i> Quản lý người dùng</a>
        <a href="../products/index.php"><i class="fa-solid fa-box"></i> Quản lý sản phẩm</a>
        <a href=""><i class="fa-solid fa-money-bill"></i> Quản lý giao dịch</a>
        <a href="../logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
    </div>
    <div class="content">