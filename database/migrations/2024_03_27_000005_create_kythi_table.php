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
        Schema::create('kythi', function (Blueprint $table) {
            // SBD là Primary Key (string)
            $table->string('SBD', 20)->primary()->comment('Số báo danh');

            // SHS là Foreign Key
            $table->string('SHS', 20)->nullable()->comment('Số hồ sơ');
            
            // Cột theo yêu cầu
            $table->string('PhongThiChung', 50)->nullable()->comment('Phòng thi chung');

            // Foreign Key constraints
            $table->foreign('SBD')
                  ->references('SBD')
                  ->on('thisinh')
                  ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kythi');
    }
};