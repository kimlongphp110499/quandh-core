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
        Schema::table('m_meeting_documents', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name'); // Loại tài liệu do người dùng nhập
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_meeting_documents', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
