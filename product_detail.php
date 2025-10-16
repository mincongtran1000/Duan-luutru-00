<?php
require 'includes/session.php';
require 'includes/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT p.*, pi.image AS main_image
        FROM products p
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

?>

<div class="container mt-5">
    <?php if ($product): ?>
        <div class="row">
            <div class="col-md-6">
                <div style="width:350px; height:350px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center; background:#f9f9f9;">
                    <?php if (!empty($product['main_image'])): ?>
                        <img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style="max-width:100%; max-height:100%; object-fit:cover;">
                    <?php else: ?>
                        <img src="uploads/no-image.png"
                            alt="No image"
                            style="max-width:100%; max-height:100%; object-fit:cover;">
                    <?php endif; ?>
                </div>
            </div>
            <<div class="col-md-6">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <?php if ($product['is_hot']): ?>
                    <span class="badge bg-danger">HOT</span>
                <?php endif; ?>
                <p class="text-muted"><?php echo htmlspecialchars($product['short_desc']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($product['long_desc'])); ?></p>
                <h4 class="text-primary"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</h4>
                <?php if ($product['quantity'] > 0): ?>
                    <span class="badge bg-success">Còn hàng</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Hết hàng</span>
                <?php endif; ?>
                <form action="add_to_cart.php" method="POST" class="mt-3">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <label for="quantity">Số lượng:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="form-control w-25">
                    <button type="submit" class="btn btn-success mt-2">Thêm vào giỏ hàng</button>
                </form>
        </div>
</div>
<?php else: ?>
    <p class="text-center text-danger">Sản phẩm không tồn tại.</p>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>