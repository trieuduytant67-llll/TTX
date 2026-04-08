<?php
/**
 * File tạo cấu trúc bảng trong cơ sở dữ liệu
 */

require_once 'db_config.php';

function createTables($conn) {
    $errors = [];
    
    // 1. Tạo bảng DANTOC
    $sql_dantoc = "
    CREATE TABLE IF NOT EXISTS DANTOC (
        MaDanToc INT AUTO_INCREMENT PRIMARY KEY,
        TenDanToc VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (!$conn->query($sql_dantoc)) {
        $errors[] = "Lỗi tạo bảng DANTOC: " . $conn->error;
    }
    
    // 2. Tạo bảng THISINH
    $sql_thisinh = "
    CREATE TABLE IF NOT EXISTS THISINH (
        SBD VARCHAR(20) PRIMARY KEY,
        HoTen VARCHAR(255) NOT NULL,
        NgaySinh DATE,
        GioiTinh VARCHAR(10),
        NoiSinh VARCHAR(255),
        HoKhau VARCHAR(255),
        MaDanToc INT,
        CCCD VARCHAR(20),
        DienThoai VARCHAR(15),
        Email VARCHAR(100),
        FOREIGN KEY (MaDanToc) REFERENCES DANTOC(MaDanToc)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (!$conn->query($sql_thisinh)) {
        $errors[] = "Lỗi tạo bảng THISINH: " . $conn->error;
    }
    
    // 3. Tạo bảng HOSO
    $sql_hoso = "
    CREATE TABLE IF NOT EXISTS HOSO (
        SHS VARCHAR(20) PRIMARY KEY,
        SBD VARCHAR(20) NOT NULL,
        TrangThai VARCHAR(50),
        TruongTHCS VARCHAR(255),
        SHSMoi VARCHAR(20),
        GhiChu TEXT,
        FOREIGN KEY (SBD) REFERENCES THISINH(SBD)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (!$conn->query($sql_hoso)) {
        $errors[] = "Lỗi tạo bảng HOSO: " . $conn->error;
    }
    
    // 4. Tạo bảng NGUYENVONG
    $sql_nguyenvong = "
    CREATE TABLE IF NOT EXISTS NGUYENVONG (
        MaNV INT AUTO_INCREMENT PRIMARY KEY,
        SHS VARCHAR(20) NOT NULL,
        TenNguyenVong VARCHAR(255),
        ThuTuNV INT,
        FOREIGN KEY (SHS) REFERENCES HOSO(SHS)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (!$conn->query($sql_nguyenvong)) {
        $errors[] = "Lỗi tạo bảng NGUYENVONG: " . $conn->error;
    }
    
    // 5. Tạo bảng KYTHI
    $sql_kythi = "
    CREATE TABLE IF NOT EXISTS KYTHI (
        SBD VARCHAR(20) PRIMARY KEY,
        SHS VARCHAR(20),
        PhongThiChung VARCHAR(50),
        FOREIGN KEY (SBD) REFERENCES THISINH(SBD),
        FOREIGN KEY (SHS) REFERENCES HOSO(SHS)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (!$conn->query($sql_kythi)) {
        $errors[] = "Lỗi tạo bảng KYTHI: " . $conn->error;
    }
    
    // 6. Tạo bảng PHONGTHI_CHUYEN
    $sql_phongthi = "
    CREATE TABLE IF NOT EXISTS PHONGTHI_CHUYEN (
        MaPhongThi INT AUTO_INCREMENT PRIMARY KEY,
        SBD VARCHAR(20),
        MonChuyen VARCHAR(100),
        SoPhong VARCHAR(50),
        FOREIGN KEY (SBD) REFERENCES THISINH(SBD)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (!$conn->query($sql_phongthi)) {
        $errors[] = "Lỗi tạo bảng PHONGTHI_CHUYEN: " . $conn->error;
    }
    
    return $errors;
}

// Gọi hàm tạo bảng
$errors = createTables($conn);

if (empty($errors)) {
    echo "✓ Tất cả bảng đã được tạo thành công!<br>";
} else {
    foreach ($errors as $error) {
        echo "✗ " . $error . "<br>";
    }
}

$conn->close();
?>
