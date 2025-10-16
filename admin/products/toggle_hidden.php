<?php
require '../autoload/session.php';
require '../autoload/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Lấy trạng thái hiện tại
    $res = $conn->query("SELECT is_hidden FROM products WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $new_status = $row['is_hidden'] ? 0 : 1;

        $stmt = $conn->prepare("UPDATE products SET is_hidden=? WHERE id=?");
        $stmt->bind_param("ii", $new_status, $id);
        $stmt->execute();
    }
}
header("Location: index.php");
exit;
