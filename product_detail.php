<?php
require 'includes/session.php';
require 'includes/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy 2 sản phẩm mới nhất
$newest_ids = [];
$newest_result = $conn->query("SELECT id FROM products ORDER BY created_at DESC LIMIT 2");
while ($r = $newest_result->fetch_assoc()) {
    $newest_ids[] = $r['id'];
}

// Lấy thông tin sản phẩm
$sql = "SELECT p.*, pi.image AS main_image
        FROM products p
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Xác định có phải sản phẩm mới không
$is_new = $product ? in_array($product['id'], $newest_ids) : false;
?>
<div class="container mt-5">
    <?php if ($product): ?>
        <div class="text-center mb-4">
            <h2 class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></h2>

            <div class="d-flex justify-content-center gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#shortDescModal">
                    Mô tả ngắn
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#longDescModal">
                    Mô tả chi tiết
                </button>
            </div>
        </div>

        <div class="product-view">
            <!-- ẢNH CHÍNH -->
            <div class="main-image-box position-relative border bg-white">
                <?php if ($product['is_hot']): ?>
                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">HOT</span>
                <?php endif; ?>
                <?php if ($is_new): ?>
                    <span class="badge bg-info text-dark position-absolute top-0 end-0 m-2">NEW</span>
                <?php endif; ?>

                <img id="mainImage"
                    src="uploads/products/<?php echo !empty($product['main_image']) ? htmlspecialchars($product['main_image']) : 'no-image.png'; ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                    class="img-fluid main-image">
            </div>

            <!-- ẢNH PHỤ -->
            <?php
            $imgs = $conn->prepare("SELECT image FROM product_images WHERE product_id = ? AND (is_main IS NULL OR is_main = 0)");
            $imgs->bind_param("i", $id);
            $imgs->execute();
            $images = $imgs->get_result();

            $all_images = [];
            if (!empty($product['main_image'])) {
                $all_images[] = $product['main_image'];
            }
            while ($img = $images->fetch_assoc()) {
                $all_images[] = $img['image'];
            }
            ?>

            <?php if (count($all_images) > 1): ?>
                <div class="thumbs mt-4 d-flex flex-wrap justify-content-center gap-3">
                    <?php foreach ($all_images as $img): ?>
                        <img src="uploads/products/<?php echo htmlspecialchars($img); ?>"
                            alt="Ảnh phụ"
                            class="thumb-img"
                            onclick="document.getElementById('mainImage').src = this.src">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- GIÁ + GIỎ HÀNG -->
        <div class="text-center mt-5">
            <h4 class="text-primary fw-semibold">
                <?php echo number_format($product['price'], 0, ',', '.'); ?> đ
            </h4>

            <?php if ($product['quantity'] > 0): ?>
                <span class="badge bg-success">Còn hàng</span>
            <?php else: ?>
                <span class="badge bg-secondary">Hết hàng</span>
            <?php endif; ?>

            <form action="add_to_cart.php" method="POST" class="mt-3">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="form-control w-auto">
                    <button type="submit" class="btn btn-success">Thêm vào giỏ hàng</button>
                </div>
            </form>
        </div>

        <!-- MODAL MÔ TẢ -->
        <div class="modal fade" id="shortDescModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Mô tả ngắn</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body product-desc">
                        <?php echo html_entity_decode($product['short_desc']); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="longDescModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Mô tả chi tiết</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo html_entity_decode($product['long_desc']); ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <p class="text-center text-danger">Sản phẩm không tồn tại.</p>
    <?php endif; ?>
</div>

<style>
    .product-view {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .main-image-box {
        width: 480px;
        height: 480px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        overflow: hidden;
    }

    .main-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.3s;
    }

    .main-image:hover {
        transform: scale(1.05);
    }

    .thumbs img.thumb-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 8px;
        transition: transform 0.2s, border-color 0.2s;
    }

    .thumbs img.thumb-img:hover {
        transform: scale(1.1);
        border-color: #0d6efd;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php
include 'includes/footer.php';