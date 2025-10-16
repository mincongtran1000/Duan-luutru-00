<?php
require '../autoload/session.php';
require '../autoload/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Không cho xóa user admin mặc định
    $check = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if ($row['username'] === 'admin') {
            $_SESSION['error_message'] = "Không thể xóa tài khoản admin mặc định!";
            header("Location: index.php");
            exit;
        }
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Xóa user thành công!";
    } else {
        $_SESSION['error_message'] = "Xóa thất bại!";
    }
}

header("Location: index.php");
exit;
