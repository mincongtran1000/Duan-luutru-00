<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';
require 'includes/navbar.php';
if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để xem giỏ hàng.");
}

$user_id = intval($_SESSION['user_id']);

// Lấy danh sách giao dịch của người dùng
$query = "
    SELECT o.product_id, SUM(o.quantity) AS total_quantity, SUM(o.price * o.quantity) AS total_price,
           p.name AS product_name, c.name AS category_name,
           (SELECT pi.image 
            FROM product_images pi 
            WHERE pi.product_id = p.id AND pi.is_main = 1 
            LIMIT 1) AS main_image
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN categories c ON o.category_id = c.id
    WHERE o.transaction_id IN (
        SELECT id FROM transactions WHERE user_id = $user_id AND status = 'pending'
    )
    GROUP BY o.product_id
    ORDER BY p.name ASC
";
$result = $conn->query($query);
?>
<div class="container my-4">
    <h2 class="mb-4">Giỏ hàng của bạn</h2>
    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Sản phẩm</th>
                    <th>Phân loại</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Thành tiền</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['main_image'])): ?>
                                <img src="http://localhost/my_website/uploads/products/<?php echo htmlspecialchars($row['main_image']); ?>"
                                    alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <img src="http://localhost/my_website/uploads/no-image.png"
                                    alt="No image"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo $row['total_quantity']; ?></td>
                        <td><?php echo number_format($row['total_price'] / $row['total_quantity'], 0, ',', '.'); ?> đ</td>
                        <td><?php echo number_format($row['total_price'], 0, ',', '.'); ?> đ</td>
                        <td>
                            <form action="remove_from_cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <form action="checkout.php" method="POST">
            <button type="submit" class="btn btn-success btn-lg mt-3">Thanh toán</button>
        </form>
    <?php else: ?>
        <p class="text-muted">Giỏ hàng của bạn đang trống.</p>
    <?php endif; ?>
</div>