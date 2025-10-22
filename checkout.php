<?php
session_start();
require 'includes/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để thanh toán.");
}

$user_id = intval($_SESSION['user_id']);

// 2. Tìm đơn hàng đang chờ thanh toán (pending hoặc trống)
$query = "SELECT id FROM transactions 
          WHERE user_id = ? 
          AND (status = 'pending' OR status IS NULL OR status = '') 
          ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Không có đơn hàng nào để thanh toán.");
}

$transaction = $result->fetch_assoc();
$transaction_id = $transaction['id'];

// 3. Cập nhật trạng thái thành completed
$query_update = "UPDATE transactions SET status = 'completed' WHERE id = ?";
$stmt_update = $conn->prepare($query_update);
$stmt_update->bind_param("i", $transaction_id);

if ($stmt_update->execute()) {
    // 4. Chuyển hướng sau khi cập nhật thành công
    header("Location: success.php");
    exit;
} else {
    die("Có lỗi xảy ra khi cập nhật trạng thái: " . $stmt_update->error);
}
