<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\Crud\DocumentController;
use App\Http\Controllers\List\DashboardController;
use App\Http\Controllers\List\RecentlyController;
use App\Http\Controllers\List\MyDocumentsController;
use App\Http\Controllers\List\StarredController;
use App\Http\Controllers\List\ArchivesController;
use App\Http\Controllers\List\TrashController;

Route::get('/', function () {
    return Inertia::render('auth/login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    Route::get('/recently', RecentlyController::class)->name('recently');
    Route::get('/my-documents', MyDocumentsController::class)->name('myDocuments');
    Route::get('/starred', StarredController::class)->name('starred');
    Route::get('/archives', ArchivesController::class)->name('archives');
    Route::get('/trash', TrashController::class)->name('trash');

    Route::resource('documents', DocumentController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::post('documents/{document}/restore', [DocumentController::class, 'restore'])
        ->name('documents.restore');
});
