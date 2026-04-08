<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FindUserNameController extends Controller
{
    public function index()
    {
        return view('find_user_name');
    }

    public function search(Request $request)
    {
        // Logic tìm kiếm tương tự file gốc
        // Sử dụng Http facade để fetch Google Sheet
        $url = 'https://docs.google.com/spreadsheets/d/1Qg4_sWYftdGVfL9I2ew6CAlpyO6gn6oh6ndACg1LRxU/export?format=csv';
        $response = Http::get($url);
        $csvData = $response->body();
        $sheetData = $this->parseCsv($csvData);

        // Logic xử lý tương tự
        // ... (rút gọn)

        return view('find_user_name', [
            'searchAttempted' => true,
            // other data
        ]);
    }

    private function parseCsv($csv)
    {
        $data = [];
        $lines = explode("\n", $csv);
        foreach ($lines as $line) {
            $data[] = str_getcsv($line);
        }
        return $data;
    }
}