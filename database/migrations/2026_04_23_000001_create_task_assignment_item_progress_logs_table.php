<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tạo bảng lịch sử cập nhật tiến độ công việc.
 * Mỗi lần user gọi PATCH /progress, hệ thống ghi 1 dòng vào bảng này.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_progress_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_item_id');
            $table->unsignedBigInteger('user_id'); // Người cập nhật
            // Giá trị trước và sau khi cập nhật để theo dõi thay đổi
            $table->string('old_processing_status', 30)->nullable();
            $table->string('new_processing_status', 30)->nullable();
            $table->unsignedTinyInteger('old_completion_percent')->nullable();
            $table->unsignedTinyInteger('new_completion_percent')->nullable();
            $table->string('note', 1000)->nullable(); // Ghi chú tiến độ của user

            $table->timestamps();

            $table->foreign('task_assignment_item_id')
                ->references('id')->on('task_assignment_items')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            // Index cho truy vấn lịch sử theo công việc, sắp xếp mới nhất trước
            $table->index(['task_assignment_item_id', 'created_at'], 'ta_progress_logs_item_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_progress_logs');
    }
};
