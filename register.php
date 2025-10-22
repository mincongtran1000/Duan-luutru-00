<?php
require 'includes/db.php';
require 'includes/session.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Xử lý avatar upload
    $avatar = null;
    if (!empty($_FILES['avatar']['name'])) {
        $targetDir = "uploads/avatars/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['avatar']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatar = $fileName;
        }
    }

    // Kiểm tra trùng username hoặc email
    $check = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "Tên đăng nhập hoặc email đã tồn tại!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, fullname, email, phone, address, password, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $fullname, $email, $phone, $address, $password, $avatar);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
            header("Location: login.php");
            exit;
        } else {
            $message = "Có lỗi xảy ra, vui lòng thử lại.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-5" style="max-width:700px;">
    <h2>Đăng ký</h2>

    <?php if ($message !== ""): ?>
        <div class="alert alert-warning">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Họ và tên</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Địa chỉ</label>
            <textarea name="address" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <input type="file" name="avatar" class="form-control" accept="image/*">
        </div>
        <button class="btn btn-primary" type="submit">Đăng ký</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>