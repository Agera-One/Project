<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('auth/login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    Route::get('recently', function () {
        return Inertia::render('recently');
    })->name('recently');
    Route::get('starred', function () {
        return Inertia::render('starred');
    })->name('starred');
    Route::get('my-documents', function () {
        return Inertia::render('my-documents');
    })->name('myDocuments');
    Route::get('shared', function () {
        return Inertia::render('shared');
    })->name('shared');
    Route::get('archives', function () {
        return Inertia::render('archives');
    })->name('archives');
    Route::get('trash', function () {
        return Inertia::render('trash');
    })->name('trash');
});

require __DIR__.'/settings.php';
