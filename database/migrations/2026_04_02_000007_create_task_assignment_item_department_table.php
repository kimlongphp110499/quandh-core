<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_item_department', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_item_id');
            $table->unsignedBigInteger('department_id');
            $table->string('role')->default('main'); // main, cooperate
            $table->timestamps();

            $table->foreign('task_assignment_item_id', 'ta_item_dept_item_fk')
                ->references('id')->on('task_assignment_items')->cascadeOnDelete();
            $table->foreign('department_id', 'ta_item_dept_dept_fk')
                ->references('id')->on('task_assignment_departments')->cascadeOnDelete();

            $table->unique(['task_assignment_item_id', 'department_id'], 'ta_item_dept_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_item_department');
    }
};
