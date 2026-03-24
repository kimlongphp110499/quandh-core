<?php

use App\Modules\Meeting\SpeechRequestController;
use Illuminate\Support\Facades\Route;

// Danh sách đăng ký phát biểu (quản lý xem tất cả)
Route::get('/{meeting}/speech-requests', [SpeechRequestController::class, 'index'])->middleware('permission:meetings.speechRequests.index,web');

// Đại biểu đăng ký phát biểu
Route::post('/{meeting}/speech-requests', [SpeechRequestController::class, 'store'])->middleware('permission:meetings.speechRequests.store,web');

// Quản lý duyệt / từ chối đăng ký
Route::patch('/{meeting}/speech-requests/{speechRequest}/status', [SpeechRequestController::class, 'updateStatus'])->middleware('permission:meetings.speechRequests.updateStatus,web');
