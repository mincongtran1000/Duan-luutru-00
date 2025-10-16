<?php
require '../autoload/session.php';
require '../autoload/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['product_id'], $_GET['image_id'])) {
    $product_id = intval($_GET['product_id']);
    $image_id   = intval($_GET['image_id']);

    // Reset
    $conn->query("UPDATE product_images SET is_main = 0 WHERE product_id = $product_id");

    // Set ảnh chính
    $conn->query("UPDATE product_images SET is_main = 1 WHERE id = $image_id AND product_id = $product_id");
}

header("Location: edit_img.php?id=$product_id");
exit;
