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
        Schema::create('titles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('group');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // ผู้สร้าง
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // ผู้แก้ไขล่าสุด
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null'); // ผู้ลบ
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titles');
    }
};
