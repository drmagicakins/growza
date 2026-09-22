<?php

use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\MarketingPageController;
use App\Http\Controllers\Web\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Marketing Routes (LEVEL 2)
|--------------------------------------------------------------------------
| Indexable public pages. Everything here is anonymous-accessible; no route
| in this file may ever assume an authenticated user.
*/

Route::get('/', [MarketingPageController::class, 'home'])->name('home');
Route::get('/services', [MarketingPageController::class, 'services'])->name('services');
Route::get('/services/{service:slug}', [MarketingPageController::class, 'serviceShow'])->name('services.show');
Route::get('/pricing', [MarketingPageController::class, 'pricing'])->name('pricing');
Route::get('/how-it-works', [MarketingPageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/why-growza', [MarketingPageController::class, 'whyGrowza'])->name('why-growza');
Route::get('/faq', [MarketingPageController::class, 'faq'])->name('faq');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');

// Throttled to blunt automated submissions. The honeypot in
// ContactFormRequest handles naive bots; this handles volume.
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('contact.store');

/*
| Legal pages. Routed individually (rather than via a wildcard) so each
| one has a stable named route the footer and sitemap can reference, while
| the controller still allow-lists the page name defensively.
|
| Controller actions with a `page` default rather than closures: closure
| routes cannot be serialised by `php artisan route:cache`, which is part
| of the production deploy sequence (DEPLOYMENT.md).
*/
Route::prefix('legal')->name('legal.')->group(function () {
    foreach ([
        'terms' => 'terms',
        'privacy' => 'privacy',
        'refund-policy' => 'refund-policy',
        'acceptable-use' => 'acceptable-use',
        'cookie-policy' => 'cookie-policy',
    ] as $uri => $page) {
        Route::get("/{$uri}", [MarketingPageController::class, 'legal'])
            ->defaults('page', $page)
            ->name($page);
    }
});

// Technical SEO (LEVEL 31 groundwork). Generated from the route list above
// rather than a hand-maintained XML file, so a new page cannot be forgotten.
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
