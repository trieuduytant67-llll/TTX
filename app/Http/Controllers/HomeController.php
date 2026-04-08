<?php

namespace App\Http\Controllers;

use App\Models\Thisinh;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Trang chủ
     */
    public function index()
    {
        return view('home');
    }

    // =====================================================================
    // TRANG PHIẾU ĐĂNG KÝ DỰ THI

    public function phieuDangKyDuThi()
    {
        return view('exam.phieudangkyduthi');
    }

    // TRANG QUY ĐỊNH DỰ THI

    public function examRules()
    {
        return view('exam.quydinhduthi');    
    }

    // TRANG TRA CỨU THÔNG TIN DỰ THI
    // =====================================================================

    public function examInfo()
    {
        return view('exam.info');
    }

    public function examInfoSearch(Request $request)
    {
        $captchaError = null;
        $thisinh = null;

        $enteredCaptcha = trim($request->input('captcha_code', ''));
        $sessionCaptcha = $request->session()->get('captcha');

        if (empty($enteredCaptcha)) {
            $captchaError = 'Vui lòng nhập mã xác thực CAPTCHA';
        } elseif (!$sessionCaptcha || strtolower($sessionCaptcha) !== strtolower($enteredCaptcha)) {
            $captchaError = 'Mã CAPTCHA không đúng, vui lòng thử lại';
        }

        if (!$captchaError) {
            $request->session()->forget('captcha');
            $tenDangNhap = trim($request->input('ten_dang_nhap', ''));
            $request->session()->put('search.ten_dang_nhap', $tenDangNhap);

            $thisinh = Thisinh::whereRaw('LOWER(ten_dang_nhap) = ?', [strtolower($tenDangNhap)])
                ->with([
                    'hoso.nguyenvong',
                    'kythi'           => fn($q) => $q->orderBy('ngay_thi')->orderBy('gio_thi'),
                    'phongthi_chuyen' => fn($q) => $q->orderBy('ngay_thi')->orderBy('gio_thi'),
                ])
                ->first();
        }

        $newCaptcha = $this->generateCaptchaText();
        $request->session()->put('captcha', $newCaptcha);

        return view('exam.info', [
            'searchAttempted' => true,
            'captchaError'    => $captchaError,
            'thisinh'         => $thisinh,
            'captchaText'     => $newCaptcha,
            'tenDangNhap'     => $request->session()->get('search.ten_dang_nhap', ''),
        ]);
    }

    // TRANG QUY ĐỊNH DỰ THI

    public function quyDinhDuThi()
    {
        return view('exam.quydinhduthi');
    }

    // TRANG NỘP LẠI ẢNH

    public function nopLaiAnh()
    {
        return view('student.nop-lai-anh');
    }

    // TRANG TRA CỨU TRẠNG THÁI HỒ SƠ
    // =====================================================================

    public function studentStatus()
    {
        return view('student.status');
    }

    public function studentStatusSearch(Request $request)
    {
        $captchaError = null;
        $thisinh      = null;

        $enteredCaptcha = trim($request->input('captcha_code', ''));
        $sessionCaptcha = $request->session()->get('captcha_status');

        if (empty($enteredCaptcha)) {
            $captchaError = 'Vui lòng nhập mã xác thực CAPTCHA';
        } elseif (!$sessionCaptcha || strtolower($sessionCaptcha) !== strtolower($enteredCaptcha)) {
            $captchaError = 'Mã CAPTCHA không đúng, vui lòng thử lại';
        }

        if (!$captchaError) {
            $request->session()->forget('captcha_status');

            $tenDangNhap = trim($request->input('ten_dang_nhap', ''));
            $request->session()->put('search_status.ten_dang_nhap', $tenDangNhap);

            $thisinh = Thisinh::whereRaw('LOWER(ten_dang_nhap) = ?', [strtolower($tenDangNhap)])
                ->with(['hoso.nguyenvong', 'dantoc'])
                ->first();
        }

        $newCaptcha = $this->generateCaptchaText();
        $request->session()->put('captcha_status', $newCaptcha);

        return view('student.status', [
            'searchAttempted' => true,
            'captchaError'    => $captchaError,
            'thisinh'         => $thisinh,
            'tenDangNhap'     => $request->session()->get('search_status.ten_dang_nhap', ''),
        ]);
    }

    // =====================================================================
    // CAPTCHA — dùng query param ?key= để phân biệt session key
    // =====================================================================

    public function captchaImage(Request $request)
    {
        $key = $request->query('key', 'default');
        $sessionKey = 'captcha' . ($key !== 'default' ? '_' . $key : '');

        $text = $this->generateCaptchaText();
        $request->session()->put($sessionKey, $text);

        $width  = 130;
        $height = 45;
        $image  = imagecreatetruecolor($width, $height);

        $bg    = imagecolorallocate($image, 240, 240, 245);
        $fg    = imagecolorallocate($image, 20, 60, 120);
        $noise = imagecolorallocate($image, 180, 190, 200);

        imagefill($image, 0, 0, $bg);

        for ($i = 0; $i < 200; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noise);
        }
        for ($i = 0; $i < 4; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noise);
        }

        $fontPath = public_path('fonts/DejaVuSans-Bold.ttf');
        if (file_exists($fontPath)) {
            imagettftext($image, 22, rand(-8, 8), 10, 33, $fg, $fontPath, $text);
        } else {
            imagestring($image, 5, 15, 12, $text, $fg);
        }

        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        imagepng($image);
        imagedestroy($image);
        exit;
    }



    // =====================================================================
    // TRANG TRA CỨU KẾT QUẢ THI
    // =====================================================================

    public function studentScore()
    {
        return view('student.score');
    }

    public function studentScoreSearch(Request $request)
    {
        $captchaError = null;
        $thisinh      = null;

        $enteredCaptcha = trim($request->input('captcha_code', ''));
        $sessionCaptcha = $request->session()->get('captcha_score');

        if (empty($enteredCaptcha)) {
            $captchaError = 'Vui lòng nhập mã xác thực CAPTCHA';
        } elseif (!$sessionCaptcha || strtolower($sessionCaptcha) !== strtolower($enteredCaptcha)) {
            $captchaError = 'Mã CAPTCHA không đúng, vui lòng thử lại';
        }

        if (!$captchaError) {
            $request->session()->forget('captcha_score');

            $tenDangNhap = trim($request->input('ten_dang_nhap', ''));
            $request->session()->put('search_score.ten_dang_nhap', $tenDangNhap);

            $thisinh = Thisinh::whereRaw('LOWER(ten_dang_nhap) = ?', [strtolower($tenDangNhap)])
                ->with([
                    'hoso.nguyenvong',
                    'kythi'           => fn($q) => $q->orderBy('ngay_thi')->orderBy('gio_thi'),
                    'phongthi_chuyen' => fn($q) => $q->orderBy('ngay_thi')->orderBy('gio_thi'),
                ])
                ->first();
        }

        $newCaptcha = $this->generateCaptchaText();
        $request->session()->put('captcha_score', $newCaptcha);

        return view('student.score', [
            'searchAttempted' => true,
            'captchaError'    => $captchaError,
            'thisinh'         => $thisinh,
            'tenDangNhap'     => $request->session()->get('search_score.ten_dang_nhap', ''),
        ]);
    }

    private function generateCaptchaText(int $length = 5): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $text  = '';
        for ($i = 0; $i < $length; $i++) {
            $text .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $text;
    }
}