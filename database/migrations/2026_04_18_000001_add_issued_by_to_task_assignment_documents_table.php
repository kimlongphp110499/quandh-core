<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_assignment_documents', function (Blueprint $table) {
            // Ghi nhận user thực hiện hành động ban hành văn bản
            $table->foreignId('issued_by')->nullable()->after('issued_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('task_assignment_documents', function (Blueprint $table) {
            $table->dropForeign(['issued_by']);
            $table->dropColumn('issued_by');
        });
    }
};
