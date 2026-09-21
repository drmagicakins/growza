<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Public marketing site (LEVEL 2), authentication (LEVEL 3), and the
| customer dashboard (LEVEL 5) are all registered from this file, split
| into route-group files per domain as each level lands, so this file
| stays a short table of contents rather than growing unbounded.
|
| Nothing beyond the framework health check exists at LEVEL 0 — routes
| are added by the level that owns them, per PROJECT_STATE.md.
*/

// Living design-system reference (LEVEL 1). Registered only outside
// production so the style guide never becomes a public route in a real
// deployment — see DESIGN_SYSTEM.md.
if (! app()->environment('production')) {
    Route::get('/dev/design-system', function () {
        return view('dev.design-system');
    })->name('dev.design-system');
}

// Public marketing site, including the `home` route that previously lived
// inline in this file (LEVEL 2).
require __DIR__.'/marketing.php';

// Authenticated screens (LEVEL 3). Fortify registers the auth endpoints
// themselves; this file holds the screens Growza owns.
require __DIR__.'/auth.php';
// require __DIR__.'/auth.php';        // LEVEL 3
// require __DIR__.'/dashboard.php';   // LEVEL 5
