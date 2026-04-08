<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="<?php echo e(asset('image/hus_logo.webp')); ?>">
    <meta property="og:title" content="TS THPT chuyên KHTN">
    <meta property="og:description" content="Tuyển sinh THPT chuyên Khoa học Tự nhiên">
    <meta property="og:url" content="<?php echo e(config('app.url')); ?>">
    <title>TS THPT chuyên KHTN</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/home.css')); ?>">
</head>
<body>

<div class="center-container">
    <div class="message-box">
        <div id="message-title">
            TUYỂN SINH THPT CHUYÊN KHOA HỌC TỰ NHIÊN NĂM <?php echo e(config('tuyen_sinh.year', date('Y'))); ?>

        </div>

        <div class="message-details">
            <p>
                – Thí sinh cần đọc kỹ
                <a href="<?php echo e(config('tuyen_sinh.plan_url', '#')); ?>" target="_blank" rel="noopener noreferrer">Kế hoạch tuyển sinh của Nhà trường</a>
                và
                <a href="<?php echo e(config('tuyen_sinh.guide_url', '#')); ?>" target="_blank" rel="noopener noreferrer">Hướng dẫn đăng ký dự thi</a>.
            </p>

            <div class="divider"></div>

            <p>
                – Thời gian nộp hồ sơ đăng ký dự thi: từ
                <span class="highlight-blue-bold"><?php echo e(config('tuyen_sinh.start_time', '...')); ?></span>
                đến
                <span class="highlight-blue-bold"><?php echo e(config('tuyen_sinh.end_time', '...')); ?></span>
            </p>
            <p class="highlight-red">
                &nbsp;&nbsp;Các hồ sơ đăng ký ngoài thời gian trên sẽ không được hệ thống ghi nhận.
            </p>

            
            <div class="notice-box">
                <?php if(config('tuyen_sinh.registration_open', false)): ?>
                    <a href="<?php echo e(config('tuyen_sinh.registration_url', '#')); ?>" class="btn-register">
                        Đăng ký dự thi
                    </a>
                <?php else: ?>
                    <button class="btn-expired" disabled>ĐÃ HẾT THỜI HẠN ĐĂNG KÝ HỒ SƠ</button>
                <?php endif; ?>
            </div>

            <div class="divider"></div>

            
            <div class="upload-section">
                <p>Tra cứu</p>
                <div class="upload-links">
                    <span>– <a href="<?php echo e(route('loading', ['target' => 'trang-thai-ho-so'])); ?>">Trạng thái hồ sơ</a></span>
                    <span class="sub-link">
                        + <a href="<?php echo e(route('nop-lai-anh')); ?>" class="link-red">Thí sinh nộp lại ảnh bấm vào đây</a>
                    </span>
                    <span>– <a href="<?php echo e(route('loading', ['target' => 'thong-tin-thi'])); ?>">Thông tin dự thi</a></span>
                    <span>– <a href="<?php echo e(route('loading', ['target' => 'ket-qua-thi'])); ?>">Kết quả thi</a></span>
                    
                </div>
            </div>
        </div>

        <div class="site-footer">
            &copy; <?php echo e(config('tuyen_sinh.year', date('Y'))); ?> Sản phẩm được xây dựng bởi đội ngũ đào tạo Trường ĐHKHTN, ĐHQGHN.<br>
            Liên hệ: Phòng Đào tạo Trường ĐHKHTN, ĐHQGHN.<br>
            Điện thoại: <a href="tel:0886074527">088.607.4527</a>;
            Email: <a href="mailto:daotaodaihoc@hus.edu.vn">daotaodaihoc@hus.edu.vn</a><br>
            Địa chỉ: 334 Nguyễn Trãi, Thanh Xuân, Hà Nội
        </div>
    </div>
</div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\project_web\resources\views/home.blade.php ENDPATH**/ ?>