<?php
session_start();
require 'includes/header.php';
require 'includes/navbar.php';
?>

<div class="container my-4">
    <h2 class="mb-4 text-success">Thanh toán thành công!</h2>
    <p>Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đã được xử lý.</p>
    <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
</div>

<?php require 'includes/footer.php'; ?>