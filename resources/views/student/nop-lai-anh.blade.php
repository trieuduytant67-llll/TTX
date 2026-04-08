<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('image/hus_logo.webp') }}">
    <title>Nộp Bổ Sung Ảnh Dự Thi – THPT Chuyên KHTN 2025</title>
    <link rel="stylesheet" href="{{ asset('css/nop-lai-anh.css') }}">
</head>
<body>

<div class="container">
    <h2>Nộp Bổ Sung Ảnh Dự Thi<br>THPT Chuyên KHTN 2025</h2>

    <div class="message-details">
        <p>
            <strong>Thông báo:</strong><br>
            Phần này dành riêng cho các thí sinh đã nộp hồ sơ đăng ký dự thi nhưng nộp ảnh
            không đúng quy cách theo yêu cầu của Hội đồng tuyển sinh. Những thí sinh này sẽ
            nhận được <strong>EMAIL</strong> hoặc <strong>TIN NHẮN VĂN BẢN</strong> đến số
            điện thoại đã đăng ký trong hồ sơ dự thi, yêu cầu nộp lại ảnh theo đúng quy cách.
            Thí sinh phải nộp lại ảnh theo đúng quy cách sau:
        </p>
        <ul>
            <li>Ảnh chân dung rõ khuôn mặt, không đội mũ, không có dấu đỏ trên ảnh, nhìn chính diện, nền trắng hoặc nền xanh.</li>
            <li>Kích thước ảnh: <span class="highlight-blue-bold">4x6cm</span></li>
            <li>File ảnh định dạng: <span class="highlight-blue-bold">JPG</span>, dung lượng không quá <span class="highlight-blue-bold">10MB</span></li>
            <li>Thời hạn hoàn thành: trước <span class="highlight-red-bold">24h00, ngày 13/05/2025</span>.</li>
            <li>File ảnh đặt tên theo quy tắc: <span class="highlight-blue-bold">SHS_HoTen.jpg</span> (Ví dụ: 123_NguyenVanDuc.jpg)</li>
        </ul>
    </div>

    <div class="button-container">
        <a href="https://forms.gle/eTSybHHk8BckRF4m6" target="_blank" rel="noopener noreferrer" class="btn-submit">
            Nộp lại ảnh tại đây (Google Forms)
        </a>
    </div>

    <div class="upload-links">
        <ul>
            <li>
                Thí sinh xem lại ảnh đã nộp sai:
                <a href="https://drive.google.com/drive/folders/1pL1yQmCx4Gmjmb5KRtYYBRC35ZZWVrur?usp=sharing"
                   target="_blank" rel="noopener noreferrer">Tại đây</a>
            </li>
            <li>
                Thí sinh xem ảnh đã nộp lại:
                <a href="https://drive.google.com/drive/folders/19o1VLD3djGdKFCT8UNmXNrJihsauX0Z9C96Ga-dVZhld9kHCSPHMsJ-tY0BDa8zzIgJs8UG-?usp=sharing"
                   target="_blank" rel="noopener noreferrer">Tại đây</a>
            </li>
        </ul>
    </div>

    <div class="warning-box">
        Lưu ý: Đối với các thí sinh được HĐTS gửi EMAIL hoặc TIN NHẮN VĂN BẢN yêu cầu nộp lại ảnh.
        Nếu không nộp lại ảnh theo yêu cầu, hồ sơ đăng ký dự thi sẽ không hợp lệ và không được tham dự thi.
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
