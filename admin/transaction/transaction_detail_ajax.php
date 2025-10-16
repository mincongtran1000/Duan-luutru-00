<?php
require '../../includes/session.php';
require '../../includes/db.php';

if (!isset($_GET['id'])) {
    echo '<div class="text-danger">ID giao dịch không được cung cấp.</div>';
    exit;
}

$transaction_id = intval($_GET['id']);

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    echo '<div class="text-danger">Không thể kết nối đến cơ sở dữ liệu.</div>';
    exit;
}

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
    echo '<div class="text-danger">Giao dịch không tồn tại.</div>';
    exit;
}

// Hiển thị thông tin giao dịch
echo '<h5>Thông tin giao dịch</h5>';
echo '<p><strong>Người dùng:</strong> ' . htmlspecialchars($transaction['username']) . '</p>';
echo '<p><strong>Email:</strong> ' . htmlspecialchars($transaction['email']) . '</p>';
echo '<p><strong>Tổng tiền:</strong> ' . number_format($transaction['total_amount'], 0, ',', '.') . ' đ</p>';
echo '<p><strong>Trạng thái:</strong> ' . ucfirst($transaction['status']) . '</p>';
echo '<p><strong>Ngày tạo:</strong> ' . $transaction['created_at'] . '</p>';

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

echo '<h5>Danh sách sản phẩm</h5>';
echo '<table class="table table-bordered">';
echo '<thead>
        <tr>
            <th>Hình ảnh</th>
            <th>Sản phẩm</th>
            <th>Phân loại</th>
            <th>Số lượng</th>
            <th>Giá</th>
            <th>Thành tiền</th>
        </tr>
      </thead>';
echo '<tbody>';
while ($row = $result_items->fetch_assoc()) {
    echo '<tr>';
    echo '<td>';
    if (!empty($row['main_image'])) {
        echo '<img src="http://localhost/my_website/uploads/products/' . htmlspecialchars($row['main_image']) . '" 
                 alt="' . htmlspecialchars($row['product_name']) . '" 
                 style="width: 80px; height: 80px; object-fit: cover;">';
    } else {
        echo '<img src="http://localhost/my_website/uploads/no-image.png" 
                 alt="No image" 
                 style="width: 80px; height: 80px; object-fit: cover;">';
    }
    echo '</td>';
    echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
    echo '<td>' . $row['quantity'] . '</td>';
    echo '<td>' . number_format($row['price'], 0, ',', '.') . ' đ</td>';
    echo '<td>' . number_format($row['price'] * $row['quantity'], 0, ',', '.') . ' đ</td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
