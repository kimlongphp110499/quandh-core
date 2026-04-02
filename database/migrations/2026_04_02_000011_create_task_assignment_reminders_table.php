<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_item_id');
            $table->timestamp('remind_at');
            $table->timestamp('sent_at')->nullable();
            $table->string('channel')->default('system'); // system, email, zalo, sms
            $table->unsignedBigInteger('recipient_department_id')->nullable();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('task_assignment_item_id', 'ta_reminder_item_fk')
                ->references('id')->on('task_assignment_items')->cascadeOnDelete();
            $table->foreign('recipient_department_id', 'ta_reminder_dept_fk')
                ->references('id')->on('task_assignment_departments')->nullOnDelete();

            $table->index(['task_assignment_item_id', 'remind_at', 'channel', 'status'], 'ta_reminder_main_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_reminders');
    }
};
