# 🚀 Laravel Integration Guide - Thongbao Tuyensinh Chuyen

## 📋 Tình trạng tích hợp

✅ **Hoàn tất copy:**
- `public/common/` - Hàm helper và config PHP
- `public/styles/` - CSS styles
- `public/image/` - Images và assets
- `public/pdf/` - PDF files
- `public/result/` - CSV result files
- `storage/legacy/` - 11 PHP logic files (await migration to Controllers)

## 🎯 Các bước tiếp theo

### Bước 1: Cấu hình Database (.env)
```bash
# Edit .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tuyensinhkhtn
DB_USERNAME=root
DB_PASSWORD=
```

### Bước 2: Tạo tables (migrations)
```bash
php artisan migrate
```

### Bước 3: Import dữ liệu từ Google Sheets
Tạo Command để import dữ liệu từ `public/common/import_data.php`:
```bash
php artisan make:command ImportGoogleSheetData
```

### Bước 4: Chuyển PHP logic thành Controllers
Các file trong `storage/legacy/` cần chuyển thành:

#### Legacy → Laravel Mapping:
| Legacy PHP File | → | Laravel Controller | → | Route |
|---|---|---|---|---|
| `index.php` | | HomeController | | `/` |
| `phieudangkyduthi.php` | | RegistrationController | | `/registration` |
| `student_status.php` | | StudentController@status | | `/student/status` |
| `student_score.php` | | StudentController@score | | `/student/score` |
| `ketquaphuckhao.php` | | AppealController@results | | `/appeal/results` |
| `find_user_name.php` | | StudentController@search | | `/api/student/search` |
| `sodotruong.php` | | ExamRoomController@layout | | `/exam-room/layout` |
| `quydinhduthi.php` | | InfoController@rules | | `/info/rules` |

### Bước 5: Migrate Views
Các HTML output từ PHP files → Blade templates trong `resources/views/`

```
resources/views/
├── layouts/
│   └── app.blade.php
├── home/
│   └── index.blade.php
├── registration/
│   └── form.blade.php
├── student/
│   ├── status.blade.php
│   └── score.blade.php
├── appeal/
│   └── results.blade.php
└── exam/
    └── layout.blade.php
```

## 📁 Cấu trúc thư mục hiện tại

```
project_web/
├── public/
│   ├── common/          ✓ PHP helpers & config
│   ├── styles/          ✓ CSS files
│   ├── image/           ✓ Images
│   ├── pdf/             ✓ PDF files
│   ├── result/          ✓ CSV results
│   └── index.php        (Laravel's entry point)
│
├── storage/
│   └── legacy/          ✓ Old PHP logic files (11 files)
│
├── app/
│   ├── Http/
│   │   ├── Controllers/    → Create feature controllers here
│   │   └── Middleware/
│   ├── Models/
│   ├── Services/           → Google Sheets integration
│   └── Jobs/
│
├── database/
│   ├── migrations/         → Create 6 table migrations
│   └── seeders/
│
├── routes/
│   ├── web.php            → Main routes
│   └── api.php            → API endpoints
│
├── resources/
│   └── views/             → Blade templates
│
└── config/
    └── tuyensinh.php      → App configuration
```

## 🔧 Tiếp theo: Chi tiết từng bước

### Bước A: Database Setup
1. Run migrations để tạo 6 tables: DANTOC, THISINH, HOSO, NGUYENVONG, KYTHI, PHONGTHI_CHUYEN
2. Import dữ liệu từ Google Sheets

### Bước B: Create Controllers
Ví dụ cho `phieudangkyduthi.php` → `RegistrationController`:

```php
// app/Http/Controllers/RegistrationController.php
namespace App\Http\Controllers;

class RegistrationController extends Controller
{
    public function form() { /* ... */ }
    public function store() { /* ... */ }
}
```

### Bước C: Update Routes
```php
// routes/web.php
Route::get('/', [HomeController::class, 'index']);
Route::get('/registration', [RegistrationController::class, 'form']);
Route::post('/registration', [RegistrationController::class, 'store']);
```

## ⚡ Quick Start
```bash
# 1. Set up environment
cp .env.example .env

# 2. Install dependencies
composer install

# 3. Generate app key
php artisan key:generate

# 4. Run migrations
php artisan migrate

# 5. Start server
php artisan serve
```

## 📚 Files đã sẵn
- `public/common/basic_function.php` - removeVietnameseTones(), layTenCuoi(), taoTenDangNhap()
- `public/common/import_data.php` - DataImporter class cho Google Sheets
- `public/common/common.php` - Global config & helpers

## 🔗 Tài nguyên
- Legacy PHP files: `storage/legacy/`
- CSS: `public/styles/common.css`
- Assets: `public/image/` & `public/pdf/`
- CSV Results: `public/result/`

---
**Lưu ý:** Toàn bộ logic PHP cần được refactor thành Laravel patterns (Controllers, Models, Services, Jobs) để tận dụng đầy đủ framework.

Cần hỗ trợ chi tiết cho bước nào? 👉
