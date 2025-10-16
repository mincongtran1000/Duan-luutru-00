<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "my_website";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// ===== Đảm bảo có admin mặc định =====
$check = $conn->prepare("SELECT * FROM admin WHERE username = 'admin'");
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $default_pass = password_hash("admin123", PASSWORD_BCRYPT);
    $sql = "INSERT INTO admin (username, email, password) 
            VALUES ('admin', 'admin@mywebsite.com', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $default_pass);
    $stmt->execute();
}
