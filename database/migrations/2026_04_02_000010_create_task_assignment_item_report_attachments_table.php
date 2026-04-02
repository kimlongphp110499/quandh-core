<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_item_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_item_report_id');
            $table->unsignedBigInteger('media_id');
            $table->string('file_name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('task_assignment_item_report_id', 'ta_rep_att_rep_fk')
                ->references('id')->on('task_assignment_item_reports')->cascadeOnDelete();
            $table->foreign('media_id', 'ta_rep_att_media_fk')
                ->references('id')->on('media')->cascadeOnDelete();

            $table->unique(['task_assignment_item_report_id', 'media_id'], 'ta_rep_att_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_item_report_attachments');
    }
};
