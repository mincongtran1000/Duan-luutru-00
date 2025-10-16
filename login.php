<?php
require 'includes/session.php';
require 'includes/db.php';
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // chỉ hiển thị 1 lần
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        // Có user trong DB
        if (password_verify($password, $row['password'])) {
            // Đúng mật khẩu
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email']; // optional

            setcookie('username', $row['username'], time() + 7 * 24 * 3600, '/');

            header('Location: index.php');
            exit;
        } else {
            // Sai mật khẩu
            $message = 'Sai mật khẩu';
        }
    } else {
        // Không tìm thấy username
        $message = 'Tài khoản không tồn tại';
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- HTML form ở đây, hiển thị $message -->
<div class="container mt-5">
    <h2>Đăng nhập</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-success" type="submit">Đăng nhập</button>
    </form>

    <p class="mt-3"><a href="admin/login.php">Đăng nhập Admin</a></p>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/footer.php'; ?>