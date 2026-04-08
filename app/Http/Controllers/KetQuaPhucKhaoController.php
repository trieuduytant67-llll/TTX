<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KetQuaPhucKhaoController extends Controller
{
    public function index()
    {
        return view('ketquaphuckhao');
    }

    public function search(Request $request)
    {
        $captchaError = null;
        $foundStudentData = null;

        $enteredCaptcha = trim($request->input('captcha_code', ''));
        $sessionCaptcha = $request->session()->get('captcha_phuckhao');

        if (empty($enteredCaptcha)) {
            $captchaError = 'Vui lòng nhập mã xác thực CAPTCHA';
        } elseif (!$sessionCaptcha || strtolower($sessionCaptcha) !== strtolower($enteredCaptcha)) {
            $captchaError = 'Mã CAPTCHA không đúng, vui lòng thử lại';
        }

        if (!$captchaError) {
            $request->session()->forget('captcha_phuckhao');
            $tenDangNhap = trim($request->input('ten_dang_nhap', ''));
            $request->session()->put('search_phuckhao.ten_dang_nhap', $tenDangNhap);

            // Đọc dữ liệu từ file CSV
            $dataPath = public_path('result/kq_phuckhao.csv');
            $sheetData = $this->readLocalCsv($dataPath);

            $COT = [
                'STT' => 0,
                'SHS' => 1,
                'SBD' => 2,
                'HO_TEN' => 3,
                'GIOI_TINH' => 4,
                'NGAY_SINH' => 5,
                'NV1' => 6,
                'NV2' => 7,
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
                array_shift($sheetData); // Bỏ header
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

                // Đảm bảo đủ cột
                for ($i = 0; $i <= 18; $i++) {
                    if (!isset($rowData[$i])) {
                        $rowData[$i] = '';
                    }
                }
            }
            unset($rowData);

            // Tìm kiếm
            foreach ($sheetData as $rowData) {
                $ho_ten = $rowData[$COT['HO_TEN']] ?? '';
                $so_ho_so = $rowData[$COT['SHS']] ?? '';
                $ten_dang_nhap_he_thong = $this->taoTenDangNhap($ho_ten, $so_ho_so);

                if (strtolower($ten_dang_nhap_he_thong) === strtolower($tenDangNhap)) {
                    $foundStudentData = $rowData;
                    break;
                }
            }
        }

        $newCaptcha = $this->generateCaptchaText();
        $request->session()->put('captcha_phuckhao', $newCaptcha);

        return view('ketquaphuckhao', [
            'searchAttempted' => true,
            'captchaError' => $captchaError,
            'foundStudentData' => $foundStudentData,
            'captchaText' => $newCaptcha,
            'tenDangNhap' => $request->session()->get('search_phuckhao.ten_dang_nhap', ''),
            'COT' => [
                'STT' => 0,
                'SHS' => 1,
                'SBD' => 2,
                'HO_TEN' => 3,
                'GIOI_TINH' => 4,
                'NGAY_SINH' => 5,
                'NV1' => 6,
                'NV2' => 7,
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
            ],
        ]);
    }

    private function readLocalCsv($filePath)
    {
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    private function taoTenDangNhap($ho_ten, $so_ho_so)
    {
        // Tách họ tên, lấy tên cuối
        $parts = explode(' ', trim($ho_ten));
        $ten_cuoi = end($parts);
        // Viết hoa chữ cái đầu
        $ten_cuoi = ucfirst(strtolower($this->removeVietnameseAccents($ten_cuoi)));
        return $ten_cuoi . $so_ho_so;
    }

    private function removeVietnameseAccents($str)
    {
        $accents = [
            'à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ằ', 'ắ', 'ẳ', 'ẵ', 'ặ', 'â', 'ầ', 'ấ', 'ẩ', 'ẫ', 'ậ',
            'è', 'é', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ề', 'ế', 'ể', 'ễ', 'ệ',
            'ì', 'í', 'ỉ', 'ĩ', 'ị',
            'ò', 'ó', 'ỏ', 'õ', 'ọ', 'ô', 'ồ', 'ố', 'ổ', 'ỗ', 'ộ', 'ơ', 'ờ', 'ớ', 'ở', 'ỡ', 'ợ',
            'ù', 'ú', 'ủ', 'ũ', 'ụ', 'ư', 'ừ', 'ứ', 'ử', 'ữ', 'ự',
            'ỳ', 'ý', 'ỷ', 'ỹ', 'ỵ',
            'đ',
        ];
        $no_accents = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd',
        ];
        return str_replace($accents, $no_accents, $str);
    }

    private function generateCaptchaText()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $captcha = '';
        for ($i = 0; $i < 6; $i++) {
            $captcha .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $captcha;
    }
}