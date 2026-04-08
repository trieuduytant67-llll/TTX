<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('image/hus_logo.webp') }}">
    <title>Phiếu Đăng Ký Dự Thi – THPT Chuyên KHTN 2025</title>
    <link rel="stylesheet" href="{{ asset('css/phieudangkyduthi.css') }}">
</head>
<body>

<div class="container">
    <h2>Phiếu Đăng Ký Dự Thi<br>THPT Chuyên KHTN 2025</h2>

    <div class="message-details">
        <p>
            Phần này dành cho thí sinh đã nộp hồ sơ đăng ký dự thi nhưng chưa in phiếu đăng ký dự thi trong thời hạn quy định.
        </p>
    </div>

    <p class="note">
        Thí sinh tải phiếu, điền thông tin, dán ảnh và xin xác nhận của trường THCS.
        Phiếu đầy đủ thông tin được nộp cho Hội đồng thi vào buổi thi đầu tiên.
    </p>

    <div class="button-container">
        <a href="https://docs.google.com/document/d/1eCFKmoYOgVMq-Pj3h13FGmnPS2U6ztN6/edit?usp=sharing&ouid=117779751828361485283&rtpof=true&sd=true"
           target="_blank" rel="noopener noreferrer" class="btn-download">
            Tải phiếu (.pdf) tại đây
        </a>
    </div>

    <div class="site-footer">
        &copy; {{ config('tuyen_sinh.year', date('Y')) }} Sản phẩm được xây dựng bởi đội ngũ đào tạo Trường ĐHKHTN, ĐHQGHN.<br>
        Liên hệ: Phòng Đào tạo Trường ĐHKHTN, ĐHQGHN.<br>
        Điện thoại: <a href="tel:0886074527">088.607.4527</a>;
        Email: <a href="mailto:daotaodaihoc@hus.edu.vn">daotaodaihoc@hus.edu.vn</a><br>
        Địa chỉ: 334 Nguyễn Trãi, Thanh Xuân, Hà Nội
    </div>
</div>

</body>
</html>
