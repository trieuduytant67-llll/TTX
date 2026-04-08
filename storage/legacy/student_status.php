<?php
// THÔNG TIN DỰ THI DANG KY HO SO
// AUTHOR: DANG TRUNG DU
// PHONG DAO TAO - TRUONG DAI HOC KHOA HOC TU NHIEN - 2025

global $googleSheetUrl_trangthaihoso;

session_start();
require_once 'common/common.php';
require_once 'common/basic_function.php';

$foundCaseNumberData = null;
$foundPersonalInfoCaseNumber = null;
$searchAttempted = false;
$sheetData = readGoogleSheetCsv($googleSheetUrl_trangthaihoso);
if (!empty($sheetData)) {
    $headerRow = array_shift($sheetData);
}

$dobColumnIndex = 3;
$phoneColumnIndex = 8;
$inputParseFormat = 'd/m/y';
$outputFormat = 'd/m/Y';

foreach ($sheetData as &$rowData) {
    if (isset($rowData[$dobColumnIndex]) && !empty($rowData[$dobColumnIndex])) {
        $originalDob = $rowData[$dobColumnIndex];
        $dateObj = DateTime::createFromFormat($inputParseFormat, $originalDob);
        if ($dateObj !== false && $dateObj->format($inputParseFormat) === $originalDob) {
            $rowData[$dobColumnIndex] = $dateObj->format($outputFormat);
        }
    } else {
        $rowData[$dobColumnIndex] = '';
    }
    if (isset($rowData[$phoneColumnIndex]) && !empty($rowData[$phoneColumnIndex])) {
        if (substr($rowData[$phoneColumnIndex], 0, 1) !== '0') {
            $rowData[$phoneColumnIndex] = "0" . $rowData[$phoneColumnIndex];
        }
    } else {
        $rowData[$phoneColumnIndex] = '';
    }
    $maxIndex = 11;
    for ($i = 0; $i <= $maxIndex; $i++) {
        if (!isset($rowData[$i])) {
            $rowData[$i] = '';
        }
    }
}
unset($rowData);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchAttempted = true;
    $captcha_error = '';

    // ===== KIỂM TRA CAPTCHA TRƯỚC KHI XỬ LÝ TÌM KIẾM =====
    if (isset($_POST['captcha_code'])) {
        $entered_captcha = excel_trim_clean($_POST['captcha_code']);
        if (empty($entered_captcha)) {
            $captcha_error = 'Vui lòng nhập mã xác thực CAPTCHA';
        } elseif (!isset($_SESSION['captcha']) || strtolower($_SESSION['captcha']) !== strtolower($entered_captcha)) {
            $captcha_error = 'Mã CAPTCHA không đúng, vui lòng thử lại';
        }
    } else {
        $captcha_error = 'Vui lòng nhập mã xác thực CAPTCHA';
    }

    // ===== CHỈ XỬ LÝ TÌM KIẾM NẾU CAPTCHA ĐÚNG =====
    if (empty($captcha_error)) {

        if (isset($_POST['so_ho_so'])) {
            $so_ho_so_input = excel_trim_clean($_POST['so_ho_so']);
            $so_ho_so_input = ltrim($so_ho_so_input, '0');
            if ($so_ho_so_input === '') {
                $so_ho_so_input = '0';
            }
            $_SESSION['search']['so_ho_so'] = $so_ho_so_input;
        }

        $_SESSION['search']['ho_va_ten'] = isset($_POST['ho_va_ten']) ? excel_trim_clean($_POST['ho_va_ten']) : '';
        $_SESSION['search']['gioi_tinh'] = isset($_POST['gioi_tinh']) ? excel_trim_clean($_POST['gioi_tinh']) : '';
        $_SESSION['search']['ngay_sinh'] = isset($_POST['ngay_sinh']) ? excel_trim_clean($_POST['ngay_sinh']) : '';
        $_SESSION['search']['so_dien_thoai'] = isset($_POST['so_dien_thoai']) ? excel_trim_clean($_POST['so_dien_thoai']) : '';

        $usedOriginalCaseNumber = false; // thêm dòng này ở đầu khối xử lý search_by_case_number

        if (isset($_POST['search_by_case_number'])) {
            $usernameInput = strtolower(excel_trim_clean($_SESSION['search']['so_ho_so']));

            foreach ($sheetData as $rowData) {
                $caseNumber = $rowData[0] ?? '';
                $fullName = $rowData[1] ?? '';
                $nameParts = explode(' ', trim($fullName));
                $lastName = removeVietnameseTones(end($nameParts));
                $generatedUsername = strtolower($lastName . $caseNumber);

                if ($generatedUsername === $usernameInput) {

                    if (!empty($rowData[11])) {
                        $newCaseNumber = ltrim($rowData[11], '0');
                        if ($newCaseNumber === '') $newCaseNumber = '0';
                        $newGeneratedUsername = strtolower($lastName . $newCaseNumber);

                        foreach ($sheetData as $retryRow) {
                            $retryCaseNumber = $retryRow[0] ?? '';
                            $retryName = $retryRow[1] ?? '';
                            $nameParts2 = explode(' ', trim($retryName));
                            $retryLastName = removeVietnameseTones(end($nameParts2));
                            $retryGeneratedUsername = strtolower($retryLastName . $retryCaseNumber);

                            if ($retryGeneratedUsername === $newGeneratedUsername) {
                                $foundCaseNumberData = $retryRow;
                                $usedOriginalCaseNumber = true; // đánh dấu dùng SHS gốc
                                $_SESSION['search']['so_ho_so'] = ucfirst($newGeneratedUsername);

                                break;
                            }
                        }
                    } else {
                        $foundCaseNumberData = $rowData;
                        $_SESSION['search']['so_ho_so'] = ucfirst($generatedUsername); // cập nhật lại tên đăng nhập gốc
                    }
                    break;
                }
            }
        }

        if (isset($_POST['search_by_personal_info'])) {
            $ho_va_ten_search = $_SESSION['search']['ho_va_ten'];
            $gioi_tinh_search = $_SESSION['search']['gioi_tinh'];
            $ngay_sinh_search = $_SESSION['search']['ngay_sinh'];
            $so_dien_thoai_search = $_SESSION['search']['so_dien_thoai'];

            foreach ($sheetData as $rowData) {
                if (!empty($rowData[0]) && !empty($rowData[1]) && !empty($rowData[2]) && !empty($rowData[3]) && !empty($rowData[8])) {
                    $sheetCaseNumber = $rowData[0];
                    $sheetName = $rowData[1];
                    $sheetGender = $rowData[2];
                    $sheetDob = $rowData[3];
                    $sheetPhone = $rowData[8];

                    $dateMatch = ($sheetDob === $ngay_sinh_search);
                    $phoneMatch = ($sheetPhone === $so_dien_thoai_search);
                    if (!$phoneMatch && substr($so_dien_thoai_search, 0, 1) !== '0') {
                        $phoneMatch = ($sheetPhone === '0' . $so_dien_thoai_search);
                    }

                    if ($sheetName === $ho_va_ten_search && $sheetGender === $gioi_tinh_search && $phoneMatch && $dateMatch) {
                        $foundPersonalInfoCaseNumber = $sheetCaseNumber;
                        break;
                    }
                }
            }
        }

        // Xóa CAPTCHA khỏi session sau khi sử dụng thành công
        unset($_SESSION['captcha']);

    } else {
        // Nếu CAPTCHA sai, reset các biến kết quả tìm kiếm
        $foundCaseNumberData = null;
        $foundPersonalInfoCaseNumber = null;
        $usedOriginalCaseNumber = false;
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="TS THPT chuyên KHTN">
    <meta property="og:description" content="Tuyển sinh THPT chuyên Khoa học Tự nhiên">
    <meta property="og:image" content="./image/hus_logo.webp">
    <meta property="og:url" content="https://tschuyenkhtn.hus.vnu.edu.vn">
    <title>TS THPT chuyên KHTN</title>
    <link rel="icon" type="image/webp" href="./image/hus_logo.webp">
    <title>Tra cứu thông tin</title>
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
    <h1>Tra cứu trạng thái hồ sơ đăng ký dự thi THPT Chuyên KHTN</h1>

    <div class="search-section">
        <form method="post" action="">
            <h3>Tên đăng nhâp:</h3>

            <input type="text" id="so_ho_so" name="so_ho_so" placeholder="Ví dụ: Dat9, Nam110, Anh311"
                   value="<?php echo htmlspecialchars(getSessionValue('so_ho_so')); ?>">
            <small style="color:#666; font-style:italic;">(Tên viết liền không dấu + số hồ sơ. Ví dụ: <b>Dat9, Nam110,
                    Anh311</b>)</small>

            <!-- CAPTCHA Section -->
            <div style="margin-top: 20px;">
                <h3>Mã xác thực:</h3>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <img src="common/captcha.php?rand=<?php echo rand(); ?>" id='captcha_image'
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

            <!-- Nút Tìm kiếm hồ sơ -->
            <div style="text-align: center; margin-top: 15px;">
                <input type="submit" name="search_by_case_number" value="Tìm kiếm hồ sơ" class="agree-button">
            </div>
            <!-- Nút Tìm kiếm hồ sơ -->

            <div class="lookup-links">
                <a href="find_user_name.php" target="_blank" rel="noopener noreferrer"
                   style="font-size: 0.95em; color: #007bff; text-decoration: underline; text-align: center">
                    Quên tên đăng nhập?</a>
            </div>
        </form>


        <?php


        // Hiển thị kết quả tìm kiếm theo số hồ sơ
        if ($searchAttempted && isset($_POST['search_by_case_number'])) {
            if ($foundCaseNumberData) {
                echo "<div class='result-section'>";
                echo "<h3>Thông tin hồ sơ:</h3>";

                if ($usedOriginalCaseNumber) {
                    echo "<div style='color: rgb(0, 64, 128); font-weight: bold; margin-bottom: 10px;'>";
                    echo "Thí sinh vui lòng sử dụng tên đăng nhập và phiếu đăng ký dự thi tương ứng: <span style='color: #cc0000;'>" . htmlspecialchars($_SESSION['search']['so_ho_so']) . "</span>";
                    echo "</div>";
                }

                echo "<table>";
                // echo "<tr><th>Trường thông tin</th><th>Giá trị</th></tr>";

                $labels = [
                    0 => 'Số hồ sơ',
                    1 => 'Họ và tên',
                    2 => 'Giới tính',
                    3 => 'Ngày sinh (dd/mm/yyyy)',
                    4 => 'Dân tộc',
                    5 => 'Nguyện Vọng 1',
                    6 => 'Nguyện Vọng 2',
                    7 => 'Trạng thái lệ phí', // Chỉ số 7
                    8 => 'Số điện thoại',
                    9 => 'Email',
                    10 => 'Trường THCS'
                ];

                foreach ($labels as $index => $label) {
                    if (isset($foundCaseNumberData[$index])) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($label) . "</td>";

                        $displayValue = $foundCaseNumberData[$index];

                        // Nguyện vọng 1
                        if ($index === 5 && strpos($displayValue, 'Tin M') !== false) {
                            $displayValue = "Tin (thi Toán)";
                        }

                        // Nguyện vọng 2
                        if ($index === 6) {
                            if (empty(trim($displayValue))) {
                                $displayValue = "-";
                            } elseif (strpos($displayValue, 'Tin I') !== false) {
                                $displayValue = "Tin (thi Tin)";
                            }
                        }

                        // Trạng thái lệ phí
                        if ($index === 7) {
                            $feeStatusValue = excel_trim_clean($foundCaseNumberData[$index]);
                            if ($feeStatusValue === '1') {
                                $displayValue = "Chưa nộp lệ phí";
                            } elseif ($feeStatusValue === '2') {
                                $displayValue = "Đã nộp lệ phí";
                            }
                        }
                        // Nếu là Họ và tên (index 1) thì in đậm
                        if ($index === 1) {
                            echo "<td><strong>" . htmlspecialchars($displayValue) . "</strong></td>";
                        } else {
                            echo "<td>" . htmlspecialchars($displayValue) . "</td>";
                        }
                        echo "</tr>";
                    }
                }

                echo "</table>";
                // Thêm đoạn này để hiển thị thông báo thành công
                $feeStatusValue = isset($foundCaseNumberData[7]) ? excel_trim_clean($foundCaseNumberData[7]) : '';
                if ($feeStatusValue === '2') {
                    echo "<div class='success-message'>Hồ sơ của thí sinh đã nộp thành công</div>";
                }
                echo "</div>";
            } else {
                echo "<p class='no-results'>Không tìm thấy thông tin cho tên đăng nhập này.</p>";
                echo "<div style='margin-top: 30px; text-align: center;'>";
                echo "<p style='color: #cc0000; font-size: 1.1em; font-weight: 500;'>Lưu ý: Hồ sơ hợp lệ sẽ được hệ thống xem xét và cập nhật muộn nhất sau 3 ngày làm việc kể từ ngày thí sinh hoàn thành nộp lệ phí.</p>";
                echo "</div>";
            }
        }
        ?>
    </div>
    <?php
    display_footer()
    ?>
</div>
</body>
</html>