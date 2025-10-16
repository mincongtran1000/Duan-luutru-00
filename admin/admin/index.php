<?php
session_start();
require '../autoload/db.php';

// Nếu có yêu cầu xoá
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Không cho xoá tài khoản mặc định admin
    $stmt = $conn->prepare("SELECT username FROM admin WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        if ($res['username'] === 'admin') {
            $_SESSION['error_message'] = "Không thể xoá tài khoản hệ thống!";
        } else {
            $stmt = $conn->prepare("DELETE FROM admin WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Xoá admin thành công!";
            } else {
                $_SESSION['error_message'] = "Xoá thất bại!";
            }
        }
    } else {
        $_SESSION['error_message'] = "Admin không tồn tại!";
    }

    header("Location: index.php");
    exit;
}

// Lấy danh sách admin
$result = $conn->query("SELECT id, username, email, created_at FROM admin");
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Quản lý Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
    <div class="container">
        <a href="../index.php" class="btn btn-secondary mb-3">⬅ Quay lại Dashboard</a>
        <h2>Danh sách Admin</h2>

        <!-- Hiển thị thông báo -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td>
                            <?= htmlspecialchars($row['username']) ?>
                            <?php if ($row['username'] === 'admin'): ?>
                                <span class="badge bg-danger">Hệ Thống</span>
                            <?php else: ?>
                                <span class="badge bg-primary">QTV</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <?php if ($row['username'] !== 'admin'): ?>
                                <a href="?delete=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Bạn có chắc muốn xoá admin này?')">Xoá</a>
                            <?php else: ?>
                                <span class="text-muted">Không thể xoá</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>