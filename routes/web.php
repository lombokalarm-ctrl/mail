<?php

use App\Http\Controllers\Admin\EmailController as AdminEmailController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\InboxController as AdminInboxController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ViewerController;
use App\Http\Controllers\ViewerEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/view/{viewerKey}', ViewerController::class)
    ->middleware('throttle:viewer')
    ->name('viewer.index');

Route::get('/view/{viewerKey}/emails/{email}', ViewerEmailController::class)
    ->middleware('throttle:viewer')
    ->name('viewer.show');

Route::get('/attachments/{attachment}', AttachmentController::class)
    ->middleware('throttle:downloads')
    ->name('attachments.download');

Route::middleware(['auth', 'verified', 'active_user', 'password_changed'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/inboxes', [AdminInboxController::class, 'index'])->name('admin.inboxes.index');
    Route::post('/dashboard/inboxes', [AdminInboxController::class, 'store'])->name('admin.inboxes.store');
    Route::patch('/dashboard/inboxes/{inbox}', [AdminInboxController::class, 'update'])->name('admin.inboxes.update');
    Route::delete('/dashboard/inboxes/{inbox}', [AdminInboxController::class, 'destroy'])->name('admin.inboxes.destroy');
    Route::get('/dashboard/emails', [AdminEmailController::class, 'index'])->name('admin.emails.index');
    Route::delete('/dashboard/emails/{email}', [AdminEmailController::class, 'destroy'])->name('admin.emails.destroy');

    Route::middleware('saas_admin')->group(function () {
        Route::get('/dashboard/groups', [AdminGroupController::class, 'index'])->name('admin.groups.index');
        Route::post('/dashboard/groups', [AdminGroupController::class, 'store'])->name('admin.groups.store');
        Route::post('/dashboard/groups/import-inboxes', [AdminGroupController::class, 'importInboxes'])->name('admin.groups.import-inboxes');
        Route::patch('/dashboard/groups/{group}', [AdminGroupController::class, 'update'])->name('admin.groups.update');
        Route::delete('/dashboard/groups/{group}', [AdminGroupController::class, 'destroy'])->name('admin.groups.destroy');

        Route::get('/dashboard/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('/dashboard/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::patch('/dashboard/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::put('/dashboard/users/{user}/password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::delete('/dashboard/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    });
});

Route::middleware(['auth', 'active_user'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
