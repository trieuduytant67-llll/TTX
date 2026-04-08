<?php
// File: common.php

// Các biến toàn cục
$start_time = '8h00 ngày 21/04/2025';
$end_time = '17h00 ngày 05/05/2025';
$announcement_date = '20/5/2025';
$site_name = "Tuyển sinh THPT chuyên Khoa học Tự Nhiên";
$year = '2025';  // Biến năm
$plan_url = 'https://hus.vnu.edu.vn/thong-bao/dao-tao-tuyen-sinh/ke-hoach-tuyen-sinh-lop-10-truong-thpt-chuyen-khoa-hoc-tu-nhien-nam-2025-140883.html';  // Biến đường dẫn Kế hoạch tuyển sinh
$guide_url = 'https://hus.vnu.edu.vn/thong-bao/dao-tao-tuyen-sinh/huong-dan-dang-ky-ho-so-du-thi-lop-10-thpt-chuyen-khtn-nam-hoc-2025-140924.html';  // Biến đường dẫn Hướng dẫn đăng ký dự thi
$copyright_year = "2025";
$counter_file = __DIR__ . '/counter.txt';

$googleSheetUrl_trangthaihoso = 'https://docs.google.com/spreadsheets/d/197FO3XQ7X7eEwBtvNJFCF-z3QOlylEvd/export?format=csv';
$googleSheetUrl_thongtinduthi = 'https://docs.google.com/spreadsheets/d/1ylaOpCRUIxqopm8iEyIEQJDetgW10LPy/export?format=csv';
$data_path_kqthi = __DIR__ . '/../result/kq_thi.csv';
$data_path_kqphuckhao = __DIR__ . '/../result/kq_phuckhao.csv';



// Các hàm tiện ích chung
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Hàm hiển thị header
function display_header($page_title = "", $additional_css = "") {
    global $site_name;
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>' . ($page_title ? $page_title . ' - ' : '') . $site_name . '</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="css/main.css">
        ' . $additional_css . '
    </head>
    <body>
        <header>
            <h1>' . $site_name . '</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="about.php">Giới thiệu</a></li>
                    <li><a href="contact.php">Liên hệ</a></li>
                </ul>
            </nav>
        </header>
        <main>';
}

// Hàm hiển thị footer
function display_footer($additional_scripts = "") {
    global $copyright_year;
    // Đường dẫn file lưu counter
    $counter_file = __DIR__ . '/counter.txt';
    // Gọi hàm cập nhật và lấy giá trị đếm
    updateVisitCounter($counter_file);
    $visit_count = file_get_contents($counter_file); // Đọc số lượt truy cập
    echo '</main>
        <footer>
            <div class="footer">
                &copy; ' . $copyright_year . ' Sản phẩm được xây dựng bởi đội ngũ đào tạo Trường ĐHKHTN, ĐHQGHN. <br>
                Liên hệ: Phòng Đào tạo Trường ĐHKHTN, ĐHQGHN.<br>
                Điện thoại: <a href="tel:0886074527">088.607.4527</a>; Email:<a href="mailto:daotaodaihoc@hus.edu.vn">daotaodaihoc@hus.edu.vn</a><br>
                Địa chỉ: <a href="https://maps.app.goo.gl/qmzeZ4UC7Psksx819" target="_blank" rel="noopener noreferrer">
                            334 Nguyễn Trãi, Thanh Xuân, Hà Nội
                        </a><br>
            </div>
            <div style="margin-top: 10px; font-style: italic; color: #555; font-size: 12px; text-align: right;">
                Số lượt truy cập trang: ' . $visit_count . '
            </div>
        </footer>
        ' . $additional_scripts . '
    </body>
    </html>';
}


// Hàm để xác định nội dung riêng cho từng trang
function get_page_content($page_id) {
    switch ($page_id) {
        case 'home':
            return '<h2>Trang chủ</h2><p>Nội dung trang chủ...</p>';
        case 'about':
            return '<h2>Giới thiệu</h2><p>Thông tin về chúng tôi...</p>';
        case 'contact':
            return '<h2>Liên hệ</h2><p>Thông tin liên hệ...</p>';
        default:
            return '<h2>Trang không tồn tại</h2>';
    }
}

// Cấu hình kết nối database (nếu cần)
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'mydb'
];

// Hàm kết nối database (nếu cần)
function db_connect() {
    global $db_config;
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}
?>
