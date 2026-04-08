<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nguyenvong extends Model
{
    protected $table = 'nguyenvong';

    protected $fillable = ['shs', 'thu_tu', 'mon_thi', 'ghi_chu'];

    public function hoso()
    {
        return $this->belongsTo(Hoso::class, 'shs', 'shs');
    }
}