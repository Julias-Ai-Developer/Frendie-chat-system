<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::view('/', 'index')->name('home');

    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('users', [ChatController::class, 'users'])->name('users');
        Route::get('conversations/{user}', [ChatController::class, 'conversation'])->name('conversation');
        Route::post('messages', [ChatController::class, 'send'])->name('send');
        Route::get('messages/{message}/attachment', [ChatController::class, 'attachment'])->name('message.attachment');
        Route::post('messages/{message}/consume-view-once', [ChatController::class, 'consumeViewOnce'])->name('message.consume');
        Route::post('typing', [ChatController::class, 'updateTyping'])->name('typing.update');
        Route::get('typing/{user}', [ChatController::class, 'typingStatus'])->name('typing.status');
        Route::post('calls/start', [ChatController::class, 'startCall'])->name('call.start');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});

require __DIR__.'/settings.php';
