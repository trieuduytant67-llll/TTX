<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hoso extends Model
{
    protected $table = 'hoso';
    protected $primaryKey = 'shs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['shs', 'sbd', 'ngay_nop', 'trang_thai', 'ghi_chu'];

    protected $casts = [
        'ngay_nop' => 'date',
    ];

    public function thisinh()
    {
        return $this->belongsTo(Thisinh::class, 'sbd', 'sbd');
    }

    public function nguyenvong()
    {
        return $this->hasMany(Nguyenvong::class, 'shs', 'shs')->orderBy('thu_tu');
    }
}