<?php
require 'session.php';
// require 'layouts/header.php';
require '../includes/db.php';
// Chỉ cho admin đã đăng nhập vào
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h1>Trang quản trị</h1>
        <p>Xin chào <strong>Admin</strong>!</p>

        <div class="row">
            <div class="col-md-4">
                <div class="card text-bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">👥 Quản trị thông tin </h5>
                        <p class="card-text">Quản lý tài khoản QTV</p>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Xem chi tiết
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admin/index.php">Trang Admin</a></li>
                                <li><a class="dropdown-item" href="users/index.php">Trang Users</a></li>
                            </ul>
                        </div>
                        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">📦 Sản phẩm</h5>
                        <p class="card-text">Thêm, sửa, xoá sản phẩm.</p>
                        <a href="products/index.php" class="btn btn-light btn-sm">Xem chi tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Giao dịch</h5>
                        <p class="card-text">xem chi tiết giao dịch</p>
                        <a href="transaction/index.php" class="btn btn-light btn-sm">Xem chi tiết</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container my-4">
            <h2 class="mb-4">Danh sách giao dịch</h2>
            <?php
            $sql = "
            SELECT t.id AS transaction_id, t.user_id, u.username, t.total_amount, t.status, t.created_at
            FROM transactions t
            LEFT JOIN users u ON t.user_id = u.id
            ORDER BY t.created_at DESC
            ";
            $result = $conn->query($sql);
            ?>
            <?php if ($result && $result->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người dùng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['transaction_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo number_format($row['total_amount'], 0, ',', '.'); ?> đ</td>
                                <td>
                                    <span class="badge bg-<?php echo $row['status'] === 'completed' ? 'success' : ($row['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary view-detail" data-id="<?php echo $row['transaction_id']; ?>">
                                        <i class="fa-solid fa-eye"></i> Chi tiết
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Không có giao dịch nào.</p>
            <?php endif; ?>
        </div>

        <a href="logout.php" class="btn btn-danger mt-3">Đăng xuất</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel">Chi tiết giao dịch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div id="transactionDetailContent" class="p-2 text-center text-muted">
                        Đang tải dữ liệu...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.view-detail').on('click', function() {
                const id = $(this).data('id');
                $('#transactionDetailContent').html('<div class="text-center p-3 text-muted">Đang tải dữ liệu...</div>');

                $.ajax({
                    url: 'transaction/transaction_detail_ajax.php',
                    type: 'GET',
                    data: {
                        id: id
                    },
                    success: function(response) {
                        $('#transactionDetailContent').html(response);
                        $('#transactionModal').modal('show');
                    },
                    error: function() {
                        $('#transactionDetailContent').html('<div class="text-danger">Lỗi tải dữ liệu</div>');
                    }
                });
            });
        });
    </script>


</body>

</html>