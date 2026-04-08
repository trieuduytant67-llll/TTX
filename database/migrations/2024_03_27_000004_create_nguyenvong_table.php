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
        Schema::create('nguyenvong', function (Blueprint $table) {
            $table->id('MaNV');                                                          // PK
            $table->string('SHS', 20)->comment('Số hồ sơ (FK)');                        // FK
            $table->string('TenNguyenVong', 100)->comment('Tên nguyện vọng / môn thi');
            $table->integer('ThuTuNV')->comment('Thứ tự nguyện vọng');

            $table  ->foreign('SHS')
                    ->references('SHS')
                    ->on('hoso')->onDelete('cascade');
            $table->unique(['SHS', 'ThuTuNV']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguyenvong');
    }
};