<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Placeholder admin landing page (LEVEL 4). Exists to prove the permission
 * middleware chain works end to end — real modules (Users, Orders,
 * Payments, ...) are built at LEVEL 16.
 */
class AdminHomeController extends Controller
{
    public function index(): View
    {
        return view('admin.placeholder');
    }
}
