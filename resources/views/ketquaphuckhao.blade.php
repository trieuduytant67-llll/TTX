@extends('layouts.app')

@section('title', 'Tra cứu kết quả phúc khảo')

@section('content')
<div class="container">
    <h1>TRA CỨU KẾT QUẢ PHÚC KHẢO THI THPT CHUYÊN KHTN</h1>
    <div class="search-section">
        <form method="post" action="{{ route('ketquaphuckhao.search') }}">
            @csrf
            <h3>Tên đăng nhập:</h3>
            <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" placeholder="Ví dụ: Nam112, Dat9, Anh311"
                   value="{{ old('ten_dang_nhap', session('search_phuckhao.ten_dang_nhap', '')) }}" required>
            <small style="color:#666; font-style:italic;">(Tên cuối viết liền không dấu + số hồ sơ. Ví dụ: Nguyễn Văn
                <b>Nam</b> số hồ sơ <b>112</b> → <b>Nam112</b>)</small>

            <!-- CAPTCHA Section -->
            <div style="margin-top: 20px;">
                <h3>Mã xác thực:</h3>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <img src="{{ route('captcha', ['key' => 'phuckhao']) }}" id='captcha_image'
                         name='captcha_image'
                         style="border: 1px solid #ddd; border-radius: 5px;">

                    <a href='javascript: refreshCaptcha();' style="color: #007bff; text-decoration: none;">
                         Làm mới
                    </a>
                </div>
                <input type="text" name="captcha_code" placeholder="Nhập mã trong hình" required>

                @if(isset($captchaError) && !empty($captchaError))
                    <div style="color: #dc3545; font-size: 14px; margin-top: 5px;">
                         {{ $captchaError }}
                    </div>
                @endif
            </div>

            <div style="text-align: center; margin-top: 15px;">
                <input type="submit" name="search_by_username" value="Tra cứu kết quả" class="agree-button">
            </div>
        </form>
    </div>

    @if(isset($searchAttempted) && $searchAttempted)
        @if($foundStudentData)
            <div class='result-section'>
                <h3>THÔNG TIN THÍ SINH:</h3>
                <table class="two-columns" style="width:100%">
                    <tr>
                        <td><strong>Số hồ sơ (SHS)</strong></td>
                        <td>{{ $foundStudentData[$COT['SHS']] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Số báo danh (SBD)</strong></td>
                        <td>{{ $foundStudentData[$COT['SBD']] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Họ và tên</strong></td>
                        <td><strong>{{ $foundStudentData[$COT['HO_TEN']] }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Ngày sinh</strong></td>
                        <td>{{ $foundStudentData[$COT['NGAY_SINH']] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nguyện vọng 1</strong></td>
                        <td>{{ $foundStudentData[$COT['NV1']] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nguyện vọng 2</strong></td>
                        <td>{{ $foundStudentData[$COT['NV2']] }}</td>
                    </tr>
                </table>

                <div style="margin-top:30px;"></div>
                @php
                    $message = $foundStudentData[$COT['TIN_NHAN']];
                @endphp
                @if($message == "THAY ĐỔI ĐIỂM SAU PHÚC KHẢO")
                    <span class='message-success'>{{ $message }}</span>
                @elseif($message == "KHÔNG THAY ĐỔI ĐIỂM SAU PHÚC KHẢO")
                    <span class='message-fail'>{{ $message }}</span>
                @else
                    <span class='message-default'>{{ $message }}</span>
                @endif

                <div style="margin-top:30px;"></div>
                <h3>BẢNG ĐIỂM SAU PHÚC KHẢO:</h3>
                <table class="two-columns" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Môn thi</th>
                            <th>Điểm thi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $diem_labels = [
                                $COT['NGU_VAN'] => 'Ngữ văn',
                                $COT['TOAN_1'] => 'Toán 1',
                                $COT['TIENG_ANH'] => 'Tiếng Anh',
                                $COT['TOAN_2'] => 'Toán 2',
                                $COT['TIN_HOC'] => 'Tin học',
                                $COT['SINH_HOC'] => 'Sinh học',
                                $COT['VAT_LY'] => 'Vật lý',
                                $COT['HOA_HOC'] => 'Hóa học'
                            ];
                        @endphp
                        @foreach($diem_labels as $index => $ten_mon)
                            <tr>
                                <td>{{ $ten_mon }}</td>
                                <td class='score-column'>{{ $foundStudentData[$index] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top:30px;"></div>
                <div style="padding:15px; background-color:#f8f9fa; border-radius:5px; text-align:left;">
                    <h3 style="color:#6c757d;">KẾT QUẢ XÉT TUYỂN</h3>
                    @if(!empty($foundStudentData[$COT['KET_LUAN']]))
                        <div class="message-success">TRÚNG TUYỂN {{ $foundStudentData[$COT['KET_LUAN']] }}</div>
                    @else
                        <div class="message-fail">KHÔNG TRÚNG TUYỂN</div>
                    @endif
                </div>
            </div>
        @else
            <p class='no-results'>Không tìm thấy thông tin cho tên đăng nhập này.</p>
        @endif
    @endif
</div>

<script>
    function refreshCaptcha() {
        var img = document.getElementById('captcha_image');
        if (img) {
            var timestamp = new Date().getTime();
            img.src = '{{ route("captcha", ["key" => "phuckhao"]) }}&rand=' + timestamp;
            img.onerror = function () {
                console.error('Không thể load CAPTCHA mới');
                alert('Lỗi khi tải CAPTCHA. Vui lòng thử lại.');
            };
            img.onload = function () {
                console.log('CAPTCHA đã được refresh thành công');
            };
        }
    }
</script>
@endsection
