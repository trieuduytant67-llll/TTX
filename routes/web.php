<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SoDoTruongController;
use App\Http\Controllers\KetQuaPhucKhaoController;
use App\Http\Controllers\FindUserNameController;
use App\Http\Controllers\DonSuaThongTinController;
use App\Http\Controllers\LoadingController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');

// CAPTCHA image (dùng chung, phân biệt session qua ?key=)
Route::get('/captcha', [HomeController::class, 'captchaImage'])->name('captcha');

// Quy định dự thi
Route::get('/phieudangkyduthi', [HomeController::class, 'phieuDangKyDuThi'])->name('phieudangkyduthi');
Route::get('/quy-dinh-du-thi', [HomeController::class, 'examRules'])->name('exam-rules');
Route::get('/quydinhduthi', [HomeController::class, 'quyDinhDuThi'])->name('quydinhduthi');

// Tra cứu thông tin dự thi
Route::get('/thong-tin-thi', [HomeController::class, 'examInfo'])->name('exam-info');
Route::post('/thong-tin-thi', [HomeController::class, 'examInfoSearch'])->name('exam-info.search');

// Sơ đồ trường
Route::get('/so-do-truong', [SoDoTruongController::class, 'index'])->name('so-do-truong');

// Kết quả phúc khảo
Route::get('/ket-qua-phuc-khao', [KetQuaPhucKhaoController::class, 'index'])->name('ketquaphuckhao');
Route::post('/ket-qua-phuc-khao', [KetQuaPhucKhaoController::class, 'search'])->name('ketquaphuckhao.search');

// Tìm tên đăng nhập
Route::get('/tim-ten-dang-nhap', [FindUserNameController::class, 'index'])->name('find_user_name');
Route::post('/tim-ten-dang-nhap', [FindUserNameController::class, 'search'])->name('find_user_name.search');

// Đề nghị sửa thông tin
Route::get('/de-nghi-sua-thong-tin', [DonSuaThongTinController::class, 'index'])->name('donsuathongtin');

// Loading trung gian — chỉ cho phép các target hợp lệ (tránh open redirect)
Route::get('/loading', function () {
    $allowed = [
        'trang-thai-ho-so',
        'thong-tin-thi',
        'ket-qua-thi',
    ];
    $target = request('target', 'trang-thai-ho-so');
    if (!in_array($target, $allowed)) {
        $target = 'trang-thai-ho-so';
    }
    return view('loading', ['target' => url($target)]);
})->name('loading');

// Nộp lại ảnh
Route::get('/nop-lai-anh', [HomeController::class, 'nopLaiAnh'])->name('nop-lai-anh');

// Tra cứu trạng thái hồ sơ
Route::get('/trang-thai-ho-so', [HomeController::class, 'studentStatus'])->name('student-status');
Route::post('/trang-thai-ho-so', [HomeController::class, 'studentStatusSearch'])->name('student-status.search');

// Tra cứu kết quả thi
Route::get('/ket-qua-thi', [HomeController::class, 'studentScore'])->name('student-score');
Route::post('/ket-qua-thi', [HomeController::class, 'studentScoreSearch'])->name('student-score.search');

// Admin — bảo vệ bằng HTTP Basic Auth (không cần database)
// Truy cập: http://127.0.0.1:8000/admin
// Tài khoản lấy từ .env: ADMIN_USER / ADMIN_PASS
Route::middleware('basic.admin')->group(function () {
    Route::get('/admin',  [AdminController::class, 'index'])->name('admin');
    Route::post('/admin', [AdminController::class, 'save'])->name('admin.save');
});