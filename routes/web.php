<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Hospital;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentPortal;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/hospitals', [Admin\HospitalController::class, 'index'])->name('hospitals.index');
    Route::get('/hospitals/{hospital}', [Admin\HospitalController::class, 'show'])->name('hospitals.show');
    Route::patch('/hospitals/{hospital}/status', [Admin\HospitalController::class, 'updateStatus'])->name('hospitals.status');
    Route::delete('/hospitals/{hospital}', [Admin\HospitalController::class, 'destroy'])->name('hospitals.destroy');

    Route::get('/admins', [Admin\AdminUserController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [Admin\AdminUserController::class, 'create'])->name('admins.create');
    Route::post('/admins', [Admin\AdminUserController::class, 'store'])->name('admins.store');

    Route::get('/activity', [Admin\ActivityLogController::class, 'index'])->name('activity.index');

    Route::get('/vaccines/schedule', [Admin\VaccineController::class, 'schedule'])->name('vaccines.schedule');
    Route::resource('vaccines', Admin\VaccineController::class)->except('show');
});

Route::middleware(['auth', 'role:hospital'])->prefix('hospital')->name('hospital.')->group(function () {
    Route::get('/', Hospital\DashboardController::class)->name('dashboard');

    Route::get('/profile', [Hospital\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Hospital\ProfileController::class, 'update'])->name('profile.update');

    Route::resource('stock', Hospital\StockController::class)
        ->except('show')
        ->parameters(['stock' => 'batch']);

    Route::get('/slots/bulk', [Hospital\TimeSlotController::class, 'bulkCreate'])->name('slots.bulk');
    Route::post('/slots/bulk', [Hospital\TimeSlotController::class, 'bulkStore'])->name('slots.bulk.store');
    Route::resource('slots', Hospital\TimeSlotController::class)->except('show');

    Route::get('/appointments', [Hospital\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [Hospital\AppointmentController::class, 'show'])->name('appointments.show');
    Route::patch('/appointments/{appointment}/approve', [Hospital\AppointmentController::class, 'approve'])->name('appointments.approve');
    Route::patch('/appointments/{appointment}/reject', [Hospital\AppointmentController::class, 'reject'])->name('appointments.reject');
    Route::patch('/appointments/{appointment}/complete', [Hospital\AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::patch('/appointments/{appointment}/no-show', [Hospital\AppointmentController::class, 'noShow'])->name('appointments.no-show');
    Route::post('/appointments/{appointment}/history', [Hospital\AppointmentController::class, 'recordHistory'])->name('appointments.history');
});

Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/', ParentPortal\DashboardController::class)->name('dashboard');

    Route::get('/children/{child}/certificate', [ParentPortal\ChildController::class, 'certificate'])->name('children.certificate');
    Route::get('/children/{child}/certificate.pdf', [ParentPortal\ChildController::class, 'certificatePdf'])->name('children.certificate.pdf');
    Route::resource('children', ParentPortal\ChildController::class);

    // Booking: child → doses → hospital → slot → confirm.
    Route::get('/book', [ParentPortal\BookingController::class, 'children'])->name('book');
    Route::get('/book/{child}', [ParentPortal\BookingController::class, 'doses'])->name('book.doses');
    Route::get('/book/{child}/hospitals', [ParentPortal\BookingController::class, 'hospitals'])->name('book.hospitals');
    Route::get('/book/{child}/hospitals/{hospital}', [ParentPortal\BookingController::class, 'slots'])->name('book.slots');
    Route::get('/book/{child}/confirm', [ParentPortal\BookingController::class, 'confirm'])->name('book.confirm');

    Route::get('/appointments', [ParentPortal\AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [ParentPortal\AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/appointments/{appointment}/cancel', [ParentPortal\AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/appointments/{appointment}/reschedule', [ParentPortal\AppointmentController::class, 'editSchedule'])->name('appointments.reschedule');
    Route::patch('/appointments/{appointment}/reschedule', [ParentPortal\AppointmentController::class, 'reschedule'])->name('appointments.reschedule.update');
});

require __DIR__.'/auth.php';
