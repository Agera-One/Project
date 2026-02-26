<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\Crud\DocumentController;
use App\Http\Controllers\List\AccountController;
use App\Http\Controllers\List\DashboardController;
use App\Http\Controllers\List\RecentlyController;
use App\Http\Controllers\List\MyDocumentsController;
use App\Http\Controllers\List\StarredController;
use App\Http\Controllers\List\ArchivesController;
use App\Http\Controllers\List\TrashController;

/*
|--------------------------------------------------------------------------|
| PUBLIC                                                                   |
|--------------------------------------------------------------------------|
*/
Route::get('/', function () {
    return Inertia::render('auth/login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------|
| USER AREA                                                                |
|--------------------------------------------------------------------------|
*/
Route::middleware(['auth', 'verified', 'role:user'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/recently', RecentlyController::class)
        ->name('recently');

    Route::get('/my-documents', MyDocumentsController::class)
        ->name('myDocuments');

    Route::get('/starred', StarredController::class)
        ->name('starred');

    Route::get('/archives', ArchivesController::class)
        ->name('archives');

    Route::get('/trash', TrashController::class)
        ->name('trash');
});

/*
|--------------------------------------------------------------------------|
| Document CRUD                                                            |
|--------------------------------------------------------------------------|
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/documents/{document}', [DocumentController::class, 'show'])
        ->name('documents.show');

    Route::post('/documents', [DocumentController::class, 'store'])
        ->name('documents.store');

    Route::patch('/documents/{document}', [DocumentController::class, 'update'])
        ->name('documents.update');

    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])
        ->name('documents.destroy');

    Route::post('/documents/{document}/restore', [DocumentController::class, 'restore'])
        ->name('documents.restore')
        ->withTrashed();

    Route::patch('/documents/{document}/star', [DocumentController::class, 'toggleStar'])
        ->name('documents.star');

    Route::patch('/documents/{document}/archive', [DocumentController::class, 'toggleArchive'])
        ->name('documents.archive');

    Route::delete('/documents/{document}/force-delete', [DocumentController::class, 'forceDelete'])
        ->name('documents.forceDelete')
        ->withTrashed();
});

/*
|--------------------------------------------------------------------------|
| ADMIN AREA                                                               |
|--------------------------------------------------------------------------|
*/
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/admin/recently', RecentlyController::class)
        ->name('admin.recently');

    Route::get('/admin/my-documents', MyDocumentsController::class)
        ->name('admin.myDocuments');

    Route::get('/admin/starred', StarredController::class)
        ->name('admin.starred');

    Route::get('/admin/archives', ArchivesController::class)
        ->name('admin.archives');

    Route::get('/admin/trash', TrashController::class)
        ->name('admin.trash');

    Route::get('/account', [AccountController::class, 'index'])
        ->name('account');

    Route::patch('/account/{account}', [AccountController::class, 'update'])
        ->name('account.update');

    Route::patch('/account/{account}/toggle-active', [AccountController::class, 'toggleActive'])
        ->name('account.toggle-active');

    Route::delete('/account/{account}', [AccountController::class, 'destroy'])
        ->name('account.destroy');
});
