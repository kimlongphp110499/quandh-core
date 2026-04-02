<?php

use App\Modules\TaskAssignment\TaskAssignmentItemController;
use App\Modules\TaskAssignment\TaskAssignmentItemReportController;
use Illuminate\Support\Facades\Route;

Route::get('/export', [TaskAssignmentItemController::class, 'export'])->middleware('permission:task-assignment-items.export,web');
Route::post('/import', [TaskAssignmentItemController::class, 'import'])->middleware('permission:task-assignment-items.import,web');
Route::post('/bulk-delete', [TaskAssignmentItemController::class, 'bulkDestroy'])->middleware('permission:task-assignment-items.bulkDestroy,web');
Route::patch('/bulk-status', [TaskAssignmentItemController::class, 'bulkUpdateStatus'])->middleware('permission:task-assignment-items.bulkUpdateStatus,web');
Route::get('/stats', [TaskAssignmentItemController::class, 'stats'])->middleware('permission:task-assignment-items.stats,web');
Route::get('/stats-by-department', [TaskAssignmentItemController::class, 'statsByDepartment'])->middleware('permission:task-assignment-items.stats,web');
Route::get('/stats-by-user', [TaskAssignmentItemController::class, 'statsByUser'])->middleware('permission:task-assignment-items.stats,web');
Route::get('/stats-by-time', [TaskAssignmentItemController::class, 'statsByTime'])->middleware('permission:task-assignment-items.stats,web');
Route::get('/overdue', [TaskAssignmentItemController::class, 'overdue'])->middleware('permission:task-assignment-items.index,web');
Route::get('/upcoming-deadline', [TaskAssignmentItemController::class, 'upcomingDeadline'])->middleware('permission:task-assignment-items.index,web');
Route::get('/', [TaskAssignmentItemController::class, 'index'])->middleware('permission:task-assignment-items.index,web');
Route::post('/', [TaskAssignmentItemController::class, 'store'])->middleware('permission:task-assignment-items.store,web');
Route::get('/{taskAssignmentItem}', [TaskAssignmentItemController::class, 'show'])->middleware('permission:task-assignment-items.show,web');
Route::put('/{taskAssignmentItem}', [TaskAssignmentItemController::class, 'update'])->middleware('permission:task-assignment-items.update,web');
Route::patch('/{taskAssignmentItem}', [TaskAssignmentItemController::class, 'update'])->middleware('permission:task-assignment-items.update,web');
Route::delete('/{taskAssignmentItem}', [TaskAssignmentItemController::class, 'destroy'])->middleware('permission:task-assignment-items.destroy,web');
Route::patch('/{taskAssignmentItem}/status', [TaskAssignmentItemController::class, 'changeStatus'])->middleware('permission:task-assignment-items.changeStatus,web');
// Reports
Route::get('/{taskAssignmentItem}/reports', [TaskAssignmentItemReportController::class, 'index'])->middleware('permission:task-assignment-item-reports.index,web');
Route::post('/{taskAssignmentItem}/reports', [TaskAssignmentItemReportController::class, 'store'])->middleware('permission:task-assignment-item-reports.store,web');
