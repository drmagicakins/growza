<?php

namespace App\Http\Controllers\Web;

use App\Domain\Catalogue\Models\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * Technical SEO endpoints (LEVEL 2 / LEVEL 31 groundwork).
 *
 * Static pages are generated from an explicit list of route names; service
 * detail pages (LEVEL 6) are appended dynamically since they come from the
 * database and a hand-maintained list would go stale the moment a service
 * is added or deactivated.
 *
 * Private areas (dashboard, admin, the dev style guide) are never added to
 * this list, and robots.txt disallows them explicitly.
 */
class SitemapController extends Controller
{
    /**
     * Public, indexable routes with their change frequency and priority.
     *
     * @var array<string, array{changefreq: string, priority: string}>
     */
    private const PUBLIC_ROUTES = [
        'home' => ['changefreq' => 'weekly', 'priority' => '1.0'],
        'services' => ['changefreq' => 'monthly', 'priority' => '0.9'],
        'pricing' => ['changefreq' => 'monthly', 'priority' => '0.9'],
        'how-it-works' => ['changefreq' => 'monthly', 'priority' => '0.8'],
        'why-growza' => ['changefreq' => 'monthly', 'priority' => '0.8'],
        'faq' => ['changefreq' => 'monthly', 'priority' => '0.7'],
        'contact' => ['changefreq' => 'yearly', 'priority' => '0.6'],
        'legal.terms' => ['changefreq' => 'yearly', 'priority' => '0.3'],
        'legal.privacy' => ['changefreq' => 'yearly', 'priority' => '0.3'],
        'legal.refund-policy' => ['changefreq' => 'yearly', 'priority' => '0.3'],
        'legal.acceptable-use' => ['changefreq' => 'yearly', 'priority' => '0.3'],
        'legal.cookie-policy' => ['changefreq' => 'yearly', 'priority' => '0.3'],
    ];

    public function index(): Response
    {
        $urls = [];

        foreach (self::PUBLIC_ROUTES as $name => $meta) {
            $urls[] = [
                'loc' => route($name),
                'changefreq' => $meta['changefreq'],
                'priority' => $meta['priority'],
            ];
        }

        foreach (Service::active()->get() as $service) {
            $urls[] = [
                'loc' => route('services.show', $service),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $xml = view('marketing.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        if (config('app.allow_indexing')) {
            // Private areas stay disallowed even when indexing is enabled.
            $lines[] = 'Disallow: /dashboard';
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /dev';
            $lines[] = '';
            $lines[] = 'Sitemap: '.route('sitemap');
        } else {
            // Staging/local: block everything so non-production environments
            // can never be indexed.
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain');
    }
}
