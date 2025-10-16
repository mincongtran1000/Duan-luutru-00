<?php
require 'includes/session.php';
require 'includes/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Lấy slug category từ URL
$category_slug = $_GET['category'] ?? 'all';

// Map slug -> tên trong DB
$category_map = [
    'tai-nghe'   => 'Tai nghe',
    'day-sac'    => 'Dây sạc',
    'dien-thoai' => 'Điện thoại',
    'phan-mem'   => 'Phần mềm',
    'laptop'     => 'Laptop',
    'dong-ho'    => 'Đồng hồ',
    'all'        => 'all'
];

$category_name = $category_map[$category_slug] ?? 'all';

if ($category_name !== 'all') {
    $sql = "SELECT p.*, c.name AS category_name, pi.image AS main_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
            WHERE p.is_hidden = 0 AND c.name = ?
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category_name);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT p.*, c.name AS category_name, pi.image AS main_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
            WHERE p.is_hidden = 0
            ORDER BY p.created_at DESC";
    $result = $conn->query($sql);
}
?>

<!-- thanh navbar của danh mục sản phẩm -->
<div class="container mt-5">
    <h1 class="text-center mb-4">Danh sách sản phẩm</h1>
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link <?= $category_name === 'all' ? 'active' : '' ?>" href="products.php?category=all">Tất cả</a></li>
        <li class="nav-item"><a class="nav-link <?= $category_name === 'tai-nghe' ? 'active' : '' ?>" href="products.php?category=tai-nghe">Tai nghe</a></li>
        <li class="nav-item"><a class="nav-link <?= $category_name === 'day-sac' ? 'active' : '' ?>" href="products.php?category=day-sac">Dây sạc</a></li>
        <li class="nav-item"><a class="nav-link <?= $category_name === 'dien-thoai' ? 'active' : '' ?>" href="products.php?category=dien-thoai">Điện thoại</a></li>
        <li class="nav-item"><a class="nav-link <?= $category_name === 'phan-mem' ? 'active' : '' ?>" href="products.php?category=phan-mem">Phần mềm</a></li>
        <li class="nav-item"><a class="nav-link <?= $category_name === 'laptop' ? 'active' : '' ?>" href="products.php?category=laptop">Laptop</a></li>
        <li class="nav-item"><a class="nav-link <?= $category_name === 'dong-ho' ? 'active' : '' ?>" href="products.php?category=dong-ho">Đồng hồ</a></li>
    </ul>
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <!-- thay đổi cấu hình và kích thước ảnh phù hợp với ô chứa sản phẩm -->
                        <?php if (!empty($row['main_image'])): ?>
                            <img src="uploads/products/<?php echo htmlspecialchars($row['main_image']); ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($row['name']); ?>"
                                style="height:350px;object-fit:cover;">
                        <?php else: ?>
                            <img src="uploads/no-image.png"
                                class="card-img-top"
                                alt="No image"
                                style="height:200px;object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($row['name']); ?>
                                <?php if ($row['is_hot']): ?>
                                    <span class="badge bg-danger">HOT</span>
                                <?php endif; ?>
                            </h5>
                            <p class="card-text text-truncate"><?php echo htmlspecialchars($row['short_desc']); ?></p>
                            <p class="fw-bold text-primary"><?php echo number_format($row['price'], 0, ',', '.'); ?> đ</p>
                            <?php if ($row['quantity'] > 0): ?>
                                <span class="badge bg-success">Còn hàng</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Hết hàng</span>
                            <?php endif; ?>
                            <br>
                            <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">Xem chi tiết</a>
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
            <p class="text-center">Không có sản phẩm nào trong mục này.</p>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>