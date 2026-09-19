<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Security settings screen.
 *
 * The state-changing endpoints behind this page (password update, 2FA
 * enable/confirm/disable, recovery code regeneration) are all provided by
 * Fortify and handled by Growza's own Actions in App\Domain\Auth\Actions.
 * This controller only renders the view.
 */
class SecuritySettingsController extends Controller
{
    public function show(Request $request): View
    {
        return view('settings.security', [
            'user' => $request->user(),
        ]);
    }
}
