<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dantoc', function (Blueprint $table) {
            $table->string('MaDanToc', 10)->primary();   // PK, kiểu string vì mã thường là 'KH', '01'...
            $table->string('TenDanToc', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dantoc');
    }
};