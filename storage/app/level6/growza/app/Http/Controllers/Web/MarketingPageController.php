<?php

namespace App\Http\Controllers\Web;

use App\Domain\Catalogue\Models\Platform;
use App\Domain\Catalogue\Models\Service;
use App\Domain\Catalogue\Models\ServiceCategory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Renders the public marketing pages.
 *
 * Catalogue content (platforms, categories, services) is read from the
 * database as of LEVEL 6 — see `App\Domain\Catalogue\Models`. Everything
 * else (pricing tiers copy, process steps, FAQs, company info) still
 * lives in config/growza-marketing.php; only the catalogue itself moved,
 * which is exactly what LEVEL 6 scoped. This did require two small,
 * expected changes: this controller now queries Eloquent instead of
 * reading a config array, and the two views that iterate services switch
 * from array access ($service['name']) to object property access
 * ($service->name) — flagged in PROJECT_STATE.md as anticipated at LEVEL 5,
 * not a surprise rewrite.
 */
class MarketingPageController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', [
            'platforms' => Platform::active()->ordered()->get(),
            'services' => Service::active()->ordered()->with(['platform', 'category'])->take(6)->get(),
        ]);
    }

    public function services(): View
    {
        return view('marketing.services', [
            'categories' => ServiceCategory::active()->ordered()
                ->with(['services' => fn ($query) => $query->active()->ordered()->with('platform')])
                ->get()
                ->filter(fn (ServiceCategory $category) => $category->services->isNotEmpty()),
        ]);
    }

    public function serviceShow(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('marketing.service-detail', [
            'service' => $service->load(['platform', 'category']),
        ]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing');
    }

    public function howItWorks(): View
    {
        return view('marketing.how-it-works');
    }

    public function whyGrowza(): View
    {
        return view('marketing.why-growza');
    }

    public function faq(): View
    {
        return view('marketing.faq');
    }

    /**
     * Legal pages are rendered through a single action with an allow-list
     * so a new policy page is one array entry plus one view — and so no
     * user-supplied string can ever reach view() directly.
     */
    public function legal(string $page): View
    {
        $allowed = ['terms', 'privacy', 'refund-policy', 'acceptable-use', 'cookie-policy'];

        abort_unless(in_array($page, $allowed, true), 404);

        return view("marketing.legal.{$page}");
    }
}
