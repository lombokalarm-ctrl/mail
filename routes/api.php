<?php

use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\GroupInboxController;
use App\Http\Controllers\Api\InboxController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:api'])->group(function () {
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    Route::patch('/groups/{group}', [GroupController::class, 'update']);
    Route::delete('/groups/{group}', [GroupController::class, 'destroy']);
    Route::post('/groups/{group}/inboxes', [GroupInboxController::class, 'store']);
    Route::delete('/groups/{group}/inboxes/{inbox}', [GroupInboxController::class, 'destroy']);

    Route::get('/inboxes', [InboxController::class, 'index']);
    Route::get('/inboxes/{inbox}', [InboxController::class, 'show']);
    Route::get('/emails', [EmailController::class, 'index']);
    Route::get('/emails/{email}', [EmailController::class, 'show']);
    Route::delete('/emails/{email}', [EmailController::class, 'destroy']);
});
