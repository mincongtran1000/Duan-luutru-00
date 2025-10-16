<!-- <?php
require '../../includes/session.php';
require '../../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Lấy ID giao dịch từ URL
if (!isset($_GET['id'])) {
    die("ID giao dịch không được cung cấp.");
}
$transaction_id = intval($_GET['id']);

// Lấy thông tin giao dịch
$query = "
    SELECT t.*, u.username, u.email 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    die("Giao dịch không tồn tại.");
}

// Lấy danh sách sản phẩm trong giao dịch
$query_items = "
    SELECT o.*, p.name AS product_name, c.name AS category_name, 
           (SELECT pi.image 
            FROM product_images pi 
            WHERE pi.product_id = o.product_id AND pi.is_main = 1 
            LIMIT 1) AS main_image
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN categories c ON o.category_id = c.id
    WHERE o.transaction_id = ?
";
$stmt_items = $conn->prepare($query_items);
$stmt_items->bind_param("i", $transaction_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết giao dịch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-4">
        <h2 class="mb-4">Chi tiết giao dịch #<?php echo $transaction_id; ?></h2>
        <div class="mb-4">
            <p><strong>Người dùng:</strong> <?php echo htmlspecialchars($transaction['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($transaction['email']); ?></p>
            <p><strong>Tổng tiền:</strong> <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?> đ</p>
            <p><strong>Trạng thái:</strong> <?php echo ucfirst($transaction['status']); ?></p>
            <p><strong>Ngày tạo:</strong> <?php echo $transaction['created_at']; ?></p>
        </div>

        <h3>Danh sách sản phẩm</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Sản phẩm</th>
                    <th>Phân loại</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['main_image'])): ?>
                                <img src="../../uploads/products/<?php echo htmlspecialchars($row['main_image']); ?>"
                                    alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <img src="../../uploads/no-image.png"
                                    alt="No image"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo number_format($row['price'], 0, ',', '.'); ?> đ</td>
                        <td><?php echo number_format($row['price'] * $row['quantity'], 0, ',', '.'); ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html><?php
        require '../../includes/session.php';
        require '../../includes/db.php';

        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: ../login.php");
            exit;
        }

        // Lấy ID giao dịch từ URL
        if (!isset($_GET['id'])) {
            die("ID giao dịch không được cung cấp.");
        }
        $transaction_id = intval($_GET['id']);

        // Lấy thông tin giao dịch
        $query = "
    SELECT t.*, u.username, u.email 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_assoc();

        if (!$transaction) {
            die("Giao dịch không tồn tại.");
        }

        // Lấy danh sách sản phẩm trong giao dịch
        $query_items = "
    SELECT o.*, p.name AS product_name, c.name AS category_name, 
           (SELECT pi.image 
            FROM product_images pi 
            WHERE pi.product_id = o.product_id AND pi.is_main = 1 
            LIMIT 1) AS main_image
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN categories c ON o.category_id = c.id
    WHERE o.transaction_id = ?
";
        $stmt_items = $conn->prepare($query_items);
        $stmt_items->bind_param("i", $transaction_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết giao dịch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-4">
        <h2 class="mb-4">Chi tiết giao dịch #<?php echo $transaction_id; ?></h2>
        <div class="mb-4">
            <p><strong>Người dùng:</strong> <?php echo htmlspecialchars($transaction['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($transaction['email']); ?></p>
            <p><strong>Tổng tiền:</strong> <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?> đ</p>
            <p><strong>Trạng thái:</strong> <?php echo ucfirst($transaction['status']); ?></p>
            <p><strong>Ngày tạo:</strong> <?php echo $transaction['created_at']; ?></p>
        </div>

        <h3>Danh sách sản phẩm</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Sản phẩm</th>
                    <th>Phân loại</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['main_image'])): ?>
                                <img src="../../uploads/products/<?php echo htmlspecialchars($row['main_image']); ?>"
                                    alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <img src="../../uploads/no-image.png"
                                    alt="No image"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo number_format($row['price'], 0, ',', '.'); ?> đ</td>
                        <td><?php echo number_format($row['price'] * $row['quantity'], 0, ',', '.'); ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html> -->