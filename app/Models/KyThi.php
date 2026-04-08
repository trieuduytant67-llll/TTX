<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kythi extends Model
{
    protected $table = 'kythi';

    protected $fillable = [
        'sbd', 'shs', 'mon_thi', 'ngay_thi',
        'gio_thi', 'phong_thi', 'so_tho', 'diem', 'trang_thai',
    ];

    protected $casts = [
        'ngay_thi' => 'date',
    ];

    public function thisinh()
    {
        return $this->belongsTo(Thisinh::class, 'sbd', 'sbd');
    }
}