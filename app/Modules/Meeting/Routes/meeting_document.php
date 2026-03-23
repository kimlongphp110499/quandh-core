<?php

use App\Modules\Meeting\MeetingDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/{meeting}/documents', [MeetingDocumentController::class, 'index'])->middleware('permission:meetings.documents.index,web');
Route::post('/{meeting}/documents', [MeetingDocumentController::class, 'store'])->middleware('permission:meetings.documents.store,web');
Route::delete('/{meeting}/documents/{document}', [MeetingDocumentController::class, 'destroy'])->middleware('permission:meetings.documents.destroy,web');
