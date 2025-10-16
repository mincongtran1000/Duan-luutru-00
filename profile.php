<?php
require 'includes/session.php';
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = ''; // tránh lỗi undefined

// Lấy dữ liệu user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, fullname, phone, address, avatar FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Xử lý update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['fullname'] ?? '';
    $email     = $_POST['email'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $address   = $_POST['address'] ?? '';
    $avatar    = $user['avatar']; // giữ avatar cũ mặc định

    // Upload avatar mới nếu có
    // Upload avatar mới nếu có
    if (!empty($_FILES['avatar']['name'])) {
        // tạo thư mục nếu chưa có
        if (!is_dir("uploads/avatars")) {
            mkdir("uploads/avatars", 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['avatar']['name']);
        $target   = "uploads/avatars/" . $fileName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            // Xóa avatar cũ nếu có (trừ default.png)
            if ($user['avatar'] && $user['avatar'] !== 'default.png') {
                $oldFile = "uploads/avatars/" . $user['avatar'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $avatar = $fileName; // chỉ lưu tên file trong DB
        }
    }


    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, phone=?, address=?, avatar=? WHERE id=?");
    $stmt->bind_param("sssssi", $full_name, $email, $phone, $address, $avatar, $user_id);

    if ($stmt->execute()) {
        $message = "Cập nhật thành công!";
        // load lại dữ liệu user mới
        $stmt = $conn->prepare("SELECT username, email, fullname, phone, address, avatar FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    } else {
        $message = "Có lỗi xảy ra, vui lòng thử lại.";
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-5" style="max-width:800px;">
    <h2>Thông tin cá nhân</h2>

    <?php if ($message): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <div style="width:200px;height:200px;border:1px solid #ddd;margin:auto;overflow:hidden;border-radius:50%;">
                <img src="uploads/avatars/<?php echo $user['avatar'] ? htmlspecialchars($user['avatar']) : 'default.png'; ?>"
                    alt="Avatar"
                    style="width:100%;height:100%;object-fit:cover;">
            </div>
            <p class="mt-2"><strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
        </div>

        <div class="col-md-8">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="fullname" class="form-control"
                        value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                        value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ảnh đại diện mới</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*">
                </div>
                <button class="btn btn-primary" type="submit">Cập nhật</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>