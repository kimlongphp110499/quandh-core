<?php

use App\Modules\Meeting\ConclusionController;
use Illuminate\Support\Facades\Route;

Route::get('/{meeting}/conclusions', [ConclusionController::class, 'index'])->middleware('permission:meetings.conclusions.index,web');
Route::post('/{meeting}/conclusions', [ConclusionController::class, 'store'])->middleware('permission:meetings.conclusions.store,web');
Route::put('/{meeting}/conclusions/{conclusion}', [ConclusionController::class, 'update'])->middleware('permission:meetings.conclusions.update,web');
Route::patch('/{meeting}/conclusions/{conclusion}', [ConclusionController::class, 'update'])->middleware('permission:meetings.conclusions.update,web');
Route::delete('/{meeting}/conclusions/{conclusion}', [ConclusionController::class, 'destroy'])->middleware('permission:meetings.conclusions.destroy,web');
