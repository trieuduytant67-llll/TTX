<?php
// session_start() must be the very first thing in your script
session_start();
require_once 'common/common.php';
require_once 'common/basic_function.php';

// URL của Google Sheet (dạng export)
$googleSheetUrl = 'https://docs.google.com/spreadsheets/d/1Qg4_sWYftdGVfL9I2ew6CAlpyO6gn6oh6ndACg1LRxU/export?format=csv';

$foundCaseNumberData = null; // Dữ liệu tìm thấy khi tìm theo số hồ sơ
$foundPersonalInfoCaseNumber = null; // Số hồ sơ tìm thấy khi tìm theo thông tin cá nhân
$searchAttempted = false; // Biến để kiểm tra xem người dùng đã thử tìm kiếm chưa

// Đọc dữ liệu Google Sheet một lần để sử dụng cho cả hai loại tìm kiếm
$sheetData = readGoogleSheetCsv($googleSheetUrl);

// Bỏ qua hàng tiêu đề nếu có và lưu lại tiêu đề nếu cần
$headerRow = [];
if (!empty($sheetData)) {
    // Áp dụng làm sạch cho cả hàng tiêu đề nếu cần thiết (thường không cần)
    // $headerRow = array_map('excel_trim_clean', array_shift($sheetData));
    $headerRow = array_shift($sheetData); // Giữ nguyên header nếu không cần làm sạch
}

// --- Tiền xử lý dữ liệu: Định dạng lại cột Ngày sinh (index 3) và xử lý Số điện thoại ---
$dobColumnIndex = 3; // Chỉ số cột Ngày sinh
$phoneColumnIndex = 8; // Chỉ số cột Số điện thoại

// Định dạng của chuỗi đầu vào từ sheet (có thể là m/d/yyyy hoặc mm/dd/yyyy)
// Sử dụng 'n/j/Y' parse chính xác hơn cho trường hợp không có số 0.
// $inputParseFormat = 'n/j/Y'; // Tháng (không 0) / Ngày (không 0) / Năm (4 chữ số)
$inputParseFormat = 'd/m/y'; // Tháng (không 0) / Ngày (không 0) / Năm (4 chữ số)
// Định dạng đầu ra mong muốn (luôn có số 0 ở đầu)
$outputFormat = 'd/m/Y'; // Ngày (có 0) / Tháng (có 0) / Năm (4 chữ số)

// Lưu ý: Hàm excel_trim_clean đã được áp dụng khi đọc dữ liệu.
// Chúng ta chỉ cần xử lý định dạng ngày tháng và số điện thoại ở đây.

foreach ($sheetData as &$rowData) { // Sử dụng & để tham chiếu đến phần tử mảng

    // Đảm bảo cột Ngày sinh tồn tại và không rỗng trước khi xử lý
    if (isset($rowData[$dobColumnIndex]) && !empty($rowData[$dobColumnIndex])) {
        $originalDob = $rowData[$dobColumnIndex]; // Dữ liệu đã được làm sạch bởi excel_trim_clean

        // Cố gắng parse ngày sinh theo định dạng đầu vào đã xác định
        $dateObj = DateTime::createFromFormat($inputParseFormat, $originalDob);

        // KIỂM TRA NẾU PARSE THÀNH CÔNG TRƯỚC KHI FORMAT
        // Đồng thời kiểm tra xem chuỗi gốc có khớp hoàn toàn với định dạng đã parse không
        // Điều này giúp tránh các ngày không hợp lệ như 31/02/2023 bị parse sai
        if ($dateObj !== false && $dateObj->format($inputParseFormat) === $originalDob) {
            // Nếu parse thành công và khớp định dạng, định dạng lại theo định dạng đầu ra mong muốn (có số 0)
            $rowData[$dobColumnIndex] = $dateObj->format($outputFormat);
        } else {
            // Xử lý trường hợp không parse được ngày tháng (định dạng sai trong sheet)
            // hoặc parse thành công nhưng không khớp định dạng gốc (ngày không hợp lệ)
            // Giữ nguyên giá trị gốc (đã làm sạch) nếu không parse được hoặc không hợp lệ
            // Hoặc bạn có thể gán một giá trị báo lỗi nào đó nếu muốn
        }
    } else {
        // Nếu cột không tồn tại hoặc rỗng, gán giá trị rỗng để đảm bảo nó tồn tại trong mảng
        $rowData[$dobColumnIndex] = '';
    }


    // Xử lý số điện thoại: đảm bảo cột tồn tại, không rỗng và thêm '0' nếu cần
    if (isset($rowData[$phoneColumnIndex]) && !empty($rowData[$phoneColumnIndex])) {
        // Kiểm tra xem ký tự đầu tiên có phải là '0' không
        if (substr($rowData[$phoneColumnIndex], 0, 1) !== '0') {
            $rowData[$phoneColumnIndex] = "0" . $rowData[$phoneColumnIndex];
        }
    } else {
        // Nếu cột không tồn tại hoặc rỗng, gán giá trị rỗng để đảm bảo nó tồn tại trong mảng
        $rowData[$phoneColumnIndex] = '';
    }

    // Đảm bảo các cột khác cũng tồn tại, gán rỗng nếu thiếu
    // Bạn có thể lặp qua các chỉ số từ 0 đến lớn nhất bạn mong đợi
    $maxIndex = 10; // Dựa vào $labels array của bạn
    for ($i = 0; $i <= $maxIndex; $i++) {
        if (!isset($rowData[$i])) {
            $rowData[$i] = ''; // Gán giá trị rỗng cho các cột bị thiếu
        }
    }
}
unset($rowData); // Bỏ tham chiếu sau khi vòng lặp kết thúc

// --- Kết thúc Tiền xử lý dữ liệu ---


// --- Xử lý khi form được submit (bất kỳ form nào) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchAttempted = true;

    // Lưu tất cả dữ liệu input từ POST vào SESSION sau khi làm sạch
    // Điều này sẽ giữ lại giá trị trên cả hai form bất kể form nào được submit
    // Sử dụng isset() và ternary operator để tránh warning nếu trường không tồn tại trong $_POST
    // $_SESSION['search']['so_ho_so'] = isset($_POST['so_ho_so']) ? excel_trim_clean($_POST['so_ho_so']) : '';

    if (isset($_POST['so_ho_so'])) {
        $so_ho_so_input = excel_trim_clean($_POST['so_ho_so']);

        // Loại bỏ các số 0 ở đầu (chỉ giữ số thật sự)
        $so_ho_so_input = ltrim($so_ho_so_input, '0');

        // Nếu sau khi loại bỏ là chuỗi rỗng (người dùng nhập toàn 0) thì gán lại là '0'
        if ($so_ho_so_input === '') {
            $so_ho_so_input = '0';
        }

        $_SESSION['search']['so_ho_so'] = $so_ho_so_input;
    }


    $_SESSION['search']['ho_va_ten'] = isset($_POST['ho_va_ten']) ? excel_trim_clean($_POST['ho_va_ten']) : '';
    $_SESSION['search']['gioi_tinh'] = isset($_POST['gioi_tinh']) ? excel_trim_clean($_POST['gioi_tinh']) : '';
    $_SESSION['search']['ngay_sinh'] = isset($_POST['ngay_sinh']) ? excel_trim_clean($_POST['ngay_sinh']) : '';
    $_SESSION['search']['so_dien_thoai'] = isset($_POST['so_dien_thoai']) ? excel_trim_clean($_POST['so_dien_thoai']) : '';


    // --- Xử lý form tìm kiếm theo Số hồ sơ (sử dụng giá trị đã lưu trong session) ---
    if (isset($_POST['search_by_case_number'])) {
        // Lấy số hồ sơ từ SESSION (đã được làm sạch khi lưu từ POST)
        $caseNumberToSearch = $_SESSION['search']['so_ho_so'];

        // Tìm kiếm số hồ sơ trong dữ liệu (dữ liệu đã được tiền xử lý và làm sạch)
        $caseNumberColumnIndex = 0; // Chỉ số cột số hồ sơ
        foreach ($sheetData as $rowData) {
            // Dữ liệu trong $rowData đã được làm sạch và format ngày sinh
            // Kiểm tra xem cột số hồ sơ có tồn tại không
            if (isset($rowData[$caseNumberColumnIndex]) && $rowData[$caseNumberColumnIndex] === $caseNumberToSearch) {
                $foundCaseNumberData = $rowData;
                break; // Dừng lại sau khi tìm thấy kết quả đầu tiên
            }
        }
    }

    // --- Xử lý form tìm kiếm theo Thông tin cá nhân (sử dụng giá trị đã lưu trong session) ---
    if (isset($_POST['search_by_personal_info'])) {
        // Lấy thông tin cá nhân từ SESSION (đã được làm sạch khi lưu từ POST)
        $ho_va_ten_search = $_SESSION['search']['ho_va_ten'];
        $gioi_tinh_search = $_SESSION['search']['gioi_tinh'];
        $ngay_sinh_search = $_SESSION['search']['ngay_sinh']; // Input từ user expected dd/mm/yyyy
        $so_dien_thoai_search = $_SESSION['search']['so_dien_thoai'];

        // Định nghĩa các chỉ số cột cho thông tin cá nhân (sử dụng lại các biến đã định nghĩa)
        $caseNumberColumnIndexForPersonalInfoSearch = 0;
        $nameColumnIndex = 1;
        $genderColumnIndex = 2;
        $dobColumnIndexForSearch = 3; // Chỉ số cột Ngày sinh (đã được format dd/mm/yyyy trong $sheetData)
        $ethnicityColumnIndex = 4;
        $phoneColumnIndexForSearch = 8; // Chỉ số cột Số điện thoại

        // Tìm kiếm theo thông tin cá nhân
        foreach ($sheetData as $rowData) {
            // Kiểm tra xem tất cả các cột cần thiết có tồn tại và không rỗng (đảm bảo so sánh chính xác)
            if (
                isset($rowData[$caseNumberColumnIndexForPersonalInfoSearch], $rowData[$nameColumnIndex], $rowData[$genderColumnIndex], $rowData[$dobColumnIndexForSearch], $rowData[$ethnicityColumnIndex], $rowData[$phoneColumnIndexForSearch]) &&
                !empty($rowData[$caseNumberColumnIndexForPersonalInfoSearch]) &&
                !empty($rowData[$nameColumnIndex]) &&
                !empty($rowData[$genderColumnIndex]) &&
                !empty($rowData[$dobColumnIndexForSearch]) && // Kiểm tra ngày sinh có giá trị sau tiền xử lý
                !empty($rowData[$phoneColumnIndexForSearch]) // Kiểm tra số điện thoại có giá trị sau tiền xử lý
            ) {

                // Lấy dữ liệu từ sheet (đã được làm sạch và ngày sinh đã format dd/mm/yyyy)
                $sheetCaseNumber = $rowData[$caseNumberColumnIndexForPersonalInfoSearch];
                $sheetName = $rowData[$nameColumnIndex];
                $sheetGender = $rowData[$genderColumnIndex];
                $sheetDobFormatted = $rowData[$dobColumnIndexForSearch]; // Dữ liệu sheet ở format dd/mm/yyyy
                $sheetEthnicity = $rowData[$ethnicityColumnIndex];
                $sheetPhone = $rowData[$phoneColumnIndexForSearch]; // Đã có '0' nếu ban đầu chưa có

                // --- Logic so sánh Ngày sinh ---
                // So sánh trực tiếp chuỗi dd/mm/yyyy của user input (đã làm sạch)
                // với chuỗi dd/mm/yyyy của sheet data (đã tiền xử lý format)
                $dateMatch = ($sheetDobFormatted === $ngay_sinh_search);


                // --- Kết thúc Logic so sánh Ngày sinh ---

                // --- Logic so sánh Số điện thoại ---
                // Sheet phone đã được tiền xử lý để luôn có '0' ở đầu (nếu ban đầu có giá trị)
                // User input phone có thể có hoặc không có '0'. Cần so sánh cả hai trường hợp.
                // Tốt nhất là so sánh sheet phone với user input phone (đã làm sạch) VÀ sheet phone với user input phone có thêm '0'
                $phoneMatch = ($sheetPhone === $so_dien_thoai_search);

                // Nếu user nhập sđt không có 0, và sđt trong sheet có 0, thì thêm 0 vào user input để so sánh
                if (!$phoneMatch && substr($so_dien_thoai_search, 0, 1) !== '0') {
                    $phoneMatch = ($sheetPhone === '0' . $so_dien_thoai_search);
                }
                // --- Kết thúc Logic so sánh Số điện thoại ---


                // Kết hợp tất cả các điều kiện so sánh
                if (
                    $sheetName === $ho_va_ten_search &&
                    $sheetGender === $gioi_tinh_search &&
                    $phoneMatch && // Sử dụng kết quả so sánh số điện thoại
                    $dateMatch // Sử dụng kết quả so sánh ngày sinh
                ) {

                    // Tìm thấy kết quả, lưu số hồ sơ và dừng lại
                    $foundPersonalInfoCaseNumber = $sheetCaseNumber; // Lưu số hồ sơ từ sheet
                    break; // Dừng lại sau khi tìm thấy kết quả đầu tiên
                }
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
    <meta property="og:description" content="Tuyển sinh THPT chuyên Khoa học Tự nhiên">
    <meta property="og:image" content="./image/hus_logo.webp">
    <meta property="og:url" content="https://tschuyenkhtn.hus.vnu.edu.vn">
    <title>TS THPT chuyên KHTN</title>
    <link rel="icon" type="image/webp" href="./image/hus_logo.webp">
    <title>Tra cứu thông tin</title>
    <link rel="stylesheet" href="styles/common.css">
</head>

<body>

<div class="container">
    <h1>Tra cứu tên đăng nhập</h1>
    <h3>Thông tin thí sinh</h3>
    <div class="search-section">
        <form method="post" action="">
            <label for="ho_va_ten">Họ và tên:</label>
            <input type="text" id="ho_va_ten" name="ho_va_ten"
                   value="<?php echo htmlspecialchars(getSessionValue('ho_va_ten')); ?>" required>

            <label for="gioi_tinh">Giới tính:</label>
            <select id="gioi_tinh" name="gioi_tinh" required>
                <option value="">-- Chọn giới tính --</option>
                <?php
                // Lấy giá trị giới tính từ SESSION nếu có
                $selected_gioi_tinh = getSessionValue('gioi_tinh');
                ?>
                <option value="Nam" <?php echo ($selected_gioi_tinh === 'Nam') ? 'selected' : ''; ?>>Nam</option>
                <option value="Nữ" <?php echo ($selected_gioi_tinh === 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                <option value="Khác" <?php echo ($selected_gioi_tinh === 'Khác') ? 'selected' : ''; ?>>Khác</option>
            </select>

            <label for="ngay_sinh">Ngày sinh (dd/mm/yyyy):</label>
            <input type="text" id="ngay_sinh" name="ngay_sinh"
                   value="<?php echo htmlspecialchars(getSessionValue('ngay_sinh')); ?>" placeholder="dd/mm/yyyy"
                   required>

            <label for="so_dien_thoai">Số điện thoại:</label>
            <input type="text" id="so_dien_thoai" name="so_dien_thoai"
                   value="<?php echo htmlspecialchars(getSessionValue('so_dien_thoai')); ?>" required>

            <!-- <input type="submit" name="search_by_personal_info" value="Tìm số hồ sơ"> -->
            <div style="text-align: center; margin-top: 15px;">
                <input type="submit" name="search_by_personal_info" value="Tìm tên đăng nhập" class="agree-button">
            </div>

        </form>

        <?php
        // Hiển thị kết quả tìm kiếm theo thông tin cá nhân
        if ($searchAttempted && isset($_POST['search_by_personal_info'])) {
            if ($foundPersonalInfoCaseNumber) {
                echo "<div class='result-section'>";
                // Lấy lại dòng dữ liệu từ $sheetData theo số hồ sơ đã tìm được
                $matchedRow = null;
                foreach ($sheetData as $row) {
                    if (isset($row[0]) && $row[0] === $foundPersonalInfoCaseNumber) {
                        $matchedRow = $row;
                        break;
                    }
                }

                if ($matchedRow) {
                    $caseNumber = $matchedRow[0];
                    $fullName = $matchedRow[1];
                    $names = explode(' ', trim($fullName));
                    $lastName = end($names);
                    $username = removeVietnameseTones($lastName) . $caseNumber;
                    echo "<h3>Tên đăng nhập của bạn là:</h3>";
                    echo "<div class='result-box'>" . htmlspecialchars($username) . "</div>";
                    echo "<p class='success-message'>Vui lòng sử dụng tên đăng nhập này để tra cứu thông tin đầy đủ.</p>";
                }
                echo "</div>";
            } else {
                echo "<p class='no-results'>Không tìm thấy số hồ sơ phù hợp với thông tin đã nhập. Vui lòng kiểm tra lại.</p>";
            }
        }
        ?>
    </div>
    <div style="margin-top: 30px; text-align: center;">
        <p style="color: #cc0000; font-size: 1.1em; font-weight: 500;">
            Lưu ý: Hồ sơ hợp lệ sẽ được hệ thống xem xét và cập nhật muộn nhất sau 3 ngày làm việc kể từ ngày thí sinh
            hoàn thành nộp lệ phí.
        </p>
    </div>
    <?php
    display_footer()
    ?>
</div>
</body>
</html>