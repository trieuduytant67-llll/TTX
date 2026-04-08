<?php
// THÔNG TIN DỰ THI DANG KY HO SO
// AUTHOR: DANG TRUNG DU
// PHONG DAO TAO - TRUONG DAI HOC KHOA HOC TU NHIEN - 2025

global $googleSheetUrl_thongtinduthi;
session_start();
require_once('common/common.php');
require_once('common/basic_function.php');

// Khởi tạo biến
$foundCaseNumberData = null;
$searchAttempted = false;

// Đọc dữ liệu bảng tính
$sheetData = readGoogleSheetCsv($googleSheetUrl_thongtinduthi);
if (!empty($sheetData)) {
    $headerRow = array_shift($sheetData); // Bỏ header
}

// Định nghĩa vị trí các cột dữ liệu quan trọng
$dobColumnIndex = 2;    // Ngày sinh (cột 2)
$phoneColumnIndex = 16; // tel (cột 16)
$emailColumnIndex = 17; // email (cột 17)
$inputParseFormat = 'd/m/y';
$outputFormat = 'd/m/Y';


// Chuẩn hóa dữ liệu ngày sinh, số điện thoại, email và các ô rỗng
foreach ($sheetData as &$rowData) {
    // Chuẩn hóa ngày sinh (định dạng dd/mm/YYYY)
    if (isset($rowData[$dobColumnIndex]) && !empty($rowData[$dobColumnIndex])) {
        $originalDob = $rowData[$dobColumnIndex];
        $dateObj = DateTime::createFromFormat($inputParseFormat, $originalDob);
        if ($dateObj !== false && $dateObj->format($inputParseFormat) === $originalDob) {
            $rowData[$dobColumnIndex] = $dateObj->format($outputFormat);
        }
    } else {
        $rowData[$dobColumnIndex] = '';
    }

    // Chuẩn hóa số điện thoại
    if (isset($rowData[$phoneColumnIndex]) && !empty($rowData[$phoneColumnIndex])) {
        if (substr($rowData[$phoneColumnIndex], 0, 1) !== '0') {
            $rowData[$phoneColumnIndex] = "0" . $rowData[$phoneColumnIndex];
        }
    } else {
        $rowData[$phoneColumnIndex] = '';
    }

    // Email nếu chưa có gán rỗng
    if (!isset($rowData[$emailColumnIndex])) {
        $rowData[$emailColumnIndex] = '';
    }

    // Đảm bảo đủ cột (tối đa 17)
    for ($i = 0; $i <= 17; $i++) {
        if (!isset($rowData[$i])) {
            $rowData[$i] = '';
        }
    }
}
unset($rowData);

// Xử lý POST khi tìm kiếm
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
        $usernameInput = strtolower(excel_trim_clean($_SESSION['search']['so_ho_so']));
        // Tìm trong dữ liệu
        foreach ($sheetData as $rowData) {
            $caseNumber = $rowData[0] ?? '';
            $fullName = $rowData[1] ?? '';
            $nameParts = explode(' ', trim($fullName));
            $lastName = removeVietnameseTones(end($nameParts));
            $generatedUsername = strtolower($lastName . $caseNumber);

            if ($generatedUsername === $usernameInput) {
                $foundCaseNumberData = $rowData;
                // Giữ nguyên chữ cái đầu viết hoa cho session hiển thị
                $_SESSION['search']['so_ho_so'] = registration_num . phpucfirst($lastName) . $caseNumber;
                break;
            }
        }
        // Xóa CAPTCHA khỏi session sau khi sử dụng thành công
        unset($_SESSION['captcha']);
    } else {
        // Nếu CAPTCHA sai, reset kết quả tìm kiếm
        $foundCaseNumberData = null;
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
    <title>Tra cứu thông tin dự thi</title>
    <link rel="icon" type="image/webp" href="image/hus_logo.webp">
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
    <h1>Tra cứu thông tin dự thi THPT Chuyên KHTN</h1>

    <div class="search-section">
        <form method="post" action="">
            <h3>Tên đăng nhập:</h3>
            <input type="text" id="so_ho_so" name="so_ho_so" placeholder="Ví dụ: Dat9, Nam110, Anh311"
                   value="<?php echo htmlspecialchars(getSessionValue('so_ho_so')); ?>">
            <small style="color:#666; font-style:italic;">(Tên viết liền không dấu + số hồ sơ. Ví dụ: <b>Dat9, Nam110,
                    Anh311</b>)</small>

            <!-- CAPTCHA Section -->
            <div style="margin-top: 20px;">
                <h3>Mã xác thực:</h3>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
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
                <input type="submit" name="search_by_case_number" value="Tìm kiếm hồ sơ" class="agree-button">
            </div>
            <div class="lookup-links">
                <a href="find_user_name.php" target="_blank" rel="noopener noreferrer">Quên tên đăng nhập?</a>
            </div>
        </form>

        <?php
        if ($searchAttempted && isset($_POST['search_by_case_number'])) {
            if ($foundCaseNumberData) {
                echo "<div class='result-section'>";
                echo "<h3>THÔNG TIN HỒ SƠ:</h3>";

                echo '<table class="two-columns" style="width:100%">';
                $labels = [
                    0 => 'Số hồ sơ (SHS)',
                    1 => 'Họ và tên',
                    2 => 'Ngày sinh (dd/mm/yyyy)',
                    3 => 'Giới tính',
                    6 => 'Nguyện vọng 1',
                    7 => 'Nguyện vọng 2'
                    // 8 => 'Số báo danh (SBD)'
                ];
                foreach ($labels as $index => $label) {
                    if (isset($foundCaseNumberData[$index])) {
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($label) . "</strong></td>";
                        $displayValue = $foundCaseNumberData[$index];
                        if ($index === 1) {
                            echo "<td><strong>" . htmlspecialchars($displayValue) . "</strong></td>";
                        } elseif ($index === 8) {
                            echo "<td><strong>" . htmlspecialchars($displayValue) . "</strong></td>";
                        } else {
                            echo "<td>" . htmlspecialchars($displayValue) . "</td>";
                        }
                        echo "</tr>";
                    }
                }
                echo "</table>";

                // Thông tin liên hệ
                // echo "<h3>Thông tin liên hệ</h3>";
                // echo '<table class="two-columns" style="width:100%">';
                // if (!empty($foundCaseNumberData[16])) {
                //     echo "<tr><td><strong>Điện thoại</strong></td><td>" . htmlspecialchars($foundCaseNumberData[16]) . "</td></tr>";
                // }
                // if (!empty($foundCaseNumberData[17])) {
                //     echo "<tr><td><strong>Email</strong></td><td>" . htmlspecialchars($foundCaseNumberData[17]) . "</td></tr>";
                // }
                // echo "</table>";

                // Bảng thông tin dự thi
                echo '<div style="margin-top:30px;"></div>';
                echo "<h3>THÔNG TIN DỰ THI: </h3>";
                echo "<div style='text-align: center; margin-bottom: 10px;'>";
                echo "<span style='color: rgb(0, 64, 128); font-weight: bold;'>";
                echo "SỐ BÁO DANH (SBD): </span>";
                echo "<span style='color: #cc0000; font-weight: bold;'>" . $foundCaseNumberData[8] . "</span>";
                echo "</div>";
                echo '<table class="three-columns" style="width:100%">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Môn thi</th>';
                echo '<th>Thời gian</th>';
                echo '<th>Phòng thi</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                $examSchedule = [
                    "Ngữ văn" => "07g30 ngày 01/06/2025",
                    "Toán (vòng 1)" => "13g30 ngày 01/06/2025",
                    "Tiếng Anh" => "16g00 ngày 01/06/2025",

                    "Toán (vòng 2)" => "08g00 ngày 02/06/2025",
                    "Tin học" => "08g00 ngày 02/06/2025",
                    "Sinh học" => "08g00 ngày 02/06/2025",

                    "Vật lý" => "14g00 ngày 02/06/2025",
                    "Hoá học" => "14g00 ngày 02/06/2025"
                ];

                $roomCommon = $foundCaseNumberData[9] ?? '';  // ptMC
                $roomToanChuyen = $foundCaseNumberData[10] ?? ''; // ptTC
                $roomTinChuyen = $foundCaseNumberData[11] ?? '';  // ptIC
                $roomLyChuyen = $foundCaseNumberData[12] ?? '';   // ptLC
                $roomHoaChuyen = $foundCaseNumberData[13] ?? '';  // ptHC
                $roomSinhChuyen = $foundCaseNumberData[14] ?? ''; // ptSC

                // Môn thi chung
                echo "<tr><td>Ngữ văn</td><td>{$examSchedule['Ngữ văn']}</td><td>" . htmlspecialchars($roomCommon) . "</td></tr>";
                echo "<tr><td>Toán (vòng 1)</td><td>{$examSchedule['Toán (vòng 1)']}</td><td>" . htmlspecialchars($roomCommon) . "</td></tr>";
                echo "<tr><td>Tiếng Anh</td><td>{$examSchedule['Tiếng Anh']}</td><td>" . htmlspecialchars($roomCommon) . "</td></tr>";

                // Môn thi chuyên
                // Nguyện vọng 1 (môn chuyên)
                $subjectNV1 = trim($foundCaseNumberData[6]); // NV1
                $roomNV1 = '';
                if ($subjectNV1 === 'Chuyên toán học') {
                    $roomNV1 = $roomToanChuyen;
                    echo "<tr><td>Toán (vòng 2)</td><td>{$examSchedule['Toán (vòng 2)']}</td><td>" . htmlspecialchars($roomNV1) . "</td></tr>";
                } elseif ($subjectNV1 === 'Chuyên tin học (thi Toán)') {
                    $roomNV1 = $roomToanChuyen;
                    echo "<tr><td>Toán (vòng 2)</td><td>{$examSchedule['Toán (vòng 2)']}</td><td>" . htmlspecialchars($roomNV1) . "</td></tr>";
                } elseif ($subjectNV1 === 'Chuyên tin học (thi Tin)') {
                    $roomNV1 = $roomLyChuyen;
                    echo "<tr><td>Tin học</td><td>{$examSchedule['Tin học']}</td><td>" . htmlspecialchars($roomNV1) . "</td></tr>";
                } elseif ($subjectNV1 === 'Chuyên vật lý') {
                    $roomNV1 = $roomLyChuyen;
                    echo "<tr><td>Vật lý</td><td>{$examSchedule['Vật lý']}</td><td>" . htmlspecialchars($roomNV1) . "</td></tr>";
                } elseif ($subjectNV1 === 'Chuyên hóa học') {
                    $roomNV1 = $roomHoaChuyen;
                    echo "<tr><td>Hoá học</td><td>{$examSchedule['Hoá học']}</td><td>" . htmlspecialchars($roomNV1) . "</td></tr>";
                } elseif ($subjectNV1 === 'Chuyên sinh học') {
                    $roomNV1 = $roomSinhChuyen;
                    echo "<tr><td>Sinh học</td><td>{$examSchedule['Sinh học']}</td><td>" . htmlspecialchars($roomNV1) . "</td></tr>";
                }

                // Nguyện vọng 2 (môn chuyên)
                $subjectNV2 = trim($foundCaseNumberData[7]); // NV2
                $roomNV2 = '';
                if ($subjectNV2 === 'Chuyên toán học') {
                    $roomNV2 = $roomToanChuyen;
                    echo "<tr><td>Toán (vòng 2)</td><td>{$examSchedule['Toán (vòng 2)']}</td><td>" . htmlspecialchars($roomNV2) . "</td></tr>";
                } elseif ($subjectNV2 === 'Chuyên tin học (thi Toán)') {
                    $roomNV2 = $roomToanChuyen;
                    echo "<tr><td>Toán (vòng 2)</td><td>{$examSchedule['Toán (vòng 2)']}</td><td>" . htmlspecialchars($roomNV2) . "</td></tr>";
                } elseif ($subjectNV2 === 'Chuyên tin học (thi Tin)') {
                    $roomNV2 = $roomLyChuyen;
                    echo "<tr><td>Tin học</td><td>{$examSchedule['Tin học']}</td><td>" . htmlspecialchars($roomNV2) . "</td></tr>";
                } elseif ($subjectNV2 === 'Chuyên vật lý') {
                    $roomNV2 = $roomLyChuyen;
                    echo "<tr><td>Vật lý</td><td>{$examSchedule['Vật lý']}</td><td>" . htmlspecialchars($roomNV2) . "</td></tr>";
                } elseif ($subjectNV2 === 'Chuyên hóa học') {
                    $roomNV2 = $roomHoaChuyen;
                    echo "<tr><td>Hoá học</td><td>{$examSchedule['Hoá học']}</td><td>" . htmlspecialchars($roomNV2) . "</td></tr>";
                } elseif ($subjectNV2 === 'Chuyên sinh học') {
                    $roomNV2 = $roomSinhChuyen;
                    echo "<tr><td>Sinh học</td><td>{$examSchedule['Sinh học']}</td><td>" . htmlspecialchars($roomNV2) . "</td></tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "<div style='margin-top: 15px'>";
                echo "<strong>Địa điểm thi:</strong> ";
                echo '<a href="https://maps.app.goo.gl/qmzeZ4UC7Psksx819" target="_blank" rel="noopener noreferrer">';
                echo "Trường Đại học Khoa học Tự nhiên, ĐHQGHN - 334 Đ. Nguyễn Trãi, Thanh Xuân Trung, Thanh Xuân, Hà Nội";
                echo '</a>';
                echo "</div>";
                echo "</div>";
            } else {
                echo "<p class='no-results'>Không tìm thấy thông tin cho tên đăng nhập này.</p>";
            }
        }
        ?>
    </div>
    <div class="upload-links">
        <ul>
            <li>Thí sinh xem quy định dự thi:
                <a href="quydinhduthi.php">Tại đây</a>
            </li>
            <li>Thí sinh xem sơ đồ phòng thi:
                <a href="sodotruong.php">Tại đây</a>
                <!-- <a href="." rel="noopener noreferrer" class="disabled">Tại đây (tính năng đang được phát triển)</a> -->

            </li>
            <li>Thí sinh đề nghị chỉnh sửa thông tin bị sai:
                <a href="donsuathongtin.php">Tại đây</a>
            </li>

            <li>Thí sinh in lại phiếu đăng ký dự thi (nếu cần):
                <a href="phieudangkyduthi.php">Tại đây</a>
            </li>
        </ul>
    </div>


    <?php
    display_footer()
    ?>
</div>

</body>

</html>