<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Public marketing pages (LEVEL 2).
 *
 * These are read-only content pages, so the controller does exactly what
 * ARCHITECTURE.md §2 says a controller should: resolve data and hand it to
 * a view. Content comes from config('marketing.*') rather than being
 * inlined into Blade, so LEVEL 6 can swap the source to the Catalogue
 * domain without touching a single template.
 */
final class PageController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', [
            'services' => array_slice(config('marketing.services'), 0, 3),
            'platforms' => config('marketing.platforms'),
            'audiences' => config('marketing.audiences'),
            'process' => config('marketing.process'),
        ]);
    }

    public function services(): View
    {
        return view('marketing.services', [
            'services' => config('marketing.services'),
            'platforms' => config('marketing.platforms'),
        ]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing', [
            'tiers' => config('marketing.pricing_tiers'),
            'faqs' => array_slice(config('marketing.faqs'), 0, 4),
        ]);
    }

    public function howItWorks(): View
    {
        return view('marketing.how-it-works', [
            'process' => config('marketing.process'),
        ]);
    }

    public function whyGrowza(): View
    {
        return view('marketing.why-growza', [
            'audiences' => config('marketing.audiences'),
        ]);
    }

    public function faq(): View
    {
        return view('marketing.faq', [
            'faqs' => config('marketing.faqs'),
        ]);
    }

    /**
     * Legal pages. Per master prompt §32 these must not invent legal
     * claims — each view carries an explicit "pending legal review"
     * notice rather than presenting unreviewed text as binding terms.
     */
    public function legal(string $page): View
    {
        $allowed = ['terms', 'privacy', 'refund-policy', 'acceptable-use'];

        abort_unless(in_array($page, $allowed, true), 404);

        return view("marketing.legal.{$page}");
    }
}
