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
                <!-- ẢNH CHÍNH + NÚT -->
                <div class="position-relative d-flex align-items-center justify-content-center border rounded bg-white"
                    style="width:100%; max-width:400px; height:400px; margin:auto; overflow:hidden;">
                    <button id="prevBtn" class="btn btn-light position-absolute start-0 top-50 translate-middle-y"
                        style="z-index:10; opacity:0.7;">&#10094;</button>

                    <img id="mainImage"
                        src="uploads/products/<?php echo !empty($product['main_image']) ? htmlspecialchars($product['main_image']) : 'no-image.png'; ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        style="max-width:100%; max-height:100%; object-fit:contain;">

                    <button id="nextBtn" class="btn btn-light position-absolute end-0 top-50 translate-middle-y"
                        style="z-index:10; opacity:0.7;">&#10095;</button>
                </div>

                <!-- LẤY ẢNH PHỤ -->
                <?php
                $imgs = $conn->prepare("SELECT image FROM product_images WHERE product_id = ? AND (is_main IS NULL OR is_main = 0)");
                $imgs->bind_param("i", $id);
                $imgs->execute();
                $images = $imgs->get_result();

                // Mảng chứa tất cả ảnh (chính + phụ)
                $all_images = [];
                if (!empty($product['main_image'])) {
                    $all_images[] = $product['main_image'];
                }
                while ($img = $images->fetch_assoc()) {
                    $all_images[] = $img['image'];
                }
                ?>

                <!-- HIỂN THỊ DANH SÁCH ẢNH -->
                <?php if (count($all_images) > 0): ?>
                    <div class="thumbs d-flex flex-wrap gap-2 justify-content-center mt-3">
                        <?php foreach ($all_images as $img): ?>
                            <img src="uploads/products/<?php echo htmlspecialchars($img); ?>"
                                alt="Ảnh phụ"
                                class="thumb border rounded"
                                style="width:75px; height:75px; object-fit:cover; cursor:pointer; opacity:0.8; transition:all 0.2s;">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <?php if ($product['is_hot']): ?>
                    <span class="badge bg-danger">HOT</span>
                <?php endif; ?>
                <p class="text-muted"><?php echo ($product['short_desc']); ?></p>
                <div class="product-desc">
                    <?php echo html_entity_decode($product['long_desc']); ?>
                </div>
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const mainImage = document.getElementById("mainImage");
            const thumbs = Array.from(document.querySelectorAll(".thumb"));
            const nextBtn = document.getElementById("nextBtn");
            const prevBtn = document.getElementById("prevBtn");

            let currentIndex = 0;

            function updateImage() {
                mainImage.src = thumbs[currentIndex].src;
                thumbs.forEach(t => t.style.border = "2px solid transparent");
                thumbs[currentIndex].style.border = "2px solid #007bff";
                // Cuộn thumbnail vào giữa nếu cần      
                // thumbs[currentIndex].scrollIntoView({ behavior: "smooth", inline: "center" });
            }

            thumbs.forEach((thumb, index) => {
                thumb.addEventListener("click", () => {
                    currentIndex = index;
                    updateImage();
                });
            });

            nextBtn.addEventListener("click", () => {
                currentIndex = (currentIndex + 1) % thumbs.length;
                updateImage();
            });

            prevBtn.addEventListener("click", () => {
                currentIndex = (currentIndex - 1 + thumbs.length) % thumbs.length;
                updateImage();
            });

            if (thumbs.length > 0) updateImage();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .product-desc img {
            max-width: 100%;
            height: auto;
            margin: 8px 0;
        }

        .product-desc ul {
            padding-left: 20px;
        }

        .product-desc p {
            line-height: 1.6;
            margin-bottom: 8px;
        }
    </style>

</div>

<?php include 'includes/footer.php'; ?>