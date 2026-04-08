<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('image/hus_logo.webp') }}">
    <title>Admin – Cấu hình tuyển sinh</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

<div class="container">
    <!-- Nút Quay lại Trang chủ -->
    <div class="back-to-home">
        <a href="{{ route('home') }}" class="btn-back">
            ← Quay lại Trang chủ
        </a>
    </div>

    <div class="page-header">
        Cấu hình Tuyển sinh
        <span>Năm {{ $cfg['year'] }}</span>
    </div>

    @if (session('success'))
        <div class="alert-success">✓ {{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.save') }}">
        @csrf

        {{-- THÔNG TIN CHUNG --}}
        <div class="section-title">Thông tin chung</div>

        <div class="field-row">
            <div class="field">
                <label>Năm tuyển sinh</label>
                <input type="text" name="year" value="{{ $cfg['year'] }}" placeholder="2025">
            </div>
            <div class="field">
                <label>Ngày thông báo kết quả</label>
                <input type="text" name="announcement_date" value="{{ $cfg['announcement_date'] }}" placeholder="20/5/2025">
            </div>
        </div>

        <div class="field">
            <label>Tên hệ thống</label>
            <input type="text" name="site_name" value="{{ $cfg['site_name'] }}">
        </div>

        {{-- THỜI GIAN --}}
        <div class="section-title">Thời gian nhận hồ sơ</div>

        <div class="field-row">
            <div class="field">
                <label>Thời gian bắt đầu</label>
                <input type="text" name="start_time" value="{{ $cfg['start_time'] }}" placeholder="8h00 ngày 01/03/2025">
            </div>
            <div class="field">
                <label>Thời gian kết thúc</label>
                <input type="text" name="end_time" value="{{ $cfg['end_time'] }}" placeholder="17h00 ngày 31/03/2025">
            </div>
        </div>

        {{-- ĐĂNG KÝ --}}
        <div class="section-title">Đăng ký dự thi</div>

        <div class="field-checkbox">
            <input type="checkbox" name="registration_open" id="registration_open"
                   {{ $cfg['registration_open'] ? 'checked' : '' }}>
            <label for="registration_open">Mở đăng ký (hiện nút "Đăng ký dự thi" trên trang chủ)</label>
        </div>

        <div class="field">
            <label>URL form đăng ký</label>
            <input type="text" name="registration_url" value="{{ $cfg['registration_url'] }}"
                   placeholder="http://tschuyen.hus.vnu.edu.vn/dk/">
            <small>Chỉ dùng khi đã bật "Mở đăng ký" ở trên</small>
        </div>

        {{-- TÀI LIỆU --}}
        <div class="section-title">URL tài liệu</div>

        <div class="field">
            <label>Kế hoạch tuyển sinh</label>
            <input type="text" name="plan_url" value="{{ $cfg['plan_url'] }}">
        </div>

        <div class="field">
            <label>Hướng dẫn đăng ký dự thi</label>
            <input type="text" name="guide_url" value="{{ $cfg['guide_url'] }}">
        </div>

        {{-- GOOGLE SHEET --}}
        <div class="section-title">Google Sheet (export CSV)</div>

        <div class="field">
            <label>Sheet tra cứu trạng thái hồ sơ</label>
            <input type="text" name="sheet_trang_thai_ho_so" value="{{ $cfg['sheet_trang_thai_ho_so'] }}"
                   placeholder="https://docs.google.com/spreadsheets/d/.../export?format=csv">
            <small>Lấy link Share → "Anyone with the link" rồi thêm <code>/export?format=csv</code></small>
        </div>

        <div class="field">
            <label>Sheet tra cứu thông tin dự thi</label>
            <input type="text" name="sheet_thong_tin_du_thi" value="{{ $cfg['sheet_thong_tin_du_thi'] }}"
                   placeholder="https://docs.google.com/spreadsheets/d/.../export?format=csv">
        </div>

        {{-- FILE CSV --}}
        <div class="section-title">Đường dẫn file CSV kết quả</div>

        <div class="field-row">
            <div class="field">
                <label>CSV kết quả thi</label>
                <input type="text" name="csv_ket_qua_thi" value="{{ $cfg['csv_ket_qua_thi'] }}"
                       placeholder="result/kq_thi.csv">
                <small>Tương đối từ thư mục <code>public/</code></small>
            </div>
            <div class="field">
                <label>CSV kết quả phúc khảo</label>
                <input type="text" name="csv_ket_qua_phuc_khao" value="{{ $cfg['csv_ket_qua_phuc_khao'] }}"
                       placeholder="result/kq_phuckhao.csv">
                <small>Tương đối từ thư mục <code>public/</code></small>
            </div>
        </div>

        {{-- TÀI KHOẢN ADMIN --}}
        <div class="section-title">
            Tài khoản admin
            <span class="badge-env">Chỉnh trong file .env</span>
        </div>

        <div class="field-row">
            <div class="field">
                <label>Username</label>
                <input type="text" value="{{ config('tuyen_sinh.admin_user') }}" disabled>
                <small>Sửa <code>ADMIN_USER</code> trong file <code>.env</code></small>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="text" value="••••••••" disabled>
                <small>Sửa <code>ADMIN_PASS</code> trong file <code>.env</code></small>
            </div>
        </div>

        <button type="submit" class="btn-save">💾 Lưu cấu hình</button>
    </form>
</div>

</body>
</html>