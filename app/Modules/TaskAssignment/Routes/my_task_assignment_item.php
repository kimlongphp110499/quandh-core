<?php

use App\Modules\TaskAssignment\MyTaskAssignmentItemController;
use Illuminate\Support\Facades\Route;

// Thống kê công việc của tôi
Route::get('/stats', [MyTaskAssignmentItemController::class, 'stats']);
// Danh sách công việc của tôi (phân trang, lọc)
Route::get('/', [MyTaskAssignmentItemController::class, 'index']);
// Chi tiết công việc của tôi
Route::get('/{taskAssignmentItem}', [MyTaskAssignmentItemController::class, 'show']);
// Cập nhật tiến độ công việc của tôi
Route::patch('/{taskAssignmentItem}/progress', [MyTaskAssignmentItemController::class, 'updateProgress']);
