<?php
require 'includes/session.php';
require 'includes/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Lấy danh sách sản phẩm + ảnh chính
$sql = "
    SELECT p.*, c.name AS category_name, pi.image AS main_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
            WHERE p.is_hidden = 0
            ORDER BY p.created_at DESC
";
$result = $conn->query($sql);
?>

<div class="container my-4">
    <h2 class="mb-4">Danh sách sản phẩm</h2>
    <div class="row g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-3">
                    <div class="card h-100 shadow-sm">
                        <!-- Ô hiển thị ảnh cố định -->
                        <div style="width:100%; height:300px; display:flex; align-items:center; justify-content:center; background:#f9f9f9;">
                            <?php if (!empty($row['main_image'])): ?>
                                <img src="uploads/products/<?php echo htmlspecialchars($row['main_image']); ?>"
                                    alt="<?php echo htmlspecialchars($row['name']); ?>"
                                    style="max-width:100%; max-height:100%; object-fit:contain;">
                            <?php else: ?>
                                <img src="uploads/no-image.png"
                                    alt="No image"
                                    style="max-width:100%; max-height:100%; object-fit:contain;">
                            <?php endif; ?>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($row['name']); ?>
                                <?php if ($row['is_hot']): ?>
                                    <span class="badge bg-danger">HOT</span>
                                <?php endif; ?>
                            </h5>
                            <h6 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h6>
                            <p class="card-text text-muted"><?php echo number_format($row['price']); ?> đ</p>

                            <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Xem chi tiết</a>
                            <form action="add_to_cart.php" method="POST" class="mt-2">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-success">Thêm vào giỏ hàng</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-muted">Chưa có sản phẩm nào.</div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>