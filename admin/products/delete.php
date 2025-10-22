<?php
require '../../includes/session.php';
require '../../includes/db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Kiểm tra sản phẩm có liên quan đơn hàng
    $check = $conn->prepare("SELECT COUNT(*) FROM orders WHERE product_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        die("⚠️ Không thể xoá sản phẩm này vì đã có đơn hàng liên quan.");
    }

    // Lấy hình ảnh từ bảng product_images
    $stmt = $conn->prepare("SELECT image FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['image'])) {
            $imagePath = "../../uploads/products/" . $row['image'];
            if (file_exists($imagePath) && is_file($imagePath)) {
                unlink($imagePath); // Xóa file hình ảnh khỏi thư mục
            }
        }
    }
    $stmt->close();

    // Xóa hình ảnh trong bảng product_images
    $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Xóa sản phẩm trong bảng products
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php?status=deleted");
exit;
