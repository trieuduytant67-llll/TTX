<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('image/hus_logo.webp') }}">
    <meta property="og:title" content="TS THPT chuyên KHTN">
    <meta property="og:description" content="Tuyển sinh THPT chuyên Khoa học Tự nhiên">
    <meta property="og:url" content="{{ config('app.url') }}">
    <title>Tra cứu trạng thái hồ sơ – THPT Chuyên KHTN</title>
    <link rel="stylesheet" href="{{ asset('css/status.css') }}">
</head>
<body>
<div class="container">
    <h1>Tra cứu trạng thái hồ sơ đăng ký dự thi THPT Chuyên KHTN</h1>

    <div class="search-section">
        <form method="POST" action="{{ route('student-status.search') }}">
            @csrf
            <h3>Tên đăng nhập:</h3>
            <input type="text"
                   id="ten_dang_nhap"
                   name="ten_dang_nhap"
                   placeholder="Ví dụ: Dat9, Nam110, Anh311"
                   value="{{ old('ten_dang_nhap', $tenDangNhap ?? '') }}">
            <small>(Tên viết liền không dấu + số hồ sơ. Ví dụ: <b>Dat9, Nam110, Anh311</b>)</small>

            <div class="captcha-section">
                <h3>Mã xác thực:</h3>
                <div class="captcha-row">
                    <img src="{{ route('captcha', ['key' => 'captcha_status']) }}"
                         id="captcha_image" alt="CAPTCHA">
                    <a href="javascript:void(0)" onclick="refreshCaptcha()"> Làm mới</a>
                </div>
                <input type="text" name="captcha_code" placeholder="Nhập mã trong hình" autocomplete="off">
                @if (!empty($captchaError))
                    <div class="error-msg"> {{ $captchaError }}</div>
                @endif
            </div>

            <button type="submit" class="btn-search">Tìm kiếm hồ sơ</button>

            <div class="lookup-links">
                <a href="#">Quên tên đăng nhập?</a>
            </div>
        </form>

        {{-- ===== KẾT QUẢ TÌM KIẾM ===== --}}
        @if (!empty($searchAttempted) && empty($captchaError))
            @if ($thisinh && $thisinh->hoso)
                @php
                    $hoso = $thisinh->hoso;
                    $nv1  = $hoso->nguyenvong->firstWhere('thu_tu', 1);
                    $nv2  = $hoso->nguyenvong->firstWhere('thu_tu', 2);

                    $formatMon = function(?string $mon): string {
                        if (!$mon) return '—';
                        if (str_contains($mon, 'Tin M')) return 'Tin (thi Toán)';
                        if (str_contains($mon, 'Tin I')) return 'Tin (thi Tin)';
                        return $mon;
                    };

                    $trangThaiLePhi = match($hoso->trang_thai) {
                        'paid'    => ['label' => 'Đã nộp lệ phí',   'class' => 'badge-success'],
                        'pending' => ['label' => 'Chưa nộp lệ phí', 'class' => 'badge-warning'],
                        default   => ['label' => $hoso->trang_thai,  'class' => 'badge-secondary'],
                    };
                @endphp

                <div class="result-section">
                    <h3>Thông tin hồ sơ:</h3>

                    <table class="info-table">
                        <tr>
                            <td>Số hồ sơ</td>
                            <td>{{ $hoso->shs }}</td>
                        </tr>
                        <tr>
                            <td>Họ và tên</td>
                            <td><strong>{{ $thisinh->ho_ten }}</strong></td>
                        </tr>
                        <tr>
                            <td>Giới tính</td>
                            <td>{{ $thisinh->gioi_tinh === 'M' ? 'Nam' : ($thisinh->gioi_tinh === 'F' ? 'Nữ' : $thisinh->gioi_tinh) }}</td>
                        </tr>
                        <tr>
                            <td>Ngày sinh (dd/mm/yyyy)</td>
                            <td>{{ $thisinh->ngay_sinh ? $thisinh->ngay_sinh->format('d/m/Y') : '—' }}</td>
                        </tr>
                        <tr>
                            <td>Dân tộc</td>
                            <td>{{ $thisinh->dantoc->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Nguyện vọng 1</td>
                            <td>{{ $formatMon($nv1?->mon_thi) }}</td>
                        </tr>
                        <tr>
                            <td>Nguyện vọng 2</td>
                            <td>{{ $formatMon($nv2?->mon_thi) }}</td>
                        </tr>
                        <tr>
                            <td>Trạng thái lệ phí</td>
                            <td>
                                <span class="badge {{ $trangThaiLePhi['class'] }}">
                                    {{ $trangThaiLePhi['label'] }}
                                </span>
                            </td>
                        </tr>
                        @if ($thisinh->dien_thoai)
                        <tr>
                            <td>Số điện thoại</td>
                            <td>{{ $thisinh->dien_thoai }}</td>
                        </tr>
                        @endif
                        @if ($thisinh->email)
                        <tr>
                            <td>Email</td>
                            <td>{{ $thisinh->email }}</td>
                        </tr>
                        @endif
                    </table>

                    @if ($hoso->trang_thai === 'paid')
                        <div class="success-message">
                             Hồ sơ của thí sinh đã nộp thành công
                        </div>
                    @endif

                    @if ($hoso->ghi_chu)
                        <div class="notice-box">
                            <strong>Ghi chú:</strong> {{ $hoso->ghi_chu }}
                        </div>
                    @endif
                </div>

            @elseif ($thisinh && !$thisinh->hoso)
                <p class="no-results">Thí sinh chưa có hồ sơ trong hệ thống.</p>
                <div class="notice-box">
                     Lưu ý: Hồ sơ hợp lệ sẽ được hệ thống xem xét và cập nhật muộn nhất sau
                    <strong>3 ngày làm việc</strong> kể từ ngày thí sinh hoàn thành nộp lệ phí.
                </div>
            @else
                <p class="no-results">Không tìm thấy thông tin cho tên đăng nhập này.</p>
                <div class="notice-box">
                     Lưu ý: Hồ sơ hợp lệ sẽ được hệ thống xem xét và cập nhật muộn nhất sau
                    <strong>3 ngày làm việc</strong> kể từ ngày thí sinh hoàn thành nộp lệ phí.
                </div>
            @endif
        @endif
    </div>

    <footer>
        &copy; {{ date('Y') }} Phòng Đào tạo – Trường Đại học Khoa học Tự nhiên – ĐHQGHN
    </footer>
</div>

<script>
function refreshCaptcha() {
    var img = document.getElementById('captcha_image');
    if (img) {
        img.src = '{{ route('captcha', ['key' => 'captcha_status']) }}' + '&rand=' + new Date().getTime();
    }
}
</script>
</body>
</html>
