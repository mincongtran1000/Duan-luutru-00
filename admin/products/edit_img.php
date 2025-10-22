<?php
require '../autoload/session.php';
require '../autoload/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();
if (!$product) {
    header('Location: index.php');
    exit;
}

        // Xử lý upload ảnh mới
        // Xử lý upload ảnh mới
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
            // Kiểm tra và tạo thư mục nếu chưa có
            $uploadDir = realpath(__DIR__ . "/../../uploads/products/");
            if ($uploadDir === false) {
                $uploadDir = __DIR__ . "/../../uploads/products/";
                mkdir($uploadDir, 0777, true);
            }

            // Kiểm tra xem đã có ảnh chính chưa
            $check_main = $conn->prepare("SELECT COUNT(*) AS total FROM product_images WHERE product_id = ? AND is_main = 1");
            $check_main->bind_param("i", $id);
            $check_main->execute();
            $has_main = $check_main->get_result()->fetch_assoc()['total'] > 0;

            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['images']['name'][$key]));
                    $targetPath = $uploadDir . "/" . $fileName;

                    if (move_uploaded_file($tmpName, $targetPath)) {
                        // Ảnh đầu tiên và chưa có ảnh chính -> đặt làm ảnh chính
                        $is_main = (!$has_main && $key === 0) ? 1 : 0;
                        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image, is_main) VALUES (?, ?, ?)");
                        $stmt->bind_param("isi", $id, $fileName, $is_main);
                        $stmt->execute();

                        if ($is_main) {
                            $has_main = true; // đánh dấu đã có ảnh chính
                        }
                    }
                }
            }

            header("Location: edit_img.php?id=$id");
            exit;
        }

        // Lấy danh sách ảnh
        $images = $conn->query("SELECT id, image, is_main FROM product_images WHERE product_id = $id ORDER BY is_main DESC, id ASC");
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Quản lý ảnh sản phẩm</title>
</head>

<body class="p-4">
    <h3>Quản lý ảnh: <?php echo htmlspecialchars($product['name']); ?></h3>

    <!-- Upload ảnh mới -->
    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <label class="form-label">Thêm ảnh mới</label>
        <input type="file" name="images[]" multiple class="form-control mb-2" accept="image/*">
        <button type="submit" class="btn btn-success">Upload</button>
    </form>

    <div class="mb-3">
        <strong>Ảnh sản phẩm</strong><br>
        <?php if ($images && $images->num_rows): ?>
            <div class="d-flex flex-wrap">
                <?php while ($img = $images->fetch_assoc()): ?>
                    <div class="text-center me-3 mb-3" style="width:200px;">
                        <!-- Ô hiển thị ảnh cố định -->
                        <div style="width:200px; height:200px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center; background:#f9f9f9;">
                            <img src="/my_website/uploads/products/<?php echo htmlspecialchars($img['image']); ?>"
                                style="max-width:100%; max-height:100%; object-fit:cover;"
                                alt="">
                        </div>

                        <!-- Nút thao tác -->
                        <div class="mt-2">
                            <?php if ($img['is_main']): ?>
                                <span class="badge bg-success d-block mb-2">Ảnh chính</span>
                            <?php else: ?>
                                <a href="set_main.php?product_id=<?php echo $id; ?>&image_id=<?php echo $img['id']; ?>"
                                    class="btn btn-sm btn-outline-primary w-100 mb-1">Đặt làm chính</a>
                            <?php endif; ?>

                            <a href="delete_image.php?id=<?php echo $img['id']; ?>&product_id=<?php echo $id; ?>"
                                class="btn btn-sm btn-outline-danger w-100"
                                onclick="return confirm('Xoá ảnh này?')">Xoá</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-muted">Chưa có ảnh</div>
        <?php endif; ?>
    </div>


    <a href="index.php" class="btn btn-secondary">Quay lại</a>
</body>

</html>