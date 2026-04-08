<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('image/hus_logo.webp') }}">
    <title>{{ $title ?? 'Tuyễn Sinh Chuyên' }}</title>
    <link rel="stylesheet" href="{{ asset('styles/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home-index.css') }}">
</head>
<body>

    <div class="navbar">
        <a href="#" class="btn">Đăng ký dự thi</a>
        <a href="#" class="btn">Kết quả thi</a>
        <a href="#" class="btn">Quy định</a>
        <a href="#" class="btn">Phúc khảo</a>
    </div>

    <div class="container">
        <h1>🎓 {{ $title ?? 'Hệ thống Tuyễn Sinh Chuyên' }}</h1>

        <div class="alert">
            <strong>Laravel Integration Success!</strong><br>
            Dự án đã được tích hợp thành công vào Laravel framework.<br>
            Năm: {{ $year ?? '2024' }}
        </div>

        <div class="content">
            <h2>Thông tin chung</h2>
            <p>Chào mừng bạn đến với hệ thống tuyễn sinh chuyên.
            Đây là nền tảng cho phép thí sinh đăng ký dự thi, xem kết quả, và thực hiện phúc khảo.</p>

            <h3>Các chức năng chính:</h3>
            <ul>
                <li><strong>Đăng ký dự thi:</strong> Thí sinh có thể đăng ký tham gia kỳ thi</li>
                <li><strong>Xem kết quả:</strong> Tra cứu điểm thi của bản thân</li>
                <li><strong>Phúc khảo:</strong> Yêu cầu làm lại, xóa, hoặc sửa chữa bài thi</li>
                <li><strong>Thông báo:</strong> Cập nhật tình hình kỳ thi</li>
            </ul>

            <h3>Tình trạng hệ thống</h3>
            <ul>
                <li>Laravel framework: Cấu hình thành công</li>
                <li>Database: Chờ migration</li>
                <li>Google Sheets sync: Chờ cấu hình</li>
                <li>Admin panel: Chờ phát triển</li>
            </ul>

            <h3>Tài liệu</h3>
            <p>Xem chi tiết tại: <code>INTEGRATION_GUIDE.md</code></p>
        </div>
    </div>

</body>
</html>
