<?php
/**
 * Cấu hình kết nối cơ sở dữ liệu MySQL
 */

// Thông tin kết nối MySQL
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP mặc định không có mật khẩu
define('DB_NAME', 'tuyensinhkhtn');

// Tạo kết nối
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Lỗi kết nối: " . $conn->connect_error);
}

// Thiết lập charset UTF-8
$conn->set_charset("utf8mb4");

// Tạo cơ sở dữ liệu nếu chưa tồn tại
$conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);

// Chọn cơ sở dữ liệu
$conn->select_db(DB_NAME);

// Hàm đóng kết nối
function closeDB($connection) {
    $connection->close();
}
?>
