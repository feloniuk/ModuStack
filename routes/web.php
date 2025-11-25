<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'is_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/plans', function () {
        return Inertia::render('Admin/Plans');
    })->name('admin.plans');

    Route::get('/users', function () {
        return Inertia::render('Admin/Users');
    })->name('admin.users');

    Route::get('/providers', function () {
        return Inertia::render('Admin/Providers');
    })->name('admin.providers');

    Route::get('/subscriptions', function () {
        return Inertia::render('Admin/Subscriptions');
    })->name('admin.subscriptions');
});

require __DIR__.'/auth.php';
