<?php

use App\Modules\TaskAssignment\TaskAssignmentItemReportController;
use Illuminate\Support\Facades\Route;

Route::post('/bulk-delete', [TaskAssignmentItemReportController::class, 'bulkDestroy'])->middleware('permission:task-assignment-item-reports.bulkDestroy,web');
Route::get('/{taskAssignmentItemReport}', [TaskAssignmentItemReportController::class, 'show'])->middleware('permission:task-assignment-item-reports.show,web');
Route::put('/{taskAssignmentItemReport}', [TaskAssignmentItemReportController::class, 'update'])->middleware('permission:task-assignment-item-reports.update,web');
Route::patch('/{taskAssignmentItemReport}', [TaskAssignmentItemReportController::class, 'update'])->middleware('permission:task-assignment-item-reports.update,web');
Route::delete('/{taskAssignmentItemReport}', [TaskAssignmentItemReportController::class, 'destroy'])->middleware('permission:task-assignment-item-reports.destroy,web');
