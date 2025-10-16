<?php
require '../autoload/session.php';
require '../autoload/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = intval($_POST['transaction_id']);
    $status = $_POST['status'];

    // Kiểm tra trạng thái hợp lệ
    if ($status === 'cancelled') {
        // Cập nhật trạng thái giao dịch thành "cancelled"
        $query = "UPDATE transactions SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $transaction_id);

        if ($stmt->execute()) {
            header("Location: index.php?success=cancelled");
            exit;
        } else {
            header("Location: index.php?error=update_failed");
            exit;
        }
    } else {
        header("Location: index.php?error=invalid_status");
        exit;
    }
}
?>