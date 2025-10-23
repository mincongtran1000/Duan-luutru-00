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

// Lấy từ khóa tìm kiếm
$search_keyword = $_GET['search'] ?? '';

// Lấy danh sách sản phẩm
$conditions = ["p.is_hidden = 0"]; // Điều kiện mặc định
$params = [];
$types = "";

// Nếu có từ khóa tìm kiếm
if (!empty($search_keyword)) {
    $conditions[] = "p.name LIKE ?";
    $params[] = '%' . $search_keyword . '%';
    $types .= "s";
}

// Nếu có danh mục cụ thể
if ($category_name !== 'all') {
    $conditions[] = "c.name = ?";
    $params[] = $category_name;
    $types .= "s";
}

// Ghép các điều kiện lại thành câu lệnh SQL
$sql = "SELECT p.*, c.name AS category_name, pi.image AS main_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
        WHERE " . implode(" AND ", $conditions) . "
        ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);

// Nếu có tham số, bind chúng vào câu lệnh
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Danh sách sản phẩm</h1>

    <!-- Thanh tìm kiếm -->
    <form action="products.php" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
    </form>

    <!-- Tabs danh mục -->
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
                <div class="col-6 col-sm-4 col-md-3 col-lg-custom mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <!-- Ảnh sản phẩm -->
                        <div class="product-image-wrapper position-relative">
                            <img src="uploads/products/<?= htmlspecialchars($row['main_image'] ?: 'no-image.png') ?>"
                                alt="<?= htmlspecialchars($row['name']) ?>"
                                class="product-img">
                        </div>

                        <div class="card-body text-center">
                            <h6 class="card-title mb-2"><?= htmlspecialchars(substr($row['name'], 0, 40)) ?><?= strlen($row['name']) > 40 ? '...' : '' ?></h6>
                            <p class="fw-bold text-primary mb-1"><?= number_format($row['price'], 0, ',', '.') ?> đ</p>

                            <?php if ($row['quantity'] > 0): ?>
                                <span class="badge bg-success mb-2">Còn hàng</span>
                            <?php else: ?>
                                <span class="badge bg-secondary mb-2">Hết hàng</span>
                            <?php endif; ?>

                            <div class="d-flex flex-column gap-2 mt-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                                <form action="add_to_cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-success w-100">Thêm vào giỏ hàng</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">Không có sản phẩm nào phù hợp với tìm kiếm.</p>
        <?php endif; ?>
    </div>
</div>

<style>
    @media (min-width: 1200px) {
        .col-lg-custom {
            flex: 0 0 20%;
            max-width: 20%;
        }
    }

    .product-image-wrapper {
        width: 100%;
        aspect-ratio: 1/1;
        overflow: hidden;
        border-radius: 10px;
        border: 1px solid #eee;
        background-color: #f9f9f9;
    }

    .product-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .product-image-wrapper:hover .product-img {
        transform: scale(1.05);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>