<?php

use App\Http\Controllers\Admin\EmailController as AdminEmailController;
use App\Http\Controllers\Admin\InboxController as AdminInboxController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/inboxes', [AdminInboxController::class, 'index'])->name('admin.inboxes.index');
    Route::delete('/dashboard/inboxes/{inbox}', [AdminInboxController::class, 'destroy'])->name('admin.inboxes.destroy');
    Route::get('/dashboard/emails', [AdminEmailController::class, 'index'])->name('admin.emails.index');
    Route::delete('/dashboard/emails/{email}', [AdminEmailController::class, 'destroy'])->name('admin.emails.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
