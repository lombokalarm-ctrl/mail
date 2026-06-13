<?php

use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\InboxController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/inboxes', [InboxController::class, 'index']);
    Route::get('/inboxes/{inbox}', [InboxController::class, 'show']);
    Route::get('/emails', [EmailController::class, 'index']);
    Route::get('/emails/{email}', [EmailController::class, 'show']);
});

Route::middleware(['auth', 'throttle:api'])->delete('/emails/{email}', [EmailController::class, 'destroy']);
