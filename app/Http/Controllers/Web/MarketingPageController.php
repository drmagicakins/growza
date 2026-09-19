<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Renders the static public marketing pages. These are content-only views
 * with no business logic — the copy itself lives in
 * config/growza-marketing.php so it can move to the database at LEVEL 6
 * without touching either this controller or the templates.
 */
class MarketingPageController extends Controller
{
    public function home(): View
    {
        return view('marketing.home');
    }

    public function services(): View
    {
        return view('marketing.services');
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
