<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
| Registered by the level that introduces the underlying job. E.g.
| SyncProviderServicesJob (LEVEL 10/11) and CleanExpiredDataJob (LEVEL 11)
| will each get a Schedule::job(...) entry here once implemented.
*/

// Schedule::job(new \App\Domain\Providers\Jobs\SyncProviderServicesJob)->everyFiveMinutes();
