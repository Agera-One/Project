<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'loginValidate'])->name('login.post');

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('register')->name('register.')->group(function () {
    Route::get('/choose', [RegisterController::class, 'registerChoose'])->name('choose');
    Route::get('/siswa', [RegisterController::class, 'registerSiswaForm'])->name('siswa.form');
    Route::post('/siswa', [RegisterController::class, 'registerSiswaStore'])->name('siswa.store');
    Route::get('/perusahaan', [RegisterController::class, 'registerPerusahaanForm'])->name('perusahaan.form');
    Route::post('/perusahaan', [RegisterController::class, 'registerPerusahaanStore'])->name('perusahaan.store');
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('siswa')->name('siswa.')->middleware('role:siswa')->group(function () {
        Route::get('/dashboard', [SiswaController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/lowongan', [SiswaController::class, 'lowongan'])
            ->name('lowongan');

        Route::get('/get-lowongan-data', [SiswaController::class, 'getLowonganData'])
            ->name('get-lowongan-data');

        Route::get('/lamaran/{lowongan_id}', [SiswaController::class, 'lamaranForm'])
            ->name('lamaran.form');
        Route::post('/lamaran/{lowongan_id}', [SiswaController::class, 'lamaranStore'])
            ->name('lamaran.store');
    });

    Route::prefix('perusahaan')->name('perusahaan.')->middleware('role:perusahaan')->group(function () {
        Route::get('/dashboard', [PerusahaanController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/lowongan', [PerusahaanController::class, 'lowonganForm'])
            ->name('lowongan.form');

        Route::post('/lowongan', [PerusahaanController::class, 'lowonganStore'])
            ->name('lowongan.store');

        Route::get('/lowongan/{lowongan}/edit', [PerusahaanController::class, 'lowonganEdit'])
            ->name('lowongan.edit');

        Route::put('/lowongan/{lowongan}', [PerusahaanController::class, 'lowonganUpdate'])
            ->name('lowongan.update');

        Route::delete('/lowongan/{lowongan}', [PerusahaanController::class, 'lowonganDestroy'])
            ->name('lowongan.destroy');

        Route::get('/pelamar', [PerusahaanController::class, 'pelamar'])
            ->name('pelamar');

        Route::get('/pelamar/lowongan/{lowongan}', [PerusahaanController::class, 'pelamarLowongan'])
            ->name('pelamar.lowongan');

        Route::post('/pelamar/update-status', [PerusahaanController::class, 'updateStatusPelamar'])
            ->name('pelamar.update-status');
    });
});
