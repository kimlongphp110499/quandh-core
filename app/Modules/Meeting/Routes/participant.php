<?php

use App\Modules\Meeting\ParticipantController;
use Illuminate\Support\Facades\Route;

Route::get('/{meeting}/participants', [ParticipantController::class, 'index'])->middleware('permission:meetings.participants.index,web');
Route::post('/{meeting}/participants', [ParticipantController::class, 'store'])->middleware('permission:meetings.participants.store,web');
Route::put('/{meeting}/participants/{participant}', [ParticipantController::class, 'update'])->middleware('permission:meetings.participants.update,web');
Route::patch('/{meeting}/participants/{participant}', [ParticipantController::class, 'update'])->middleware('permission:meetings.participants.update,web');
Route::delete('/{meeting}/participants/{participant}', [ParticipantController::class, 'destroy'])->middleware('permission:meetings.participants.destroy,web');
Route::patch('/{meeting}/participants/{participant}/checkin', [ParticipantController::class, 'checkin'])->middleware('permission:meetings.participants.checkin,web');
