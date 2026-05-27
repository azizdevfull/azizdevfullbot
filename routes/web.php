<?php

use App\Http\Controllers\Admin\LearnController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(
        ['message' => 'Hello world!']
    );
});

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook')
    ->withoutMiddleware(['web'])
    ->middleware('telegram.webhook.secret');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminController::class, 'login'])->name('login');
    Route::post('login', [AdminController::class, 'postLogin'])->name('login.post');
    Route::post('otp/request', [AdminController::class, 'requestOtp'])->name('otp.request');
    Route::post('otp/verify', [AdminController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('logout', [AdminController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('connections', [AdminController::class, 'connections'])->name('connections.index');
        Route::post('connections/{connection}/toggle', [AdminController::class, 'toggleConnection'])->name('connections.toggle');

        Route::get('commands', [AdminController::class, 'commands'])->name('commands.index');
        Route::post('commands', [AdminController::class, 'storeCommand'])->name('commands.store');
        Route::delete('commands/{telegramCommand}', [AdminController::class, 'destroyCommand'])->name('commands.destroy');

        Route::get('settings', [AdminController::class, 'settings'])->name('settings.index');
        Route::post('settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        Route::get('chats', [AdminController::class, 'chats'])->name('chats.index');
        Route::get('chats/{chatId}', [AdminController::class, 'chatDetail'])->name('chats.show');
        Route::get('chats/{chatId}/messages', [AdminController::class, 'chatMessages'])->name('chats.messages');
        Route::post('chats/{chatId}/language', [AdminController::class, 'setChatLanguage'])->name('chats.language.set');
        Route::post('chats/{chatId}/language/reset', [AdminController::class, 'resetChatLanguage'])->name('chats.language.reset');
        Route::post('chats/{chatId}/address', [AdminController::class, 'setAddressForm'])->name('chats.address.set');
        Route::post('chats/{chatId}/persona', [AdminController::class, 'setChatPersona'])->name('chats.persona.set');
        Route::post('chats/{chatId}/status', [AdminController::class, 'updateChatStatus'])->name('chats.status.update');

        Route::get('personas', [AdminController::class, 'personas'])->name('personas.index');
        Route::post('personas', [AdminController::class, 'storePersona'])->name('personas.store');
        Route::put('personas/{persona}', [AdminController::class, 'updatePersona'])->name('personas.update');
        Route::delete('personas/{persona}', [AdminController::class, 'destroyPersona'])->name('personas.destroy');

        Route::get('profile', [AdminController::class, 'profile'])->name('profile');
        Route::post('profile', [AdminController::class, 'updateProfile'])->name('profile.update');

        Route::get('chats/{chatId}/export', [AdminController::class, 'exportChat'])->name('chats.export');
        Route::post('chats/{chatId}/clear', [AdminController::class, 'clearChatMessages'])->name('chats.clear');
        Route::delete('messages/{message}', [AdminController::class, 'destroyChatMessage'])->name('messages.destroy');

        Route::post('learn/{chatId}/analyze', [LearnController::class, 'analyze'])->name('learn.analyze');
        Route::get('learn/{chatId}/review', [LearnController::class, 'review'])->name('learn.review');
        Route::post('learn/{chatId}/save', [LearnController::class, 'save'])->name('learn.save');
        Route::post('learn/{chatId}/reject', [LearnController::class, 'reject'])->name('learn.reject');
    });
});
