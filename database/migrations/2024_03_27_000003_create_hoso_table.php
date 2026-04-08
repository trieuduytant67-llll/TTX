<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoso', function (Blueprint $table) {
            $table->string('SHS', 20)->primary();
            $table->string('SBD', 20);
            $table->string('TrangThai', 50);
            $table->string('TruongTHCS', 255)->nullable();
            $table->string('SHSMoi', 20)->nullable();
            $table->text('GhiChu')->nullable();

            $table->foreign('SBD')
                  ->references('SBD')
                  ->on('thisinh')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoso');
    }
};