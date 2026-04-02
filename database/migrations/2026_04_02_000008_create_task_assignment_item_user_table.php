<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_item_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_item_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('user_id');
            $table->string('assignment_role')->default('main'); // main, support
            $table->string('assignment_status')->default('assigned'); // assigned, accepted, rejected, done
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('task_assignment_item_id', 'ta_item_user_item_fk')
                ->references('id')->on('task_assignment_items')->cascadeOnDelete();
            $table->foreign('department_id', 'ta_item_user_dept_fk')
                ->references('id')->on('task_assignment_departments')->cascadeOnDelete();
            $table->foreign('user_id', 'ta_item_user_user_fk')
                ->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['task_assignment_item_id', 'user_id'], 'ta_item_user_unique');
            $table->index(['department_id', 'assignment_status'], 'ta_item_user_dept_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_item_user');
    }
};
