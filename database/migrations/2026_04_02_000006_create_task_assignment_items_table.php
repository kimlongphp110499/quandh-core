<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_document_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('task_assignment_item_type_id')->nullable();
            $table->string('deadline_type')->default('has_deadline'); // has_deadline, no_deadline
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('processing_status')->default('todo'); // todo, in_progress, done, overdue, paused, cancelled
            $table->unsignedTinyInteger('completion_percent')->default(0);
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('task_assignment_document_id', 'ta_item_doc_fk')
                ->references('id')->on('task_assignment_documents')->cascadeOnDelete();
            $table->foreign('task_assignment_item_type_id', 'ta_item_type_fk')
                ->references('id')->on('task_assignment_item_types')->nullOnDelete();

            $table->index('task_assignment_document_id', 'ta_item_doc_idx');
            $table->index('processing_status', 'ta_item_status_idx');
            $table->index(['deadline_type', 'end_at'], 'ta_item_deadline_idx');
            $table->index('task_assignment_item_type_id', 'ta_item_type_idx');
            $table->index('priority', 'ta_item_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_items');
    }
};
