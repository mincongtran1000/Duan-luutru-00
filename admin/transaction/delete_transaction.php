<?php
require '../autoload/session.php';
require '../autoload/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = intval($_POST['transaction_id']);

    // Xóa các đơn hàng liên quan đến giao dịch
    $query_orders = "DELETE FROM orders WHERE transaction_id = ?";
    $stmt_orders = $conn->prepare($query_orders);
    $stmt_orders->bind_param("i", $transaction_id);
    $stmt_orders->execute();

    // Xóa giao dịch
    $query_transaction = "DELETE FROM transactions WHERE id = ?";
    $stmt_transaction = $conn->prepare($query_transaction);
    $stmt_transaction->bind_param("i", $transaction_id);

    if ($stmt_transaction->execute()) {
        header("Location: index.php?success=transaction_deleted");
        exit;
    } else {
        header("Location: index.php?error=delete_failed");
        exit;
    }
}
