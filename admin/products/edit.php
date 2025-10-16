<?php
require '../autoload/session.php';
require '../autoload/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy sản phẩm theo ID
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Sản phẩm không tồn tại!");
}

// Nếu submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $short_desc = $_POST['short_desc'] ?? '';
    $long_desc = $_POST['long_desc'] ?? '';
    $price = $_POST['price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    $category_id = $_POST['category_id'] ?? null;
    $is_hot = isset($_POST['is_hot']) ? 1 : 0;
    $is_hidden = isset($_POST['is_hidden']) ? 1 : 0;

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
        header("Location: index.php?msg=updated"); // về trang danh sách
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
    <title>Thêm sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<div class="container mt-5">
    <h2>Chỉnh sửa sản phẩm</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả ngắn</label>
            <input type="text" name="short_desc" class="form-control" value="<?= htmlspecialchars($product['short_desc']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả dài</label>
            <textarea name="long_desc" class="form-control"><?= htmlspecialchars($product['long_desc']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Giá</label>
            <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Số lượng</label>
            <input type="number" name="quantity" class="form-control" value="<?= $product['quantity'] ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-control">
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

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_hot" <?= $product['is_hot'] ? 'checked' : '' ?>>
            <label class="form-check-label">HOT</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_hidden" <?= $product['is_hidden'] ? 'checked' : '' ?>>
            <label class="form-check-label">Ẩn sản phẩm</label>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>