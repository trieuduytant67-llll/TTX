<?php
/**
 * File import dữ liệu từ Google Sheets sang MySQL
 */

require_once 'db_config.php';
require_once 'db_schema.php';
require_once 'common.php';

class DataImporter {
    private $conn;
    private $googleSheetUrl_trangthai;
    private $googleSheetUrl_thongtin;
    
    public function __construct($connection) {
        $this->conn = $connection;
        // Lấy URL từ biến toàn cục
        global $googleSheetUrl_trangthaihoso, $googleSheetUrl_thongtinduthi;
        $this->googleSheetUrl_trangthai = $googleSheetUrl_trangthaihoso;
        $this->googleSheetUrl_thongtin = $googleSheetUrl_thongtinduthi;
    }
    
    /**
     * Lấy dữ liệu CSV từ URL Google Sheets
     */
    public function fetchCSVFromGoogle($url) {
        try {
            $csv_content = file_get_contents($url);
            if ($csv_content === false) {
                throw new Exception("Không thể lấy dữ liệu từ URL: " . $url);
            }
            return $csv_content;
        } catch (Exception $e) {
            echo "Lỗi: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    /**
     * Chuyển CSV thành mảng
     */
    public function csvToArray($csv_content) {
        $lines = explode("\n", $csv_content);
        $array = [];
        $headers = [];
        
        foreach ($lines as $index => $line) {
            $data = str_getcsv($line);
            
            if ($index === 0) {
                $headers = $data;
            } else {
                if (!empty(array_filter($data))) {
                    $row = [];
                    foreach ($headers as $key => $header) {
                        $row[trim($header)] = isset($data[$key]) ? trim($data[$key]) : '';
                    }
                    $array[] = $row;
                }
            }
        }
        
        return $array;
    }
    
    /**
     * Import dữ liệu DANTOC (Dân tộc)
     */
    public function importDanToc() {
        echo "<h3>🔄 Đang import dữ liệu Dân tộc...</h3>";
        
        // Danh sách dân tộc (có thể thêm từ dữ liệu thực tế)
        $dantocs = [
            'Kinh', 'Tầy', 'Hoa', 'Khmer', 'Mường', 'Thái', 'Việt', 'Khác'
        ];
        
        foreach ($dantocs as $dantoc) {
            $sql = "INSERT IGNORE INTO DANTOC (TenDanToc) VALUES (?)";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                echo "✗ Lỗi chuẩn bị câu lệnh DANTOC: " . $this->conn->error . "<br>";
                return false;
            }
            
            $stmt->bind_param("s", $dantoc);
            
            if (!$stmt->execute()) {
                echo "✗ Lỗi insert dân tộc '{$dantoc}': " . $stmt->error . "<br>";
            }
            
            $stmt->close();
        }
        
        echo "✓ Dữ liệu dân tộc đã được import<br>";
        return true;
    }
    
    /**
     * Import dữ liệu ThiSinh từ Google Sheets
     */
    public function importThiSinh() {
        echo "<h3>🔄 Đang import dữ liệu Thí sinh...</h3>";
        
        // Lấy dữ liệu từ Google Sheets (thông tin dự thi)
        $csv_content = $this->fetchCSVFromGoogle($this->googleSheetUrl_thongtin);
        if (!$csv_content) return false;
        
        $data = $this->csvToArray($csv_content);
        $count = 0;
        
        foreach ($data as $row) {
            // Xử lý mapping dữ liệu từ Google Sheets
            $sbd = isset($row['SBD']) || isset($row['Mã Thí Sinh']) ? 
                   ($row['SBD'] ?? $row['Mã Thí Sinh']) : '';
            $hoten = isset($row['Họ tên']) ? $row['Họ tên'] : '';
            $ngaysinh = isset($row['Ngày sinh']) ? $row['Ngày sinh'] : NULL;
            $gioitinh = isset($row['Giới tính']) ? $row['Giới tính'] : '';
            $noisinh = isset($row['Nơi sinh']) ? $row['Nơi sinh'] : '';
            $hokhau = isset($row['Hộ khẩu']) ? $row['Hộ khẩu'] : '';
            $dantoc = isset($row['Dân tộc']) ? $row['Dân tộc'] : 'Kinh';
            $cccd = isset($row['CCCD']) ? $row['CCCD'] : '';
            $dienthoai = isset($row['Điện thoại']) ? $row['Điện thoại'] : '';
            $email = isset($row['Email']) ? $row['Email'] : '';
            
            // Bỏ qua nếu không có SBD
            if (empty($sbd) || empty($hoten)) continue;
            
            // Lấy MaDanToc
            $madan = $this->getMaDanToc($dantoc);
            
            // Chuyển ngày sang định dạng MySQL
            if (!empty($ngaysinh)) {
                $ngaysinh = $this->formatDate($ngaysinh);
            } else {
                $ngaysinh = NULL;
            }
            
            $sql = "INSERT IGNORE INTO THISINH 
                    (SBD, HoTen, NgaySinh, GioiTinh, NoiSinh, HoKhau, MaDanToc, CCCD, DienThoai, Email) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                echo "✗ Lỗi chuẩn bị câu lệnh ThiSinh: " . $this->conn->error . "<br>";
                continue;
            }
            
            $stmt->bind_param("ssssssssss", 
                $sbd, $hoten, $ngaysinh, $gioitinh, $noisinh, $hokhau, $madan, $cccd, $dienthoai, $email);
            
            if ($stmt->execute()) {
                $count++;
            } else {
                echo "✗ Lỗi insert thí sinh '{$hoten}' (SBD: {$sbd}): " . $stmt->error . "<br>";
            }
            
            $stmt->close();
        }
        
        echo "✓ Đã import {$count} thí sinh<br>";
        return true;
    }
    
    /**
     * Import dữ liệu HoSo từ Google Sheets
     */
    public function importHoSo() {
        echo "<h3>🔄 Đang import dữ liệu Hồ sơ...</h3>";
        
        // Lấy dữ liệu trạng thái hồ sơ
        $csv_content = $this->fetchCSVFromGoogle($this->googleSheetUrl_trangthai);
        if (!$csv_content) return false;
        
        $data = $this->csvToArray($csv_content);
        $count = 0;
        
        foreach ($data as $row) {
            // Xử lý mapping dữ liệu
            $shs = isset($row['SHS']) || isset($row['Số Hồ Sơ']) ? 
                   ($row['SHS'] ?? $row['Số Hồ Sơ']) : '';
            $sbd = isset($row['SBD']) || isset($row['Mã Thí Sinh']) ? 
                   ($row['SBD'] ?? $row['Mã Thí Sinh']) : '';
            $trangthai = isset($row['Trạng thái']) ? $row['Trạng thái'] : '';
            $truongthcs = isset($row['Trường THCS']) ? $row['Trường THCS'] : '';
            $shsmoi = isset($row['SHS Mới']) ? $row['SHS Mới'] : '';
            $ghichu = isset($row['Ghi chú']) ? $row['Ghi chú'] : '';
            
            // Bỏ qua nếu không có SHS hoặc SBD
            if (empty($shs) || empty($sbd)) continue;
            
            $sql = "INSERT IGNORE INTO HOSO 
                    (SHS, SBD, TrangThai, TruongTHCS, SHSMoi, GhiChu) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                echo "✗ Lỗi chuẩn bị câu lệnh HoSo: " . $this->conn->error . "<br>";
                continue;
            }
            
            $stmt->bind_param("ssssss", $shs, $sbd, $trangthai, $truongthcs, $shsmoi, $ghichu);
            
            if ($stmt->execute()) {
                $count++;
            } else {
                echo "✗ Lỗi insert hồ sơ (SHS: {$shs}): " . $stmt->error . "<br>";
            }
            
            $stmt->close();
        }
        
        echo "✓ Đã import {$count} hồ sơ<br>";
        return true;
    }
    
    /**
     * Import dữ liệu NguyenVong
     */
    public function importNguyenVong() {
        echo "<h3>🔄 Đang import dữ liệu Nguyên vọng...</h3>";
        
        // Lấy dữ liệu từ dbo
        $csv_content = $this->fetchCSVFromGoogle($this->googleSheetUrl_thongtin);
        if (!$csv_content) return false;
        
        $data = $this->csvToArray($csv_content);
        $count = 0;
        
        foreach ($data as $row) {
            $shs = isset($row['SHS']) || isset($row['Số Hồ Sơ']) ? 
                   ($row['SHS'] ?? $row['Số Hồ Sơ']) : '';
            
            if (empty($shs)) continue;
            
            // Giả sử Google Sheets có các cột nguyên vọng
            // Bạn cần điều chỉnh theo cấu trúc thực tế của Google Sheets
            $nguyenvong = isset($row['Nguyên vọng']) ? $row['Nguyên vọng'] : '';
            $thutu = isset($row['Thứ tự']) ? (int)$row['Thứ tự'] : 1;
            
            if (empty($nguyenvong)) continue;
            
            $sql = "INSERT INTO NGUYENVONG 
                    (SHS, TenNguyenVong, ThuTuNV) 
                    VALUES (?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                echo "✗ Lỗi chuẩn bị câu lệnh NguyenVong: " . $this->conn->error . "<br>";
                continue;
            }
            
            $stmt->bind_param("ssi", $shs, $nguyenvong, $thutu);
            
            if ($stmt->execute()) {
                $count++;
            }
            
            $stmt->close();
        }
        
        echo "✓ Đã import {$count} nguyên vọng<br>";
        return true;
    }
    
    /**
     * Import dữ liệu KyThi
     */
    public function importKyThi() {
        echo "<h3>🔄 Đang import dữ liệu Kỳ thi...</h3>";
        
        $csv_content = $this->fetchCSVFromGoogle($this->googleSheetUrl_thongtin);
        if (!$csv_content) return false;
        
        $data = $this->csvToArray($csv_content);
        $count = 0;
        
        foreach ($data as $row) {
            $sbd = isset($row['SBD']) || isset($row['Mã Thí Sinh']) ? 
                   ($row['SBD'] ?? $row['Mã Thí Sinh']) : '';
            $shs = isset($row['SHS']) || isset($row['Số Hồ Sơ']) ? 
                   ($row['SHS'] ?? $row['Số Hồ Sơ']) : '';
            $phongthi = isset($row['Phòng thi chung']) ? $row['Phòng thi chung'] : '';
            
            if (empty($sbd) || empty($shs)) continue;
            
            $sql = "INSERT IGNORE INTO KYTHI 
                    (SBD, SHS, PhongThiChung) 
                    VALUES (?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                echo "✗ Lỗi chuẩn bị câu lệnh KyThi: " . $this->conn->error . "<br>";
                continue;
            }
            
            $stmt->bind_param("sss", $sbd, $shs, $phongthi);
            
            if ($stmt->execute()) {
                $count++;
            }
            
            $stmt->close();
        }
        
        echo "✓ Đã import {$count} kỳ thi<br>";
        return true;
    }
    
    /**
     * Import dữ liệu PhongThi Chuyên
     */
    public function importPhongThiChuyen() {
        echo "<h3>🔄 Đang import dữ liệu Phòng thi chuyên...</h3>";
        
        $csv_content = $this->fetchCSVFromGoogle($this->googleSheetUrl_thongtin);
        if (!$csv_content) return false;
        
        $data = $this->csvToArray($csv_content);
        $count = 0;
        
        foreach ($data as $row) {
            $sbd = isset($row['SBD']) || isset($row['Mã Thí Sinh']) ? 
                   ($row['SBD'] ?? $row['Mã Thí Sinh']) : '';
            $mon = isset($row['Môn chuyên']) ? $row['Môn chuyên'] : '';
            $sophong = isset($row['Số phòng']) ? $row['Số phòng'] : '';
            
            if (empty($sbd)) continue;
            
            $sql = "INSERT INTO PHONGTHI_CHUYEN 
                    (SBD, MonChuyen, SoPhong) 
                    VALUES (?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                echo "✗ Lỗi chuẩn bị câu lệnh PhongThiChuyen: " . $this->conn->error . "<br>";
                continue;
            }
            
            $stmt->bind_param("sss", $sbd, $mon, $sophong);
            
            if ($stmt->execute()) {
                $count++;
            }
            
            $stmt->close();
        }
        
        echo "✓ Đã import {$count} phòng thi chuyên<br>";
        return true;
    }
    
    /**
     * Lấy MaDanToc từ TenDanToc
     */
    private function getMaDanToc($tenDanToc) {
        $tenDanToc = trim($tenDanToc);
        if (empty($tenDanToc)) $tenDanToc = 'Kinh';
        
        $sql = "SELECT MaDanToc FROM DANTOC WHERE TenDanToc = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            return 1; // Mặc định là Kinh
        }
        
        $stmt->bind_param("s", $tenDanToc);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['MaDanToc'];
        }
        
        $stmt->close();
        return 1; // Mặc định là Kinh
    }
    
    /**
     * Chuyển đổi định dạng ngày
     */
    private function formatDate($date_str) {
        // Thử các format khác nhau
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $date_str);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        
        return NULL;
    }
    
    /**
     * Chạy import toàn bộ
     */
    public function importAll() {
        echo "<div style='background-color: #f0f0f0; padding: 15px; border-radius: 5px;'>";
        echo "<h2>📊 Bắt đầu import dữ liệu từ Google Sheets</h2>";
        
        // Xóa dữ liệu cũ (tùy chọn)
        // $this->clearAllData();
        
        // Import theo thứ tự
        $this->importDanToc();
        $this->importThiSinh();
        $this->importHoSo();
        $this->importNguyenVong();
        $this->importKyThi();
        $this->importPhongThiChuyen();
        
        echo "<h2 style='color: green;'>✓ Import hoàn tất!</h2>";
        echo "</div>";
    }
    
    /**
     * Xóa tất cả dữ liệu (cẩn thận!)
     */
    public function clearAllData() {
        $tables = ['PHONGTHI_CHUYEN', 'KYTHI', 'NGUYENVONG', 'HOSO', 'THISINH', 'DANTOC'];
        
        foreach ($tables as $table) {
            $this->conn->query("DELETE FROM {$table}");
        }
        
        echo "✓ Đã xóa tất cả dữ liệu cũ<br>";
    }
}

// Kiểm tra nếu được gọi trực tiếp
if (basename($_SERVER['PHP_SELF']) == 'import_data.php') {
    // Nếu có request clear_data
    if (isset($_GET['clear_data']) && $_GET['clear_data'] == '1') {
        $importer = new DataImporter($conn);
        echo "<h2 style='color: red;'>⚠️ Xóa dữ liệu</h2>";
        $importer->clearAllData();
    }
    
    // Chạy import
    $importer = new DataImporter($conn);
    $importer->importAll();
    
    $conn->close();
}
?>
