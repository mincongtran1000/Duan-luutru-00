<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $user_id = intval($_SESSION['user_id']);

    // Kiểm tra xem giao dịch "pending" đã tồn tại chưa
    $query = "SELECT id FROM transactions WHERE user_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu giao dịch "pending" đã tồn tại, lấy transaction_id
        $transaction = $result->fetch_assoc();
        $transaction_id = $transaction['id'];
    } else {
        // Nếu chưa có giao dịch "pending", tạo giao dịch mới
        $query = "INSERT INTO transactions (user_id, total_amount, status) VALUES (?, 0, 'pending')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $transaction_id = $stmt->insert_id;
    }

    // Thêm sản phẩm vào đơn hàng
    $query = "INSERT INTO orders (transaction_id, product_id, quantity, price, category_id) 
              VALUES (?, ?, ?, (SELECT price FROM products WHERE id = ?), (SELECT category_id FROM products WHERE id = ?))";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $transaction_id, $product_id, $quantity, $product_id, $product_id);
    $stmt->execute();

    // Cập nhật tổng tiền giao dịch
    $query = "UPDATE transactions SET total_amount = total_amount + (SELECT price FROM products WHERE id = ?) * ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $product_id, $quantity, $transaction_id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>
