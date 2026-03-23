<?php

use App\Modules\Meeting\AgendaController;
use Illuminate\Support\Facades\Route;

Route::get('/{meeting}/agendas', [AgendaController::class, 'index'])->middleware('permission:meetings.agendas.index,web');
Route::post('/{meeting}/agendas', [AgendaController::class, 'store'])->middleware('permission:meetings.agendas.store,web');
Route::put('/{meeting}/agendas/{agenda}', [AgendaController::class, 'update'])->middleware('permission:meetings.agendas.update,web');
Route::patch('/{meeting}/agendas/{agenda}', [AgendaController::class, 'update'])->middleware('permission:meetings.agendas.update,web');
Route::delete('/{meeting}/agendas/{agenda}', [AgendaController::class, 'destroy'])->middleware('permission:meetings.agendas.destroy,web');
Route::post('/{meeting}/agendas/{agenda}/set-current', [AgendaController::class, 'setCurrent'])->middleware('permission:meetings.agendas.setCurrent,web');
Route::post('/{meeting}/agendas/reorder', [AgendaController::class, 'reorder'])->middleware('permission:meetings.agendas.reorder,web');
