<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để thực hiện thao tác này.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $user_id = intval($_SESSION['user_id']);

    // Xóa sản phẩm khỏi giỏ hàng
    $query = "
        DELETE FROM orders 
        WHERE product_id = ? AND transaction_id IN (
            SELECT id FROM transactions WHERE user_id = ? AND status = 'pending'
        )
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();

    // Kiểm tra xem giao dịch có còn sản phẩm nào không
    $check_query = "
        SELECT COUNT(*) AS total_items 
        FROM orders 
        WHERE transaction_id IN (
            SELECT id FROM transactions WHERE user_id = ? AND status = 'pending'
        )
    ";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();

    if ($check_row['total_items'] == 0) {
        // Xóa giao dịch nếu không còn sản phẩm nào
        $delete_transaction_query = "
            DELETE FROM transactions 
            WHERE user_id = ? AND status = 'pending'
        ";
        $delete_transaction_stmt = $conn->prepare($delete_transaction_query);
        $delete_transaction_stmt->bind_param("i", $user_id);
        $delete_transaction_stmt->execute();
    }

    // Chuyển hướng về trang giỏ hàng
    header("Location: cart.php");
    exit;
}
