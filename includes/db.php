<?php
$servername = "localhost";
$username   = "root";   // user MySQL của bạn
$password   = "";       // mật khẩu MySQL (nếu có)
$dbname     = "my_website"; // tên database của bạn

// Kết nối MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// ===== Đảm bảo có admin mặc định =====
$check = $conn->prepare("SELECT * FROM users WHERE username = 'admin'");
$check->execute();
$result = $check->get_result();

// if ($result->num_rows === 0) {
//     $default_pass = password_hash("admin123", PASSWORD_BCRYPT);
//     $sql = "INSERT INTO users (username, email, password, role) 
//             VALUES ('admin', 'admin@mywebsite.com', ?, 'admin')";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("s", $default_pass);
//     $stmt->execute();
// }
