<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhongthiChuyen extends Model
{
    protected $table = 'phongthi_chuyen';

    protected $fillable = [
        'sbd', 'mon_thi', 'ngay_thi',
        'gio_thi', 'phong_thi', 'so_tho', 'diem', 'trang_thai', 'ghi_chu',
    ];

    protected $casts = [
        'ngay_thi' => 'date',
    ];

    public function thisinh()
    {
        return $this->belongsTo(Thisinh::class, 'sbd', 'sbd');
    }
}