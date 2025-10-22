<?php
require '../../includes/session.php';
require '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $price      = floatval($_POST['price']);
    $short_desc = trim($_POST['short_desc']);
    $long_desc  = trim($_POST['long_desc']);
    $quantity   = intval($_POST['quantity']);
    $is_hot     = isset($_POST['is_hot']) ? 1 : 0;
    $is_hidden  = isset($_POST['is_hidden']) ? 1 : 0;
    $category_id = $_POST['category_id'];

    // 1. Insert sản phẩm
    $stmt = $conn->prepare("INSERT INTO products 
        (name, price, short_desc, long_desc, quantity, is_hot, is_hidden, category_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sdssiiii",
        $name,
        $price,
        $short_desc,
        $long_desc,
        $quantity,
        $is_hot,
        $is_hidden,
        $category_id
    );

    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;

        // 2. Upload ảnh (nhiều ảnh)
        if (!empty($_FILES['images']['name'][0])) {
            $target_dir = "../../uploads/products/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (!empty($_FILES['images']['name'][$key])) {
                    $filename = time() . "_" . basename($_FILES['images']['name'][$key]);
                    $target_file = $target_dir . $filename;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        // 3. Lưu vào bảng product_images
                        $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
                        $stmt_img->bind_param("is", $product_id, $filename);
                        $stmt_img->execute();
                    }
                }
            }
        }

        header("Location: ../index.php");
        exit;
    } else {
        echo "Lỗi: " . $stmt->error;
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

<body class="bg-light">
    <div class="container mt-5">
        <h2>➕ Thêm sản phẩm mới</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Tên sản phẩm</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select" required>
                    <?php
                    $cats = $conn->query("SELECT * FROM categories");
                    while ($cat = $cats->fetch_assoc()):
                    ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Giá</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ảnh sản phẩm (chọn nhiều ảnh)</label>
                <input type="file" name="images[]" multiple class="form-control" id="images">
                <div id="preview" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả ngắn</label>
                <textarea name="short_desc" id="short_desc" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả chi tiết</label>
                <textarea name="long_desc" id="long_desc" class="form-control" rows="5"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Số lượng</label>
                <input type="number" name="quantity" class="form-control" value="0" min="0" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="is_hot" value="1">
                <label class="form-check-label">Sản phẩm nổi bật (Hot)</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="is_hidden" value="1">
                <label class="form-check-label">Ẩn sản phẩm (không hiển thị)</label>
            </div>

            <button class="btn btn-success" type="submit">Lưu sản phẩm</button>
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

        const input = document.getElementById('images');
        const preview = document.getElementById('preview');
        input.addEventListener('change', e => {
            preview.innerHTML = '';
            [...e.target.files].forEach(file => {
                const reader = new FileReader();
                reader.onload = ev => {
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.classList.add('rounded', 'border');
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>

</body>

</html>