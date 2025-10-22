<?php
require '../autoload/session.php';
require '../autoload/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy sản phẩm
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) die("Sản phẩm không tồn tại!");

// Cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $short_desc  = trim($_POST['short_desc']);
    $long_desc   = trim($_POST['long_desc']);
    $price       = floatval($_POST['price']);
    $quantity    = intval($_POST['quantity']);
    $category_id = $_POST['category_id'];
    $is_hot      = isset($_POST['is_hot']) ? 1 : 0;
    $is_hidden   = isset($_POST['is_hidden']) ? 1 : 0;

    $sql_update = "UPDATE products 
                   SET name=?, short_desc=?, long_desc=?, price=?, quantity=?, category_id=?, is_hot=?, is_hidden=? 
                   WHERE id=?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param(
        "sssdiisii",
        $name,
        $short_desc,
        $long_desc,
        $price,
        $quantity,
        $category_id,
        $is_hot,
        $is_hidden,
        $id
    );

    if ($stmt_update->execute()) {
        header("Location: index.php?msg=updated");
        exit;
    } else {
        echo "Lỗi khi cập nhật: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2>✏️ Chỉnh sửa sản phẩm</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Tên sản phẩm</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select" required>
                    <?php
                    $cats = $conn->query("SELECT * FROM categories");
                    while ($cat = $cats->fetch_assoc()):
                    ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Giá</label>
                <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả ngắn</label>
                <textarea name="short_desc" id="short_desc" class="form-control" rows="3"><?= htmlspecialchars($product['short_desc']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả chi tiết</label>
                <textarea name="long_desc" id="long_desc" class="form-control" rows="5"><?= htmlspecialchars($product['long_desc']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Số lượng</label>
                <input type="number" name="quantity" class="form-control" value="<?= $product['quantity'] ?>" min="0" required>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="is_hot" value="1" <?= $product['is_hot'] ? 'checked' : '' ?>>
                <label class="form-check-label">Sản phẩm nổi bật (Hot)</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="is_hidden" value="1" <?= $product['is_hidden'] ? 'checked' : '' ?>>
                <label class="form-check-label">Ẩn sản phẩm</label>
            </div>

            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="index.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>

    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('short_desc', {
            height: 150,
            removePlugins: 'elementspath',
            toolbar: [
                ['Bold', 'Italic', 'Underline', 'Strike'],
                ['NumberedList', 'BulletedList'],
                ['Link', 'Unlink'],
                ['JustifyLeft', 'JustifyCenter', 'JustifyRight'],
                ['Format']
            ]
        });

        CKEDITOR.replace('long_desc', {
            height: 250,
            removePlugins: 'elementspath',
            toolbar: [
                ['Bold', 'Italic', 'Underline', 'Strike'],
                ['NumberedList', 'BulletedList'],
                ['Link', 'Unlink'],
                ['JustifyLeft', 'JustifyCenter', 'JustifyRight'],
                ['Image', 'Table', 'Format']
            ]
        });
    </script>
</body>

</html>