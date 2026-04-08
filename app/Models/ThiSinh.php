<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thisinh extends Model
{
    protected $table = 'thisinh';
    protected $primaryKey = 'sbd';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sbd', 'ho_ten', 'ten_dang_nhap', 'ngay_sinh',
        'gioi_tinh', 'dantoc_id', 'email', 'dien_thoai', 'dia_chi',
    ];

    protected $casts = [
        'ngay_sinh' => 'date',
    ];

    public function hoso()
    {
        return $this->hasOne(Hoso::class, 'sbd', 'sbd');
    }

    public function kythi()
    {
        return $this->hasMany(Kythi::class, 'sbd', 'sbd');
    }

    public function phongthi_chuyen()
    {
        return $this->hasMany(PhongthiChuyen::class, 'sbd', 'sbd');
    }

    public function dantoc()
    {
        return $this->belongsTo(Dantoc::class, 'dantoc_id');
    }
}