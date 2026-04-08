<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thisinh', function (Blueprint $table) {
            $table->string('SBD', 20)->primary();
            $table->string('HoTen', 100);
            $table->date('NgaySinh');
            $table->string('GioiTinh', 10);
            $table->string('NoiSinh', 255)->nullable();
            $table->string('HoKhau', 255)->nullable();
            $table->string('MaDanToc', 10)->nullable();
            $table->string('CCCD', 20)->nullable();
            $table->string('DienThoai', 20)->nullable();
            $table->string('Email', 100)->nullable();

            $table->foreign('MaDanToc')
                  ->references('MaDanToc')
                  ->on('dantoc')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thisinh');
    }
};