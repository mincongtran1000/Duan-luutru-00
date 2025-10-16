<?php
require '../autoload/session.php';
require '../autoload/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

$image_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($image_id && $product_id) {
    // Lấy thông tin ảnh
    $stmt = $conn->prepare("SELECT image FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->bind_param("ii", $image_id, $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $img = $res->fetch_assoc();

    if ($img) {
        // Xóa file trong uploads
        $filePath = "../uploads/" . $img['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Xóa trong DB
        $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
        $stmt->bind_param("ii", $image_id, $product_id);
        $stmt->execute();
    }
}

header("Location: edit_img.php?id=$product_id");
exit;
