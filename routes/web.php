<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\ProgramController;



Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('program', ProgramController::class);

    Route::post('/program/{program}/activity',
        [ProgramController::class, 'storeActivity']
    )->name('program.activity.store');

    Route::post('/activity/{activity}/sub',
        [ProgramController::class, 'storeSubActivity']
    )->name('activity.sub.store');

    Route::put(
        '/program/{program}',
        [ProgramController::class, 'update']
    )->name('program.update');

    Route::delete(
        '/program/{program}',
        [ProgramController::class, 'destroy']
    )->name('program.destroy');

    Route::put(
        '/activity/{activity}',
        [ProgramController::class, 'updateActivity']
    )->name('activity.update');

    Route::delete(
        '/activity/{activity}',
        [ProgramController::class, 'destroyActivity']
    )->name('activity.destroy');

    Route::put(
        '/sub/{subActivity}',
        [ProgramController::class, 'updateSubActivity']
    )->name('sub.update');

    Route::delete(
        '/sub/{subActivity}',
        [ProgramController::class, 'destroySubActivity']
    )->name('sub.destroy');

    Route::post('/program/{id}/verifikasi', [ProgramController::class, 'verifikasi'])
    ->name('program.verifikasi');

    Route::post('/program/{id}/setujui', [ProgramController::class, 'setujui'])
    ->name('program.setujui');

    Route::post('/program/{id}/kembalikan', [ProgramController::class, 'kembalikan'])
    ->name('program.kembalikan');

    Route::get('/arsip', [App\Http\Controllers\ArsipController::class, 'index'])
    ->name('arsip.index');

    Route::get('/ranwal/print', [ProgramController::class, 'ranwal']);

    Route::get('/ranwal/export', [ProgramController::class, 'exportExcel']);
});


require __DIR__.'/settings.php';
