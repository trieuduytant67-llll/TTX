{{-- views/exam/info.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('image/hus_logo.webp') }}">
    <meta property="og:title" content="TS THPT chuyên KHTN">
    <meta property="og:description" content="Tuyển sinh THPT chuyên Khoa học Tự nhiên">
    <meta property="og:url" content="{{ config('app.url') }}">
    <title>Tra cứu thông tin dự thi</title>
    <link rel="stylesheet" href="{{ asset('css/exam-info.css') }}">
</head>
<body>

<div class="container">
    <h1>Tra cứu thông tin dự thi THPT Chuyên KHTN</h1>

    <div class="search-section">
        <form method="POST" action="{{ route('exam-info.search') }}">
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
                    <img src="{{ route('captcha') }}" id="captcha_image" alt="CAPTCHA">
                    <a href="javascript:void(0)" onclick="refreshCaptcha()">Làm mới</a>
                </div>
                <input type="text" name="captcha_code" placeholder="Nhập mã trong hình" autocomplete="off">
                @if (!empty($captchaError))
                    <div class="error-msg"> {{ $captchaError }}</div>
                @endif
            </div>

            <button type="submit" class="btn-search">Tìm kiếm hồ sơ</button>

            <div class="lookup-links">
                <a href="{{ route('find_user_name') }}">Quên tên đăng nhập?</a>
            </div>
        </form>

        {{-- Kết quả tìm kiếm --}}
        @if (!empty($searchAttempted) && empty($captchaError))
            @if ($thisinh)
                <div class="result-section">
                    <h3>THÔNG TIN HỒ SƠ:</h3>
                    <table class="two-columns">
                        <tr>
                            <td><strong>Số hồ sơ (SHS)</strong></td>
                            <td>{{ $thisinh->hoso->shs ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Họ và tên</strong></td>
                            <td><strong>{{ $thisinh->ho_ten }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Ngày sinh (dd/mm/yyyy)</strong></td>
                            <td>{{ $thisinh->ngay_sinh ? $thisinh->ngay_sinh->format('d/m/Y') : '—' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Giới tính</strong></td>
                            <td>{{ $thisinh->gioi_tinh === 'M' ? 'Nam' : ($thisinh->gioi_tinh === 'F' ? 'Nữ' : $thisinh->gioi_tinh) }}</td>
                        </tr>
                        @if ($thisinh->hoso && $thisinh->hoso->nguyenvong->isNotEmpty())
                            @foreach ($thisinh->hoso->nguyenvong as $nv)
                                <tr>
                                    <td><strong>Nguyện vọng {{ $nv->thu_tu }}</strong></td>
                                    <td>{{ $nv->mon_thi }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </table>

                    <div style="margin-top:28px;"></div>
                    <h3>THÔNG TIN DỰ THI:</h3>

                    <div class="sbd-highlight">
                        <span class="label">SỐ BÁO DANH (SBD): </span>
                        <span class="value">{{ $thisinh->sbd }}</span>
                    </div>

                    <table class="three-columns">
                        <thead>
                            <tr>
                                <th>Môn thi</th>
                                <th>Thời gian</th>
                                <th>Phòng thi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($thisinh->kythi as $kt)
                                <tr>
                                    <td>{{ $kt->mon_thi }}</td>
                                    <td>
                                        {{ $kt->gio_thi ? \Carbon\Carbon::parse($kt->gio_thi)->format('H\gi') : '' }}
                                        ngày {{ $kt->ngay_thi->format('d/m/Y') }}
                                    </td>
                                    <td>{{ $kt->phong_thi ?? '—' }}</td>
                                </tr>
                            @endforeach

                            @foreach ($thisinh->phongthi_chuyen as $ptc)
                                <tr>
                                    <td>{{ $ptc->mon_thi }}</td>
                                    <td>
                                        {{ $ptc->gio_thi ? \Carbon\Carbon::parse($ptc->gio_thi)->format('H\gi') : '' }}
                                        ngày {{ $ptc->ngay_thi->format('d/m/Y') }}
                                    </td>
                                    <td>{{ $ptc->phong_thi ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="diadiem">
                        <strong>Địa điểm thi:</strong>
                        <a href="https://maps.app.goo.gl/qmzeZ4UC7Psksx819" target="_blank" rel="noopener noreferrer">
                            Trường Đại học Khoa học Tự nhiên, ĐHQGHN – 334 Đ. Nguyễn Trãi, Thanh Xuân Trung, Thanh Xuân, Hà Nội
                        </a>
                    </div>
                </div>
            @else
                <p class="no-results">Không tìm thấy thông tin cho tên đăng nhập này.</p>
            @endif
        @endif
    </div>

    <div class="upload-links">
        <ul>
            <li>Thí sinh xem quy định dự thi:
                <a href="{{ route('quydinhduthi') }}">Tại đây</a>
            </li>
            <li>Thí sinh xem sơ đồ phòng thi:
                <a href="{{ route('so-do-truong') }}">Tại đây</a>
            </li>
            <li>Thí sinh đề nghị chỉnh sửa thông tin bị sai:
                <a href="{{ route('donsuathongtin') }}">Tại đây</a>
            </li>
            <li>Thí sinh in lại phiếu đăng ký dự thi (nếu cần):
                <a href="{{ route('phieudangkyduthi') }}">Tại đây</a>
            </li>
        </ul>
    </div>

    <footer>
        &copy; {{ date('Y') }} Phòng Đào tạo – Trường Đại học Khoa học Tự nhiên – ĐHQGHN
    </footer>
</div>

<script>
function refreshCaptcha() {
    var img = document.getElementById('captcha_image');
    if (img) {
        img.src = '{{ route('captcha') }}?rand=' + new Date().getTime();
    }
}
</script>

</body>
</html>
