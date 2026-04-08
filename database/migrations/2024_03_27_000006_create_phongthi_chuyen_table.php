<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phongthi_chuyen', function (Blueprint $table) {
            // MaPhongThi là Primary Key (string)
            $table->string('MaPhongThi', 50)->primary()->comment('Mã phòng thi');

            // SBD là Foreign Key
            $table->string('SBD', 20)->comment('Số báo danh');

            // Các cột theo yêu cầu
            $table->string('MonChuyen', 100)->comment('Môn chuyên');
            $table->string('SoPhong', 50)->comment('Số phòng');


            // Foreign Key
            $table->foreign('SBD')
                  ->references('SBD')
                  ->on('thisinh')
                  ->onDelete('cascade');

            // Index để tối ưu truy vấn
            $table->index(['SBD', 'MonChuyen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phongthi_chuyen');
    }
};