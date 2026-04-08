<?php
// THÔNG TIN DỰ THI DANG KY HO SO
// AUTHOR: DANG TRUNG DU
// PHONG DAO TAO - TRUONG DAI HOC KHOA HOC TU NHIEN - 2025
global $data_path_kqphuckhao;
session_start();
require_once 'common/common.php';
require_once 'common/basic_function.php';

// Đường dẫn file CSV local
//$data_path = 'result/kqthi.csv';

// Khởi tạo biến
$foundStudentData = null;
$searchAttempted = false;

// Đọc dữ liệu từ file CSV local
$sheetData = readLocalCsv($data_path_kqphuckhao);
// Hiển thị dữ liệu (xuất ra HTML)
//echo "<pre>";
//print_r($sheetData);
//echo "</pre>";

$COT = [
    'STT' => 0,
    'SHS' => 1,
    'SBD' => 2,
    'HO_TEN' => 3,
    'GIOI_TINH' => 4,
    'NGAY_SINH' => 5,
    'NV1' => 6,           // Nguyện vọng 1
    'NV2' => 7,           // Nguyện vọng 2

    'TIENG_ANH' => 8,
    'TOAN_1' => 9,
    'NGU_VAN' => 10,
    'HOA_HOC' => 11,
    'TIN_HOC' => 12,
    'VAT_LY' => 13,
    'SINH_HOC' => 14,
    'TOAN_2' => 15,

    'KET_LUAN' => 16,
    'SDT' => 17,
    'TIN_NHAN' => 18,
];


if (!empty($sheetData)) {
    $headerRow = array_shift($sheetData); // Bỏ header
}

// Chuẩn hóa dữ liệu
foreach ($sheetData as &$rowData) {
    // Chuẩn hóa ngày sinh
    if (isset($rowData[$COT['NGAY_SINH']]) && !empty($rowData[$COT['NGAY_SINH']])) {
        $originalDob = $rowData[$COT['NGAY_SINH']];
        $dateObj = DateTime::createFromFormat('d/m/y', $originalDob);
        if ($dateObj !== false && $dateObj->format('d/m/y') === $originalDob) {
            $rowData[$COT['NGAY_SINH']] = $dateObj->format('d/m/Y');
        }
    } else {
        $rowData[$COT['NGAY_SINH']] = '';
    }

    // Chuẩn hóa số điện thoại
    if (isset($rowData[$COT['SDT']]) && !empty($rowData[$COT['SDT']])) {
        if (substr($rowData[$COT['SDT']], 0, 1) !== '0') {
            $rowData[$COT['SDT']] = "0" . $rowData[$COT['SDT']];
        }
    } else {
        $rowData[$COT['SDT']] = '';
    }

    // Đảm bảo đủ cột (0-17)
    for ($i = 0; $i <= 17; $i++) {
        if (!isset($rowData[$i])) {
            $rowData[$i] = '';
        }
    }
}
unset($rowData);

// Xử lý POST khi tìm kiếm theo tên đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchAttempted = true;
    $captcha_error = '';

    // Kiểm tra CAPTCHA trước khi xử lý tìm kiếm
    if (isset($_POST['captcha_code'])) {
        $entered_captcha = excel_trim_clean($_POST['captcha_code']);
        if (empty($entered_captcha)) {
            $captcha_error = 'Vui lòng nhập mã xác thực CAPTCHA';
        } elseif (!isset($_SESSION['captcha']) || strtolower($_SESSION['captcha']) !== strtolower($entered_captcha)) {
            $captcha_error = 'Mã CAPTCHA không đúng, vui lòng thử lại';
//            echo "<!-- DEBUG: ";
//            echo "Nhập vào: " . $_POST['captcha_code'] . " | ";
//            echo "Đúng là: " . $_SESSION['captcha'];
//            echo " -->";
        }
    } else {
        $captcha_error = 'Vui lòng nhập mã xác thực CAPTCHA';
    }

    // Chỉ xử lý tìm kiếm nếu CAPTCHA đúng
    if (empty($captcha_error) && isset($_POST['ten_dang_nhap'])) {
        $ten_dang_nhap_input = excel_trim_clean($_POST['ten_dang_nhap']);
        $_SESSION['search']['ten_dang_nhap'] = $ten_dang_nhap_input;

        $searchUsername = $_SESSION['search']['ten_dang_nhap'] ?? '';

        // Tìm trong dữ liệu theo tên đăng nhập (tên cuối + số hồ sơ)
        foreach ($sheetData as $rowData) {
            $ho_ten = $rowData[$COT['HO_TEN']] ?? '';
            $so_ho_so = $rowData[$COT['SHS']] ?? '';

            // Tạo tên đăng nhập từ họ tên và số hồ sơ
            $ten_dang_nhap_he_thong = taoTenDangNhap($ho_ten, $so_ho_so);

            // So sánh không phân biệt hoa thường
            if (strtolower($ten_dang_nhap_he_thong) === strtolower($searchUsername)) {
                $foundStudentData = $rowData;
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="TS THPT chuyên KHTN">
    <meta property="og:description" content="Tra cứu kết quả phúc khảo thi THPT chuyên Khoa học Tự nhiên">
    <meta property="og:image" content="./image/hus_logo.webp">
    <meta property="og:url" content="https://tschuyenkhtn.hus.vnu.edu.vn">
    <title>Tra cứu kết quả phúc khảo</title>
    <link rel="icon" type="image/webp" href="./image/hus_logo.webp">
    <link rel="stylesheet" href="styles/common.css">
    <script>
        function refreshCaptcha() {
            var img = document.getElementById('captcha_image');
            if (img) {
                // Thêm timestamp để tránh cache
                var timestamp = new Date().getTime();
                img.src = 'common/captcha.php?refresh=1&rand=' + timestamp;

                // Xử lý lỗi khi load ảnh
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
</head>

<body>
<div class="container">
    <h1>TRA CỨU KẾT QUẢ PHÚC KHẢO THI THPT CHUYÊN KHTN</h1>
    <div class="search-section">
        <form method="post" action="">
            <h3>Tên đăng nhập:</h3>
            <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" placeholder="Ví dụ: Nam112, Dat9, Anh311"
                   value="<?php echo htmlspecialchars(getSessionValue('ten_dang_nhap')); ?>" required>
            <small style="color:#666; font-style:italic;">(Tên cuối viết liền không dấu + số hồ sơ. Ví dụ: Nguyễn Văn
                <b>Nam</b> số hồ sơ <b>112</b> → <b>Nam112</b>)</small>

            <!-- CAPTCHA Section -->
            <div style="margin-top: 20px;">
                <h3>Mã xác thực:</h3>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <!--                    <img src="common/captcha.php?rand=-->
                    <?php //echo rand(); ?><!--" id='captcha_image'-->
                    <!--                         style="border: 1px solid #ddd; border-radius: 5px;">-->
                    <img src="common/captcha.php?rand=<?php echo rand(); ?>"
                         id='captcha_image'
                         name='captcha_image'
                         style="border: 1px solid #ddd; border-radius: 5px;">

                    <a href='javascript: refreshCaptcha();' style="color: #007bff; text-decoration: none;">
                        🔄 Làm mới
                    </a>
                </div>
                <input type="text" name="captcha_code" placeholder="Nhập mã trong hình" required>

                <?php if (isset($captcha_error) && !empty($captcha_error)): ?>
                    <div style="color: #dc3545; font-size: 14px; margin-top: 5px;">
                        ⚠️ <?php echo htmlspecialchars($captcha_error); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 15px;">
                <input type="submit" name="search_by_username" value="Tra cứu kết quả" class="agree-button">
            </div>
        </form>

    </div>

    <?php
    if ($searchAttempted && isset($_POST['search_by_username'])) {
        if ($foundStudentData) {
            // Tạo tên đăng nhập để hiển thị
            $ho_ten = $foundStudentData[$COT['HO_TEN']] ?? '';
            $so_ho_so = $foundStudentData[$COT['SHS']] ?? '';
            $ten_dang_nhap_hien_thi = taoTenDangNhap($ho_ten, $so_ho_so);

            echo "<div class='result-section'>";
            echo "<h3>THÔNG TIN THÍ SINH:</h3>";

            // Bảng thông tin cơ bản
            echo '<table class="two-columns" style="width:100%">';
//                echo "<tr>";
//                echo "<td><strong>Tên đăng nhập</strong></td>";
//                echo "<td><strong>" . htmlspecialchars($ten_dang_nhap_hien_thi) . "</strong></td>";
//                echo "</tr>";
            echo "<tr>";
            echo "<td><strong>Số hồ sơ (SHS)</strong></td>";
            echo "<td>" . htmlspecialchars($foundStudentData[$COT['SHS']]) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td><strong>Số báo danh (SBD)</strong></td>";
            echo "<td>" . htmlspecialchars($foundStudentData[$COT['SBD']]) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td><strong>Họ và tên</strong></td>";
            echo "<td><strong>" . htmlspecialchars($foundStudentData[$COT['HO_TEN']]) . "</strong></td>";
            echo "</tr>";
//                echo "<tr>";
//                echo "<td><strong>Giới tính</strong></td>";
//                echo "<td>" . htmlspecialchars($foundStudentData[$COT['GIOI_TINH']]) . "</td>";
//                echo "</tr>";
            echo "<td><strong>Ngày sinh</strong></td>";
            echo "<td>" . htmlspecialchars($foundStudentData[$COT['NGAY_SINH']]) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td><strong>Nguyện vọng 1</strong></td>";
            echo "<td>" . htmlspecialchars($foundStudentData[$COT['NV1']]) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td><strong>Nguyện vọng 2</strong></td>";
            echo "<td>" . htmlspecialchars($foundStudentData[$COT['NV2']]) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "</table>";

            echo '<div style="margin-top:30px;"></div>';
            // Kiểm tra nội dung và thay đổi màu sắc
            $message = htmlspecialchars($foundStudentData[$COT['TIN_NHAN']]);
            if ($message == "THAY ĐỔI ĐIỂM SAU PHÚC KHẢO") {
                // Nếu là "THAY ĐỔI ĐIỂM SAU PHÚC KHẢO", hiển thị màu xanh
                echo "<span class='message-success'>" . $message . "</span>";
            } elseif ($message == "KHÔNG THAY ĐỔI ĐIỂM SAU PHÚC KHẢO") {
                // Nếu là "KHÔNG THAY ĐỔI ĐIỂM SAU PHÚC KHẢO", hiển thị màu đỏ
                echo "<span class='message-fail'>" . $message . "</span>";
            } else {
                echo "<span class='message-default'>" . $message . "</span>";
            }


            // Bảng điểm thi

            echo '<div style="margin-top:30px;"></div>';
            echo "<h3>BẢNG ĐIỂM SAU PHÚC KHẢO:</h3>";
            echo '<table class="two-columns" style="width:100%;">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Môn thi</th>';
            echo '<th>Điểm thi</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

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

            foreach ($diem_labels as $index => $ten_mon) {
                $diem = $foundStudentData[$index] ?? '';

                echo "<tr>";
                echo "<td>" . htmlspecialchars($ten_mon) . "</td>";
                echo "<td class='score-column'>" . htmlspecialchars($diem) . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";


            echo '<div style="margin-top:30px;"></div>';
            echo '<div style="padding:15px; background-color:#f8f9fa; border-radius:5px; text-align:left;">';
            echo '<h3 style="color:#6c757d;">KẾT QUẢ XÉT TUYỂN</h3>';
            if (!empty($foundStudentData[$COT['KET_LUAN']])) {
                echo '<div class="message-success">TRÚNG TUYỂN ' . htmlspecialchars($foundStudentData[$COT['KET_LUAN']]) . '</div>';
            } else {
                echo '<div class="message-fail">KHÔNG TRÚNG TUYỂN</div>';
            }
            echo '</div>';

        } else {
            echo "<p class='no-results'>Không tìm thấy thông tin cho tên đăng nhập này.</p>";
        }
    }
    ?>
    <?php
    display_footer()
    ?>
</div>

</body>

</html>
