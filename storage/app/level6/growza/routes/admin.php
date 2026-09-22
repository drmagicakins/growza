<?php

use App\Http\Controllers\Admin\AdminHomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (/admin/*)
|--------------------------------------------------------------------------
| Registered by bootstrap/app.php under the 'web' middleware group with
| the 'admin.' route name prefix (see ARCHITECTURE.md §15).
|
| Only a placeholder index exists at LEVEL 4 — its purpose is to prove the
| full chain (auth -> active -> verified -> permission) actually rejects
| and admits correctly, with a real test behind it, rather than declaring
| RBAC "done" on the strength of a seeder alone. LEVEL 16 replaces this
| with the real admin panel (Users, Orders, Payments, ... modules).
*/
Route::middleware(['auth', 'active', 'verified', 'permission:view_users'])->group(function () {
    Route::get('/', [AdminHomeController::class, 'index'])->name('home');
});
