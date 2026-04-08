<?php

// config/tuyen_sinh.php
// Cấu hình thông tin tuyển sinh — cập nhật lần cuối: 04/04/2026 08:37

return [
    'year' => '2025',
    'site_name' => 'Tuyển sinh THPT chuyên Khoa học Tự Nhiên',
    'start_time' => '8h00 ngày 21/04/2025',
    'end_time' => '17h00 ngày 05/05/2025',
    'announcement_date' => '20/5/2025',
    'registration_open' => false,
    'registration_url' => 'http://tschuyen.hus.vnu.edu.vn/dk/',
    'plan_url' => 'https://hus.vnu.edu.vn/thong-bao/dao-tao-tuyen-sinh/ke-hoach-tuyen-sinh-lop-10-truong-thpt-chuyen-khoa-hoc-tu-nhien-nam-2025-140883.html',
    'guide_url' => 'https://hus.vnu.edu.vn/thong-bao/dao-tao-tuyen-sinh/huong-dan-dang-ky-ho-so-du-thi-lop-10-thpt-chuyen-khtn-nam-hoc-2025-140924.html',
    'sheet_trang_thai_ho_so' => 'https://docs.google.com/spreadsheets/d/197FO3XQ7X7eEwBtvNJFCF-z3QOlylEvd/export?format=csv',
    'sheet_thong_tin_du_thi' => 'https://docs.google.com/spreadsheets/d/1ylaOpCRUIxqopm8iEyIEQJDetgW10LPy/export?format=csv',
    'csv_ket_qua_thi' => 'result/kq_thi.csv',
    'csv_ket_qua_phuc_khao' => 'result/kq_phuckhao.csv',
    'admin_user' => env('ADMIN_USER', 'admin'),
    'admin_pass' => env('ADMIN_PASS', ''),
];
