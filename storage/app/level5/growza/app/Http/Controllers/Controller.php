<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * Base controller.
 *
 * Laravel 11's slim skeleton omits this class by default. Growza needs it
 * because every state-changing controller action is required to call an
 * explicit `$this->authorize(...)` Policy check (ARCHITECTURE.md §7) —
 * which is what the AuthorizesRequests trait provides.
 */
abstract class Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
