<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SubActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RevisionNoteController;
use App\Http\Controllers\MasterKodeController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// === TESTING HALAMAN ERROR (hanya aktif di local, otomatis nonaktif saat production) ===
if (app()->environment('local')) {
    Route::get('/dev/test-error/{code}', function (int $code) {
        abort($code);
    })->whereNumber('code')->name('dev.test-error');
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // === SEARCH (Program, Kegiatan, Sub Kegiatan) ===
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])
        ->name('search');

    // === AUTO-GENERATE KODE PROGRAM ===
    Route::get('/program/next-code', [ProgramController::class, 'nextCode'])
        ->name('program.next-code');

    // === DATA MASTER KODE (untuk auto-fill nama/indikator/target/pagu dari referensi Excel) ===
    Route::get('/master-kode', [MasterKodeController::class, 'index'])
        ->name('master-kode.index');

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

    Route::post('/program/{id}/konfirmasi',
        [ProgramController::class, 'konfirmasi'])
        ->name('program.konfirmasi');

    Route::post('/program/{id}/kembalikan',
        [ProgramController::class, 'kembalikan'])
        ->name('program.kembalikan');

    Route::post('/program/{id}/tolak',
        [ProgramController::class, 'tolak'])
        ->name('program.tolak');

    Route::post('/program/{id}/ajukan',
        [ProgramController::class, 'ajukan'])
        ->name('program.ajukan');

    // === CATATAN PERBAIKAN (REVISION NOTES) ===
    Route::post('/revision-notes',
        [RevisionNoteController::class, 'store'])
        ->name('revision-notes.store');

    Route::post('/revision-notes/{revisionNote}/konfirmasi',
        [RevisionNoteController::class, 'konfirmasi'])
        ->name('revision-notes.konfirmasi');

    // === ARSIP & LAPORAN ===
    Route::get('/arsip', [App\Http\Controllers\ArsipController::class, 'index'])
        ->name('arsip.index');

    Route::get('/ranwal/print', [ProgramController::class, 'ranwal']);

    Route::get('/ranwal/export', [ProgramController::class, 'exportExcel']);

    // === KELOLA AKUN (khusus admin — otorisasi diatur di UserController) ===
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__.'/settings.php';
