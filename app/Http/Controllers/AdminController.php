<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Đường dẫn file config
    private string $configPath;

    public function __construct()
    {
        $this->configPath = config_path('tuyen_sinh.php');
    }

    // Hiển thị form admin với giá trị hiện tại
    public function index()
    {
        $cfg = config('tuyen_sinh');
        return view('admin.index', compact('cfg'));
    }

    // Lưu thay đổi vào config/tuyen_sinh.php
    public function save(Request $request)
    {
        $request->validate([
            'year'              => 'required|string|max:10',
            'site_name'         => 'required|string|max:255',
            'start_time'        => 'required|string|max:100',
            'end_time'          => 'required|string|max:100',
            'announcement_date' => 'nullable|string|max:50',
            'registration_open' => 'nullable',
            'registration_url'  => 'nullable|string|max:500',
            'plan_url'          => 'nullable|string|max:500',
            'guide_url'         => 'nullable|string|max:500',
            'sheet_trang_thai_ho_so' => 'nullable|string|max:500',
            'sheet_thong_tin_du_thi' => 'nullable|string|max:500',
            'csv_ket_qua_thi'        => 'nullable|string|max:200',
            'csv_ket_qua_phuc_khao'  => 'nullable|string|max:200',
        ]);

        $data = [
            'year'              => $request->input('year'),
            'site_name'         => $request->input('site_name'),
            'start_time'        => $request->input('start_time'),
            'end_time'          => $request->input('end_time'),
            'announcement_date' => $request->input('announcement_date', ''),
            'registration_open' => $request->has('registration_open'),
            'registration_url'  => $request->input('registration_url', ''),
            'plan_url'          => $request->input('plan_url', ''),
            'guide_url'         => $request->input('guide_url', ''),
            'sheet_trang_thai_ho_so' => $request->input('sheet_trang_thai_ho_so', ''),
            'sheet_thong_tin_du_thi' => $request->input('sheet_thong_tin_du_thi', ''),
            'csv_ket_qua_thi'        => $request->input('csv_ket_qua_thi', 'result/kq_thi.csv'),
            'csv_ket_qua_phuc_khao'  => $request->input('csv_ket_qua_phuc_khao', 'result/kq_phuckhao.csv'),
            // admin_user / admin_pass giữ nguyên từ .env, không cho sửa qua form
            'admin_user' => env('ADMIN_USER', 'admin'),
            'admin_pass' => env('ADMIN_PASS', ''),
        ];

        // Ghi đè file config/tuyen_sinh.php
        $this->writeConfig($data);

        // Xóa config cache để Laravel đọc lại ngay
        \Artisan::call('config:clear');

        return redirect()->route('admin')->with('success', 'Đã lưu cấu hình thành công!');
    }

    // Ghi mảng $data thành file PHP hợp lệ
    private function writeConfig(array $data): void
    {
        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = '// config/tuyen_sinh.php';
        $lines[] = '// Cấu hình thông tin tuyển sinh — cập nhật lần cuối: ' . now()->format('d/m/Y H:i');
        $lines[] = '';
        $lines[] = 'return [';

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $val = $value ? 'true' : 'false';
                $lines[] = "    '{$key}' => {$val},";
            } elseif (in_array($key, ['admin_user', 'admin_pass'])) {
                // Giữ env() cho 2 key này
                $envKey = strtoupper($key);
                $default = $key === 'admin_user' ? 'admin' : '';
                $lines[] = "    '{$key}' => env('{$envKey}', '{$default}'),";
            } else {
                $escaped = str_replace("'", "\\'", $value);
                $lines[] = "    '{$key}' => '{$escaped}',";
            }
        }

        $lines[] = '];';
        $lines[] = '';

        file_put_contents($this->configPath, implode("\n", $lines));
    }
}