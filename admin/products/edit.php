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

// Lấy quà đi kèm
$sql_gift = "SELECT gift_description FROM product_gifts WHERE product_id = ?";
$stmt_gift = $conn->prepare($sql_gift);
$stmt_gift->bind_param("i", $id);
$stmt_gift->execute();
$result_gift = $stmt_gift->get_result();
$gift = $result_gift->fetch_assoc();

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
    $gift_description = trim($_POST['gift_description']); // Quà đi kèm

    // Cập nhật sản phẩm
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
        // Cập nhật quà đi kèm
        if (!empty($gift_description)) {
            // Kiểm tra xem quà tặng đã tồn tại hay chưa
            $sql_check_gift = "SELECT id FROM product_gifts WHERE product_id = ?";
            $stmt_check_gift = $conn->prepare($sql_check_gift);
            $stmt_check_gift->bind_param("i", $id);
            $stmt_check_gift->execute();
            $result_check_gift = $stmt_check_gift->get_result();

            if ($result_check_gift->num_rows > 0) {
                // Nếu đã tồn tại, cập nhật quà tặng
                $sql_gift_update = "UPDATE product_gifts SET gift_description = ? WHERE product_id = ?";
                $stmt_gift_update = $conn->prepare($sql_gift_update);
                $stmt_gift_update->bind_param("si", $gift_description, $id);
                $stmt_gift_update->execute();
            } else {
                // Nếu chưa tồn tại, thêm mới quà tặng
                $sql_gift_insert = "INSERT INTO product_gifts (product_id, gift_description) VALUES (?, ?)";
                $stmt_gift_insert = $conn->prepare($sql_gift_insert);
                $stmt_gift_insert->bind_param("is", $id, $gift_description);
                $stmt_gift_insert->execute();
            }
        }

        // Xử lý hình ảnh (nếu có)
        if (!empty($_FILES['images']['name'][0])) {
            $target_dir = "../../uploads/products/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (!empty($_FILES['images']['name'][$key])) {
                    $filename = time() . "_" . basename($_FILES['images']['name'][$key]);
                    $target_file = $target_dir . $filename;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        // Lưu vào bảng product_images
                        $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
                        $stmt_img->bind_param("is", $id, $filename);
                        $stmt_img->execute();
                    }
                }
            }
        }

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

            <div class="mb-3">
                <label class="form-label">Quà đi kèm</label>
                <textarea name="gift_description" id="gift_description" class="form-control" rows="3"><?= htmlspecialchars($gift['gift_description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Ảnh sản phẩm (chọn nhiều ảnh)</label>
                <input type="file" name="images[]" multiple class="form-control" id="images">
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