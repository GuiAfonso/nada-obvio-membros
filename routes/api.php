<?php

use App\Http\Controllers\HotmartWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/hotmart', [HotmartWebhookController::class, 'handle'])->name('webhooks.hotmart');
