<?php

use App\Modules\TaskAssignment\TaskAssignmentDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/export', [TaskAssignmentDocumentController::class, 'export'])->middleware('permission:task-assignment-documents.export,web');
Route::post('/import', [TaskAssignmentDocumentController::class, 'import'])->middleware('permission:task-assignment-documents.import,web');
Route::post('/bulk-delete', [TaskAssignmentDocumentController::class, 'bulkDestroy'])->middleware('permission:task-assignment-documents.bulkDestroy,web');
Route::patch('/bulk-status', [TaskAssignmentDocumentController::class, 'bulkUpdateStatus'])->middleware('permission:task-assignment-documents.bulkUpdateStatus,web');
Route::get('/stats', [TaskAssignmentDocumentController::class, 'stats'])->middleware('permission:task-assignment-documents.stats,web');
Route::get('/', [TaskAssignmentDocumentController::class, 'index'])->middleware('permission:task-assignment-documents.index,web');
Route::post('/', [TaskAssignmentDocumentController::class, 'store'])->middleware('permission:task-assignment-documents.store,web');
Route::get('/{taskAssignmentDocument}', [TaskAssignmentDocumentController::class, 'show'])->middleware('permission:task-assignment-documents.show,web');
Route::put('/{taskAssignmentDocument}', [TaskAssignmentDocumentController::class, 'update'])->middleware('permission:task-assignment-documents.update,web');
Route::patch('/{taskAssignmentDocument}', [TaskAssignmentDocumentController::class, 'update'])->middleware('permission:task-assignment-documents.update,web');
Route::delete('/{taskAssignmentDocument}', [TaskAssignmentDocumentController::class, 'destroy'])->middleware('permission:task-assignment-documents.destroy,web');
Route::patch('/{taskAssignmentDocument}/status', [TaskAssignmentDocumentController::class, 'changeStatus'])->middleware('permission:task-assignment-documents.changeStatus,web');
// Attachment endpoints
Route::post('/{taskAssignmentDocument}/attachments', [TaskAssignmentDocumentController::class, 'addAttachments'])->middleware('permission:task-assignment-documents.update,web');
Route::delete('/{taskAssignmentDocument}/attachments/{attachment}', [TaskAssignmentDocumentController::class, 'removeAttachment'])->middleware('permission:task-assignment-documents.update,web');
Route::patch('/{taskAssignmentDocument}/attachments/sort', [TaskAssignmentDocumentController::class, 'sortAttachments'])->middleware('permission:task-assignment-documents.update,web');
