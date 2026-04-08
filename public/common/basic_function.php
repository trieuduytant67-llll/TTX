<?php

/**
 * Hàm loại bỏ dấu tiếng Việt
 */
function removeVietnameseTones($str)
{
    $accents = [
        'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
        'd' => 'đ',
        'D' => 'Đ',
        'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'i' => 'í|ì|ỉ|ĩ|ị',
        'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
        'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
    ];
    foreach ($accents as $nonAccent => $accentPattern) {
        $str = preg_replace("/$accentPattern/u", $nonAccent, $str);
    }
    return $str;
}

/**
 * Lấy tên cuối từ họ tên đầy đủ
 */
function layTenCuoi($ho_ten)
{
    $ho_ten = trim($ho_ten);
    $ten_parts = explode(' ', $ho_ten);
    return end($ten_parts); // Lấy phần tử cuối cùng
}

/**
 * Tạo tên đăng nhập từ họ tên và số hồ sơ
 */
function taoTenDangNhap($ho_ten, $so_ho_so)
{
    $ten_cuoi = layTenCuoi($ho_ten);
    $ten_khong_dau = removeVietnameseTones($ten_cuoi);
    return $ten_khong_dau . $so_ho_so;
}

/**
 * Loại bỏ ký tự điều khiển, xóa khoảng trắng thừa giống hàm TRIM của Excel
 */
function excel_trim_clean($string)
{
    if (!is_string($string)) {
        $string = (string)$string;
    }
    $string = preg_replace('/[[:cntrl:]]/', '', $string);
    $string = preg_replace('/\s+/', ' ', $string);
    $string = preg_replace('/^[\s\p{Zs}]+|[\s\p{Zs}]+$/u', '', $string);
    return $string;
}

// Hàm để đọc dữ liệu từ Google Sheet URL
function readGoogleSheetCsv($url)
{
    $data = [];
    // Đặt tùy chọn context để bỏ qua lỗi xác thực SSL nếu cần (không khuyến khuyên cho production)
    // Hãy chỉ sử dụng cái này nếu bạn gặp lỗi kết nối SSL và không thể cấu hình chứng chỉ đúng cách.
    // $context = stream_context_create([
    //     'ssl' => [
    //          'verify_peer' => false,
    //          'verify_peer_name' => false,
    //      ],
    //  ]);
    //  if (($handle = fopen($url, "r", false, $context)) !== FALSE) {

    // Cách kết nối thông thường, an toàn hơn:
    if (($handle = fopen($url, "r")) !== FALSE) {
        while (($row = fgetcsv($handle)) !== FALSE) {
            // Áp dụng hàm làm sạch mới cho từng giá trị trong hàng
            // Đảm bảo mỗi phần tử trước khi map là chuỗi
            $cleaned_row = array_map(function ($item) {
                return excel_trim_clean((string)$item); // Ép kiểu về chuỗi trước khi làm sạch
            }, $row);
            $data[] = $cleaned_row;
        }
        fclose($handle);
    }
    return $data;
}

function readLocalCsv($filePath)
{
    $data = [];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($row = fgetcsv($handle)) !== FALSE) {
            $cleaned_row = array_map(function ($item) {
                return excel_trim_clean((string)$item);
            }, $row);
            $data[] = $cleaned_row;
        }
        fclose($handle);
    }
    return $data;
}

function updateVisitCounter($counter_file)
{
    // Mở file với chế độ đọc/ghi và sử dụng file lock
    $file = fopen($counter_file, 'c+');  // 'c+' có thể đọc và ghi
    if ($file) {
        // Đặt file lock để tránh xung đột giữa các tiến trình
        if (flock($file, LOCK_EX)) {  // LOCK_EX là kiểu lock exclusive (chỉ có một tiến trình được phép đọc/ghi)
            $count = (int)fread($file, filesize($counter_file));
            // Nếu file trống, gán giá trị ban đầu là 0
            if ($count === false) {
                $count = 0;
            }
            $count++;  // Tăng giá trị đếm lên 1
            // Di chuyển con trỏ file về đầu file để ghi lại số đếm mới
            ftruncate($file, 0);  // Cắt ngắn file, xóa nội dung hiện tại
            rewind($file);  // Đặt lại con trỏ ở đầu file
            fwrite($file, $count);  // Ghi lại số đếm vào file
            fflush($file);  // Đảm bảo ghi vào disk ngay lập tức
            flock($file, LOCK_UN);  // Tháo khóa
        } else {
            echo "";
        }
        fclose($file);  // Đóng file
    } else {
        echo "";
    }
    // Tăng lượt truy cập lên 1
    $count++;
    // Ghi lại số lượt truy cập mới vào file
    file_put_contents($counter_file, $count);
    sleep(1);  // Đợi 1 giây
}


/**
 * Lấy giá trị từ session search
 */
function getSessionValue($key)
{
    return $_SESSION['search'][$key] ?? '';
}
?>