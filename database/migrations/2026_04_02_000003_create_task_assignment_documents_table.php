<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('summary')->nullable();
            $table->date('issue_date')->nullable();
            $table->foreignId('task_assignment_type_id')->nullable()->constrained('task_assignment_types')->nullOnDelete();
            $table->string('status')->default('draft'); // draft, issued
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('issue_date');
            $table->index('task_assignment_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_documents');
    }
};
