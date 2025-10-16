<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để thực hiện thanh toán.");
}

$user_id = intval($_SESSION['user_id']);

// Cập nhật trạng thái giao dịch từ 'pending' thành 'completed'
$query = "UPDATE transactions SET status = 'completed' WHERE user_id = ? AND status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    header("Location: confirmation.php");
    exit;
} else {
    die("Có lỗi xảy ra khi thanh toán.");
}
