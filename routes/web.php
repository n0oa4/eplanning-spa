<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SubActivityController;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // === PROGRAM ===
    Route::resource('program', ProgramController::class);

    // === ACTIVITY ===
    Route::post('/program/{program}/activity',
        [ActivityController::class, 'store'])
        ->name('program.activity.store');

    Route::put('/activity/{activity}',
        [ActivityController::class, 'update'])
        ->name('activity.update');

    Route::delete('/activity/{activity}',
        [ActivityController::class, 'destroy'])
        ->name('activity.destroy');

    // === SUB ACTIVITY ===
    Route::post('/activity/{activity}/sub',
        [SubActivityController::class, 'store'])
        ->name('activity.sub.store');

    Route::put('/sub/{subActivity}',
        [SubActivityController::class, 'update'])
        ->name('sub.update');

    Route::delete('/sub/{subActivity}',
        [SubActivityController::class, 'destroy'])
        ->name('sub.destroy');

    // === APPROVAL ===
    Route::post('/program/{id}/verifikasi',
        [ProgramController::class, 'verifikasi'])
        ->name('program.verifikasi');

    Route::post('/program/{id}/setujui',
        [ProgramController::class, 'setujui'])
        ->name('program.setujui');

    Route::post('/program/{id}/kembalikan',
        [ProgramController::class, 'kembalikan'])
        ->name('program.kembalikan');

    // === ARSIP & LAPORAN ===
    Route::get('/arsip', [App\Http\Controllers\ArsipController::class, 'index'])
        ->name('arsip.index');

    Route::get('/ranwal/print', [ProgramController::class, 'ranwal']);

    Route::get('/ranwal/export', [ProgramController::class, 'exportExcel']);
});

require __DIR__.'/settings.php';
