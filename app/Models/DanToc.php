<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dantoc extends Model
{
    protected $table = 'dantoc';
    public $timestamps = false;

    protected $fillable = ['name', 'description'];

    public function thisinh()
    {
        return $this->hasMany(Thisinh::class, 'dantoc_id');
    }
}