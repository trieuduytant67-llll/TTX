<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SoDoTruongController extends Controller
{
    public function index()
    {
        // Đường dẫn folder chứa PDF
        $dir = public_path('pdf/');
        // Lấy danh sách file PDF
        $pdfFiles = glob($dir . '*.pdf');
        sort($pdfFiles);
        if (empty($pdfFiles)) {
            abort(404, "Không tìm thấy file PDF nào trong thư mục 'pdf'.");
        }
        // Chuyển danh sách file thành mảng tên file (không đường dẫn)
        $pdfFileNames = array_map('basename', $pdfFiles);

        return view('sodotruong', compact('pdfFileNames'));
    }
}