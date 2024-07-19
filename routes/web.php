<?php

use FriendsOfBotble\UddoktaPay\Http\Controllers\UddoktaPayController;
use Illuminate\Support\Facades\Route;

Route::middleware(['core', 'web'])->prefix('payment/uddokta-pay')->name('payment.uddokta-pay.')->group(function () {
    Route::get('webhook', [UddoktaPayController::class, 'webhook'])->name('webhook');
    Route::get('error', [UddoktaPayController::class, 'error'])->name('error');
    Route::get('success', [UddoktaPayController::class, 'success'])->name('success');
});
