<?php
session_start();
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container my-4">
    <h2 class="text-success">Thanh toán thành công!</h2>
    <p>Cảm ơn bạn đã mua hàng. Giao dịch của bạn đã được hoàn tất.</p>
    <a href="index.php" class="btn btn-primary">Quay lại trang chủ</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/footer.php'; ?>