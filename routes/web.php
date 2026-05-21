<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook')
    ->withoutMiddleware(['web'])
    ->middleware('telegram.webhook.secret');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminController::class, 'login'])->name('login');
    Route::post('otp/request', [AdminController::class, 'requestOtp'])->name('otp.request');
    Route::post('otp/verify', [AdminController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('logout', [AdminController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('chats', [AdminController::class, 'chats'])->name('chats.index');
        Route::get('chats/{chatId}', [AdminController::class, 'chatDetail'])->name('chats.show');
        Route::post('settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('connections/{connection}/toggle', [AdminController::class, 'toggleConnection'])->name('connections.toggle');
        Route::post('chats/{chatId}/language', [AdminController::class, 'setChatLanguage'])->name('chats.language.set');
        Route::post('chats/{chatId}/language/reset', [AdminController::class, 'resetChatLanguage'])->name('chats.language.reset');
        Route::post('chats/{chatId}/address', [AdminController::class, 'setAddressForm'])->name('chats.address.set');
        Route::post('commands', [AdminController::class, 'storeCommand'])->name('commands.store');
        Route::delete('commands/{telegramCommand}', [AdminController::class, 'destroyCommand'])->name('commands.destroy');
    });
});
