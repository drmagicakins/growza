<?php

namespace Database\Seeders;

use App\Domain\Catalogue\Models\Platform;
use App\Domain\Catalogue\Models\Service;
use App\Domain\Catalogue\Models\ServiceCategory;
use App\Domain\Catalogue\Models\ServicePriceTier;
use Illuminate\Database\Seeder;

/**
 * Seeds real platforms, categories and services (LEVEL 6).
 *
 * These service definitions mirror what config/growza-marketing.php used
 * to hold as marketing copy — this seeder is what LEVEL 6 promised would
 * replace that config as the actual source, per ARCHITECTURE.md §6.
 * Idempotent throughout via updateOrCreate.
 */
class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = $this->seedPlatforms();
        $categories = $this->seedCategories();
        $this->seedServices($platforms, $categories);
    }

    /**
     * @return array<string, Platform>
     */
    private function seedPlatforms(): array
    {
        $names = [
            'Instagram', 'TikTok', 'YouTube', 'Facebook', 'X',
            'LinkedIn', 'Spotify', 'Audiomack', 'SoundCloud',
        ];

        $platforms = [];

        foreach ($names as $index => $name) {
            $platforms[$name] = Platform::updateOrCreate(
                ['slug' => str($name)->slug()->toString()],
                ['name' => $name, 'is_active' => true, 'sort_order' => $index]
            );
        }

        return $platforms;
    }

    /**
     * @return array<string, ServiceCategory>
     */
    private function seedCategories(): array
    {
        $defs = [
            ['name' => 'Paid Social', 'description' => 'Managed advertising through official ad platforms.'],
            ['name' => 'Content Strategy', 'description' => 'Publishing plans and creative production.'],
            ['name' => 'Music Promotion', 'description' => 'Release campaigns and editorial pitching.'],
            ['name' => 'Creator Partnerships', 'description' => 'Sourced and managed creator collaborations.'],
            ['name' => 'Search & Discovery', 'description' => 'Technical SEO and local discovery.'],
            ['name' => 'Analytics & Reporting', 'description' => 'Measurement and attribution setup.'],
        ];

        $categories = [];

        foreach ($defs as $index => $def) {
            $categories[$def['name']] = ServiceCategory::updateOrCreate(
                ['slug' => str($def['name'])->slug()->toString()],
                [
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }

        return $categories;
    }

    /**
     * @param  array<string, Platform>  $platforms
     * @param  array<string, ServiceCategory>  $categories
     */
    private function seedServices(array $platforms, array $categories): void
    {
        $services = [
            [
                'name' => 'Instagram & Facebook Ad Campaign',
                'category' => 'Paid Social',
                'platform' => 'Instagram',
                'summary' => 'Managed Meta Ads campaign across Instagram and Facebook, built around one clear objective.',
                'description' => 'We plan audience targeting, write and test ad creative, and manage budget pacing through Meta Ads Manager for the length of the campaign. You keep ownership of your ad account.',
                'pricing_model' => 'budget_range',
                'min_budget_minor' => 5_000_00,
                'max_budget_minor' => 500_000_00,
                'management_fee_minor' => 15_000_00,
                'base_price_minor' => null,
                'customer_price_minor' => null,
                'delivery' => [1, 2],
                'requirements' => 'Access to your Meta Business Manager (or we can help you create one).',
            ],
            [
                'name' => 'TikTok Ads Campaign',
                'category' => 'Paid Social',
                'platform' => 'TikTok',
                'summary' => 'A TikTok Ads Manager campaign with creative built for the platform, not repurposed from elsewhere.',
                'description' => 'Includes audience research, at least two ad creative concepts, and ongoing spend optimisation for the campaign window.',
                'pricing_model' => 'budget_range',
                'min_budget_minor' => 10_000_00,
                'max_budget_minor' => 400_000_00,
                'management_fee_minor' => 18_000_00,
                'base_price_minor' => null,
                'customer_price_minor' => null,
                'delivery' => [1, 2],
                'requirements' => 'Access to your TikTok Ads Manager account.',
            ],
            [
                'name' => 'Google Search Ads Campaign',
                'category' => 'Paid Social',
                'platform' => null,
                'summary' => 'Intent-driven Google Search campaign for businesses with a clear conversion goal.',
                'description' => 'Keyword research, ad copywriting, conversion tracking setup and bid management through Google Ads.',
                'pricing_model' => 'budget_range',
                'min_budget_minor' => 15_000_00,
                'max_budget_minor' => 600_000_00,
                'management_fee_minor' => 20_000_00,
                'base_price_minor' => null,
                'customer_price_minor' => null,
                'delivery' => [2, 3],
                'requirements' => 'Access to your Google Ads account and a defined conversion goal (calls, form fills, or purchases).',
            ],
            [
                'name' => 'Content Strategy — Starter',
                'category' => 'Content Strategy',
                'platform' => 'Instagram',
                'summary' => 'A one-month content audit and posting plan for a single platform.',
                'description' => 'Audit of your last 90 days of posts, a content pillar framework, and a 30-day posting calendar with captions.',
                'pricing_model' => 'fixed',
                'min_budget_minor' => null,
                'max_budget_minor' => null,
                'management_fee_minor' => null,
                'base_price_minor' => 40_000_00,
                'customer_price_minor' => 75_000_00,
                'delivery' => [5, 7],
                'requirements' => 'Access to view your account insights/analytics.',
            ],
            [
                'name' => 'Short-Form Video Production',
                'category' => 'Content Strategy',
                'platform' => 'TikTok',
                'summary' => 'Scripted, shot and edited short-form video content for TikTok and Reels.',
                'description' => 'A batch of five short-form videos: scripting, filming coordination or editing of your own footage, and platform-native formatting.',
                'pricing_model' => 'fixed',
                'min_budget_minor' => null,
                'max_budget_minor' => null,
                'management_fee_minor' => null,
                'base_price_minor' => 90_000_00,
                'customer_price_minor' => 150_000_00,
                'delivery' => [7, 10],
                'requirements' => 'Raw footage or availability for a coordinated shoot.',
            ],
            [
                'name' => 'Release Campaign — Single',
                'category' => 'Music Promotion',
                'platform' => 'Spotify',
                'summary' => 'A four-week rollout plan and editorial pitching campaign for a single release.',
                'description' => 'Release timeline planning, pitch submissions to Spotify and independent curator playlists through official channels, and a paid campaign around the release date.',
                'pricing_model' => 'fixed',
                'min_budget_minor' => null,
                'max_budget_minor' => null,
                'management_fee_minor' => null,
                'base_price_minor' => 60_000_00,
                'customer_price_minor' => 120_000_00,
                'delivery' => [14, 21],
                'requirements' => 'Finished master at least 3 weeks before release date, Spotify for Artists access.',
                'refund_policy_note' => 'Editorial pitch placement is never guaranteed by any platform — this fee covers the pitching and campaign work itself, not a placement outcome.',
            ],
            [
                'name' => 'Audiomack & SoundCloud Push',
                'category' => 'Music Promotion',
                'platform' => 'Audiomack',
                'summary' => 'Upload optimisation and promotional push across Audiomack and SoundCloud.',
                'description' => 'Metadata and artwork optimisation, submission to relevant Audiomack charts/playlists where eligible, and a short paid promotion window.',
                'pricing_model' => 'fixed',
                'min_budget_minor' => null,
                'max_budget_minor' => null,
                'management_fee_minor' => null,
                'base_price_minor' => 25_000_00,
                'customer_price_minor' => 45_000_00,
                'delivery' => [3, 5],
                'requirements' => 'Finished audio file and cover art.',
            ],
            [
                'name' => 'Creator Partnership Sourcing',
                'category' => 'Creator Partnerships',
                'platform' => 'Instagram',
                'summary' => 'We source, vet and manage a creator collaboration end to end.',
                'description' => 'Includes creator shortlisting based on audience overlap, outreach and negotiation, a written brief, and delivery management. All partnerships are disclosed per advertising standards.',
                'pricing_model' => 'fixed',
                'min_budget_minor' => null,
                'max_budget_minor' => null,
                'management_fee_minor' => null,
                'base_price_minor' => 50_000_00,
                'customer_price_minor' => 100_000_00,
                'delivery' => [10, 14],
                'requirements' => 'Budget range for creator fees (paid directly to the creator, separate from this fee).',
            ],
            [
                'name' => 'Local SEO Setup',
                'category' => 'Search & Discovery',
                'platform' => null,
                'summary' => 'Google Business Profile optimisation and on-page SEO for a local business.',
                'description' => 'Google Business Profile setup or cleanup, on-page optimisation for up to 5 pages, and a keyword-targeted content plan.',
                'pricing_model' => 'fixed',
                'min_budget_minor' => null,
                'max_budget_minor' => null,
                'management_fee_minor' => null,
                'base_price_minor' => 45_000_00,
                'customer_price_minor' => 85_000_00,
                'delivery' => [7, 14],
                'requirements' => 'Access to your website and Google Business Profile.',
            ],
            [
                'name' => 'Campaign Analytics Setup',
                'category' => 'Analytics & Reporting',
                'platform' => null,
                'summary' => 'Proper tracking and attribution setup so ad spend can be measured against real outcomes.',
                'description' => 'Conversion tracking setup across your ad platforms and website, a reporting dashboard, and a walkthrough session.',
                'pricing_model' => 'fixed',
                'min_budget_minor' => null,
                'max_budget_minor' => null,
                'management_fee_minor' => null,
                'base_price_minor' => 35_000_00,
                'customer_price_minor' => 65_000_00,
                'delivery' => [5, 7],
                'requirements' => 'Admin access to your website and ad accounts.',
            ],
        ];

        foreach ($services as $index => $def) {
            $service = Service::updateOrCreate(
                ['slug' => str($def['name'])->slug()->toString()],
                [
                    'platform_id' => $def['platform'] ? $platforms[$def['platform']]->id : null,
                    'service_category_id' => $categories[$def['category']]->id,
                    'name' => $def['name'],
                    'summary' => $def['summary'],
                    'description' => $def['description'],
                    'pricing_model' => $def['pricing_model'],
                    'min_budget_minor' => $def['min_budget_minor'],
                    'max_budget_minor' => $def['max_budget_minor'],
                    'management_fee_minor' => $def['management_fee_minor'],
                    'base_price_minor' => $def['base_price_minor'],
                    'customer_price_minor' => $def['customer_price_minor'],
                    'estimated_delivery_min_days' => $def['delivery'][0],
                    'estimated_delivery_max_days' => $def['delivery'][1],
                    'requirements' => $def['requirements'] ?? null,
                    'terms' => null,
                    'refund_policy_note' => $def['refund_policy_note'] ?? null,
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            // Retail tier mirrors the base customer price so the table is
            // populated from day one — LEVEL 20 adds real reseller/agency/
            // enterprise overrides.
            if ($service->customer_price_minor !== null) {
                ServicePriceTier::updateOrCreate(
                    ['service_id' => $service->id, 'tier' => 'retail'],
                    ['price_minor' => $service->customer_price_minor]
                );
            }
        }
    }
}
