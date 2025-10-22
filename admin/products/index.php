<?php
require '../autoload/session.php';
require '../autoload/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Lấy danh sách sản phẩm + ảnh đầu tiên
// Lấy danh sách sản phẩm (hiện tất cả, không lọc is_hidden)
$sql = "SELECT p.*, c.name as category_name,
        (SELECT pi.image 
         FROM product_images pi 
         WHERE pi.product_id = p.id 
         ORDER BY pi.is_main DESC, pi.id ASC
         LIMIT 1) as main_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
        LIMIT 6";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid mt-2">
        <h2>📦 Quản lý sản phẩm</h2>
        <a href="../index.php" class="btn btn-secondary mb-3">⬅ Quay lại Dashboard</a>
        <a href="add.php" class="btn btn-primary mb-3">➕ Thêm sản phẩm</a>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Hình ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Danh mục</th>
                    <th>Hot</th>
                    <th>Ẩn</th>
                    <th>Mô tả ngắn</th>
                    <th>Mô tả chi tiết</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?php echo ($row['quantity'] <= 0) ? 'table-danger' : ''; ?>">
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <?php if ($row['main_image']): ?>
                                    <img src="http://localhost/my_website/uploads/products/<?php echo htmlspecialchars($row['main_image']); ?>" width="80" style="object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted">Chưa có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['name']); ?>
                                <?php if ($row['quantity'] <= 0): ?>
                                    <br><span class="badge bg-danger mt-1">Hết hàng</span>
                                <?php elseif ($row['quantity'] == 1): ?>
                                    <br><span class="badge bg-warning text-dark mt-1">Còn 1 sản phẩm</span>

                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($row['price'], 0, ',', '.'); ?> ₫</td>

                            <td>
                                <input type="number"
                                    class="form-control form-control-sm w-50 update-quantity"
                                    data-product-id="<?php echo $row['id']; ?>"
                                    value="<?php echo $row['quantity']; ?>"
                                    min="0">
                            </td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td>
                                <a href="toggle_hot.php?id=<?php echo $row['id']; ?>"
                                    class="btn btn-sm <?php echo $row['is_hot'] ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                    <?php echo $row['is_hot'] ? '🔥 Hot' : '☆ Bình thường'; ?>
                                </a>
                            </td>
                            <td>
                                <a href="toggle_hidden.php?id=<?php echo $row['id']; ?>"
                                    class="btn btn-sm <?php echo $row['is_hidden'] ? 'btn-danger' : 'btn-success'; ?>">
                                    <?php echo $row['is_hidden'] ? '🚫 Ẩn' : '✔️ Hiện'; ?>
                                </a>
                            </td>
                            <td class="desc-cell">
                                <div class="desc-short">
                                    <?php echo htmlspecialchars($row['short_desc']); ?>
                                </div>
                            </td>
                            <td class="desc-cell">
                                <div class="desc-long">
                                    <?php echo nl2br(htmlspecialchars($row['long_desc'])); ?>
                                </div>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                <a href="edit_img.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">Ảnh chính</a>

                                <a href="delete.php?id=<?php echo $row['id']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Bạn có chắc muốn xoá sản phẩm này?');">
                                    Xoá
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center">Chưa có sản phẩm nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.update-quantity').on('change', function() {
                const productId = $(this).data('product-id');
                const quantity = $(this).val();

                $.ajax({
                    url: 'update_quantity.php',
                    type: 'POST',
                    data: {
                        product_id: productId,
                        quantity: quantity
                    },
                    success: function(response) {
                        // Không hiển thị thông báo, xử lý trực tiếp
                    },
                    error: function() {
                        console.error('Có lỗi xảy ra khi cập nhật số lượng.');
                    }
                });
            });
        });
        $(document).ready(function() {
            $('table').on('click', '.desc-toggle', function() {
                const cell = $(this).closest('.desc-cell');
                cell.toggleClass('expanded');
                $(this).text(cell.hasClass('expanded') ? 'Thu gọn' : 'Xem thêm');
            });

            // Tự động thêm nút "Xem thêm" cho ô mô tả dài
            $('.desc-cell div').each(function() {
                if ($(this).text().length > 80) { // dài hơn 80 ký tự
                    $(this).after('<span class="desc-toggle">Xem thêm</span>');
                }
            });
        });
    </script>
    <style>
        .desc-cell {
            max-width: 250px;
            position: relative;
        }

        .desc-short,
        .desc-long {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* chỉ hiển thị 2 dòng */
            -webkit-box-orient: vertical;
            text-overflow: ellipsis;
            white-space: normal;
        }

        .desc-cell.expanded .desc-short,
        .desc-cell.expanded .desc-long {
            -webkit-line-clamp: unset;
            max-height: none;
        }

        .desc-toggle {
            color: #007bff;
            cursor: pointer;
            font-size: 0.85rem;
            display: block;
            margin-top: 3px;
        }
    </style>

</body>

</html>