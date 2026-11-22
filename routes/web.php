<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Hospital;
use App\Http\Controllers\ParentPortal;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/hospitals', [Admin\HospitalController::class, 'index'])->name('hospitals.index');
    Route::get('/hospitals/{hospital}', [Admin\HospitalController::class, 'show'])->name('hospitals.show');
    Route::patch('/hospitals/{hospital}/status', [Admin\HospitalController::class, 'updateStatus'])->name('hospitals.status');

    Route::get('/admins', [Admin\AdminUserController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [Admin\AdminUserController::class, 'create'])->name('admins.create');
    Route::post('/admins', [Admin\AdminUserController::class, 'store'])->name('admins.store');
});

Route::middleware(['auth', 'role:hospital'])->prefix('hospital')->name('hospital.')->group(function () {
    Route::get('/', Hospital\DashboardController::class)->name('dashboard');

    Route::get('/profile', [Hospital\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Hospital\ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/', ParentPortal\DashboardController::class)->name('dashboard');
});

require __DIR__.'/auth.php';
