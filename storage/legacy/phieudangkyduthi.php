<?php
require_once 'common/common.php';
require_once 'common/basic_function.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Thông Tin Hồ Sơ Dự Thi THPT Chuyên KHTN 2025</title>
    <link rel="stylesheet" href="styles/common.css">
</head>

<body>

    <div class="container">
        <h2 id="message-title">PHIẾU ĐĂNG KÝ DỰ THI<br>THPT CHUYÊN KHTN 2025</h2>

        <div class="message-details">
            <p>
                Phần này dành cho thí sinh đã nộp hồ sơ đăng ký dự thi nhưng chưa in phiếu đăng ký dự thi trong thời hạn quy định.
            </p>
       </div>
          <p>Thí sinh tải phiếu, điền thông tin, dán ảnh và xin xác nhận của trường THCS. Phiếu đầy đủ thông tin được nộp cho Hội đồng thi vào buổi thi đầu tiên.</p>
   
        <div class="button-container">
            <a href="https://docs.google.com/document/d/1eCFKmoYOgVMq-Pj3h13FGmnPS2U6ztN6/edit?usp=sharing&ouid=117779751828361485283&rtpof=true&sd=true" target="_blank" class="agree-button">
                Tải phiếu (.pdf) tại đây
            </a>
        </div>
        <?php
        display_footer()
        ?>
    </div>

</body>

</html>