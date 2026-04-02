<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_item_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_item_id');
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->string('report_document_number')->nullable();
            $table->string('report_document_excerpt')->nullable();
            $table->text('report_document_content')->nullable();
            $table->timestamps();

            $table->foreign('task_assignment_item_id', 'ta_item_report_item_fk')
                ->references('id')->on('task_assignment_items')->cascadeOnDelete();

            $table->index(['task_assignment_item_id', 'reporter_user_id'], 'ta_item_report_item_user_idx');
            $table->index('completed_at', 'ta_item_report_completed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_item_reports');
    }
};
