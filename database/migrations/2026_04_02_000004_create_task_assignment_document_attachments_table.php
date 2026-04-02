<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_document_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_document_id');
            $table->unsignedBigInteger('media_id');
            $table->string('file_name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('task_assignment_document_id', 'ta_doc_att_doc_fk')
                ->references('id')->on('task_assignment_documents')->cascadeOnDelete();
            $table->foreign('media_id', 'ta_doc_att_media_fk')
                ->references('id')->on('media')->cascadeOnDelete();

            $table->unique(['task_assignment_document_id', 'media_id'], 'ta_doc_att_unique');
            $table->index(['task_assignment_document_id', 'sort_order'], 'ta_doc_att_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_document_attachments');
    }
};
