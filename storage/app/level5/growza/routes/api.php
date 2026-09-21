<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (/api/v1/*)
|--------------------------------------------------------------------------
| Versioned per ARCHITECTURE.md §14. Nothing is registered at LEVEL 0 —
| endpoints (GET /services, GET /balance, POST /orders, etc.) are added
| at LEVEL 19 once auth (LEVEL 3), catalogue (LEVEL 6), and orders
| (LEVEL 7) exist for them to expose.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // require __DIR__.'/api/v1/services.php';
    // require __DIR__.'/api/v1/orders.php';
});
