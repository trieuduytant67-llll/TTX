<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('image/hus_logo.webp') }}">
    <meta property="og:title" content="TS THPT chuyên KHTN">
    <meta property="og:description" content="Tra cứu kết quả thi THPT chuyên Khoa học Tự nhiên">
    <meta property="og:url" content="{{ config('app.url') }}">
    <title>Tra cứu kết quả thi – THPT Chuyên KHTN</title>
    <link rel="stylesheet" href="{{ asset('css/score.css') }}">
</head>
<body>
<div class="container">
    <h1>Tra cứu kết quả thi THPT Chuyên KHTN</h1>

    <div class="search-section">
        <form method="POST" action="{{ route('student-score.search') }}">
            @csrf
            <h3>Tên đăng nhập:</h3>
            <input type="text"
                   id="ten_dang_nhap"
                   name="ten_dang_nhap"
                   placeholder="Ví dụ: Nam112, Dat9, Anh311"
                   value="{{ old('ten_dang_nhap', $tenDangNhap ?? '') }}"
                   required>
            <small>
                (Tên cuối viết liền không dấu + số hồ sơ. Ví dụ: Nguyễn Văn <b>Nam</b> số hồ sơ <b>112</b> → <b>Nam112</b>)
            </small>

            <div class="captcha-section">
                <h3>Mã xác thực:</h3>
                <div class="captcha-row">
                    <img src="{{ route('captcha', ['key' => 'captcha_score']) }}"
                         id="captcha_image" alt="CAPTCHA">
                    <a href="javascript:void(0)" onclick="refreshCaptcha()"> Làm mới</a>
                </div>
                <input type="text" name="captcha_code" placeholder="Nhập mã trong hình" autocomplete="off" required>
                @if (!empty($captchaError))
                    <div class="error-msg"> {{ $captchaError }}</div>
                @endif
            </div>

            <button type="submit" class="btn-search">Tra cứu kết quả</button>
        </form>
    </div>

    {{-- ===== KẾT QUẢ ===== --}}
    @if (!empty($searchAttempted) && empty($captchaError))
        @if ($thisinh)
            @php
                $hoso = $thisinh->hoso;
                $nv1  = $hoso?->nguyenvong->firstWhere('thu_tu', 1);
                $nv2  = $hoso?->nguyenvong->firstWhere('thu_tu', 2);

                $diemChung = $thisinh->kythi->keyBy('mon_thi');
                $diemChuyen = $thisinh->phongthi_chuyen->keyBy('mon_thi');

                $monChung = ['Ngữ văn', 'Toán 1', 'Tiếng Anh'];
                $monDieuKien = ['Ngữ văn', 'Toán 1', 'Tiếng Anh'];
                $monChuyen = ['Toán 2', 'Tin học', 'Sinh học', 'Vật lý', 'Hóa học'];

                $ketLuan = null;
                $duDieuKien = true;
                foreach ($monDieuKien as $mon) {
                    $kt = $diemChung->get($mon);
                    if (!$kt || $kt->diem === null || floatval($kt->diem) < 4.0) {
                        $duDieuKien = false;
                        break;
                    }
                }
                $coMonChuyen = $diemChuyen->filter(fn($r) => $r->diem !== null)->isNotEmpty();
                if ($duDieuKien && $coMonChuyen) {
                    $ketLuan = $nv1?->mon_thi ?? 'trúng tuyển';
                }
            @endphp

            <div class="result-section">
                <h3>THÔNG TIN THÍ SINH:</h3>
                <table class="info-table">
                    <tr>
                        <td>Số hồ sơ (SHS)</td>
                        <td>{{ $hoso?->shs ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Số báo danh (SBD)</td>
                        <td>{{ $thisinh->sbd }}</td>
                    </tr>
                    <tr>
                        <td>Họ và tên</td>
                        <td><strong>{{ $thisinh->ho_ten }}</strong></td>
                    </tr>
                    <tr>
                        <td>Ngày sinh</td>
                        <td>{{ $thisinh->ngay_sinh?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Nguyện vọng 1</td>
                        <td>{{ $nv1?->mon_thi ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Nguyện vọng 2</td>
                        <td>{{ $nv2?->mon_thi ?? '—' }}</td>
                    </tr>
                </table>

                <h3>BẢNG ĐIỂM THI:</h3>
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>Môn thi</th>
                            <th style="text-align:center;">Điểm thi</th>
                            <th>Điều kiện</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monChung as $mon)
                            @php
                                $kt   = $diemChung->get($mon);
                                $diem = $kt?->diem;
                                $isDieuKien = in_array($mon, $monDieuKien);
                            @endphp
                            <tr>
                                <td>{{ $mon }}</td>
                                <td class="score-val {{ $diem === null ? 'empty' : '' }}">
                                    {{ $diem !== null ? number_format($diem, 2) : '—' }}
                                </td>
                                <td class="condition">
                                    @if ($isDieuKien && $diem !== null)
                                        @if (floatval($diem) >= 4.0)
                                            <span class="cond-pass">✓ Đủ điều kiện</span>
                                        @else
                                            <span class="cond-fail">✗ Không đủ điều kiện</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @foreach ($monChuyen as $mon)
                            @php
                                $ptc  = $diemChuyen->get($mon);
                                $diem = $ptc?->diem;
                            @endphp
                            @if ($ptc)
                                <tr>
                                    <td>{{ $mon }}</td>
                                    <td class="score-val {{ $diem === null ? 'empty' : '' }}">
                                        {{ $diem !== null ? number_format($diem, 2) : '—' }}
                                    </td>
                                    <td class="condition"></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                <div class="result-box" style="background:#f8f9fa; border:1px solid #e2e6ea;">
                    <h3>Kết quả xét tuyển</h3>
                    @if ($ketLuan)
                        <div class="message-success">
                             Trúng tuyển {{ $ketLuan }}
                        </div>
                    @else
                        <div class="message-fail">
                            Không trúng tuyển
                        </div>
                    @endif
                </div>
            </div>
        @else
            <p class="no-results">Không tìm thấy thông tin cho tên đăng nhập này.</p>
        @endif
    @endif

    <footer>
        &copy; {{ date('Y') }} Phòng Đào tạo – Trường Đại học Khoa học Tự nhiên – ĐHQGHN
    </footer>
</div>

<script>
function refreshCaptcha() {
    var img = document.getElementById('captcha_image');
    if (img) {
        img.src = '{{ route('captcha', ['key' => 'captcha_score']) }}' + '&rand=' + new Date().getTime();
    }
}
</script>
</body>
</html>
