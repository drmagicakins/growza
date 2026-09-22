<?php

/*
|--------------------------------------------------------------------------
| Marketing Site Content (LEVEL 2)
|--------------------------------------------------------------------------
| Content for the public marketing pages lives here rather than being
| hard-coded into Blade templates. Two reasons:
|
| 1. LEVEL 6 makes the service catalogue database-driven. When that lands,
|    the `services` key below is replaced by a query against the
|    `services`/`platforms` tables — the Blade templates do not change.
| 2. Non-developers can revise marketing copy without touching templates.
|
| ⚠️ PRICING VALUES BELOW ARE PLACEHOLDERS. They must be confirmed against
| your actual commercial model before this site goes live. See LEVEL 32 /
| PROJECT_STATE.md "Open Business Decisions".
*/

return [

    'company' => [
        'name' => 'Growza',
        'tagline' => 'Grow Smarter. Reach Further.',
        'email' => env('GROWZA_CONTACT_EMAIL', 'hello@growza.example'),
        'phone' => env('GROWZA_CONTACT_PHONE', ''),
        'address' => env('GROWZA_CONTACT_ADDRESS', ''),
    ],

    /*
    /*
    | Service categories and the platform list used to live here as
    | static arrays. As of LEVEL 6 both are database-driven —
    | App\Domain\Catalogue\Models\ServiceCategory / Platform / Service,
    | seeded by database/seeders/CatalogueSeeder.php. Querying them is the
    | job of the controller that needs them (MarketingPageController,
    | DashboardController), not this config file — see PROJECT_STATE.md's
    | LEVEL 6 section for what changed and why.
    */

        ['name' => 'Creators', 'description' => 'Building an audience around what you make, without guessing at what the algorithm wants this month.'],
        ['name' => 'Musicians & Labels', 'description' => 'Release campaigns that put the record in front of people who are likely to listen to it twice.'],
        ['name' => 'Businesses', 'description' => 'Acquisition campaigns measured against revenue, not impressions.'],
        ['name' => 'Brands', 'description' => 'Consistent positioning and creative across every channel your customers actually use.'],
        ['name' => 'Agencies', 'description' => 'White-label campaign execution and reporting, with reseller pricing and API access.'],
        ['name' => 'Public Figures', 'description' => 'Reputation-aware audience growth and content strategy for people whose name is the brand.'],
    ],

    'process' => [
        ['step' => '01', 'title' => 'Tell us what you are trying to move', 'description' => 'Sign-ups, streams, ticket sales, enquiries — we start from the outcome, not the channel.'],
        ['step' => '02', 'title' => 'We scope the campaign', 'description' => 'You get a written plan: channels, budget split, creative requirements, timeline and what success looks like.'],
        ['step' => '03', 'title' => 'Fund your wallet and launch', 'description' => 'Top up your Growza wallet, approve the campaign, and it enters the queue for execution.'],
        ['step' => '04', 'title' => 'Track it in real time', 'description' => 'Every campaign has a live status, delivery progress, and a full transaction history in your dashboard.'],
        ['step' => '05', 'title' => 'Review and iterate', 'description' => 'Reporting against the original objective, with a clear recommendation on what to change next cycle.'],
    ],

    /*
    | ⚠️ PLACEHOLDER PRICING — confirm against your real commercial model
    | before launch. Tier names map to the pricing tiers described in
    | ARCHITECTURE.md §22 (retail / reseller / agency / enterprise), which
    | LEVEL 20 implements against the `service_price_tiers` table.
    */
    'pricing' => [
        'note' => 'Campaign budget is separate from management fees. You fund your wallet and see exactly how much goes to ad spend versus management.',
        'tiers' => [
            [
                'name' => 'Starter',
                'price' => 'From ₦75,000',
                'period' => 'per campaign',
                'summary' => 'A single-channel campaign with a defined objective and a fixed run window.',
                'features' => [
                    'One platform, one objective',
                    'Campaign setup and targeting',
                    'Creative review and feedback',
                    'End-of-campaign report',
                    'Email support',
                ],
                'featured' => false,
            ],
            [
                'name' => 'Growth',
                'price' => 'From ₦220,000',
                'period' => 'per month',
                'summary' => 'Ongoing multi-channel management for teams publishing and advertising consistently.',
                'features' => [
                    'Up to three platforms',
                    'Monthly content and campaign plan',
                    'Ongoing optimisation',
                    'Creator partnership sourcing',
                    'Monthly reporting call',
                    'Priority support',
                ],
                'featured' => true,
            ],
            [
                'name' => 'Agency',
                'price' => 'Custom',
                'period' => 'volume-based',
                'summary' => 'White-label execution for agencies managing multiple client accounts.',
                'features' => [
                    'Reseller pricing tier',
                    'Multiple client workspaces',
                    'API access for order placement',
                    'White-label reporting',
                    'Dedicated account manager',
                ],
                'featured' => false,
            ],
        ],
    ],

    'faqs' => [
        [
            'question' => 'Do you sell followers, likes or streams?',
            'answer' => 'No. Growza does not provide artificial engagement of any kind — no purchased followers, bot traffic, or automated engagement. Those services violate every major platform\'s terms of service, routinely get accounts restricted, and do nothing for actual business outcomes. We run campaigns through the platforms\' own advertising products and through legitimate editorial and creator channels.',
        ],
        [
            'question' => 'How does the wallet work?',
            'answer' => 'You fund your Growza wallet by card or bank transfer through our payment partners. Campaign costs are debited from that balance when you place an order, and every debit and credit appears in your transaction history with a reference you can reconcile against.',
        ],
        [
            'question' => 'What happens if a campaign underdelivers?',
            'answer' => 'Orders that complete partially are marked as partially completed, and the undelivered portion is refunded to your wallet. Our full refund terms are on the Refund Policy page.',
        ],
        [
            'question' => 'Do I keep ownership of my ad accounts?',
            'answer' => 'Yes. Where campaigns run through your own Meta, Google, TikTok or LinkedIn ad accounts, those accounts remain yours. We request access rather than asking you to hand over credentials, and access can be revoked at any time.',
        ],
        [
            'question' => 'How long does a campaign take to start?',
            'answer' => 'Most campaigns move from paid to processing within one business day. Campaigns requiring creative production or partnership sourcing take longer, and the estimated timeline is shown before you confirm an order.',
        ],
        [
            'question' => 'Can agencies resell Growza services?',
            'answer' => 'Yes. Agency and reseller accounts get tiered pricing, multiple client workspaces, and API access for placing orders programmatically. Contact us to discuss volume terms.',
        ],
        [
            'question' => 'Which payment methods do you accept?',
            'answer' => 'Card payments and bank transfers are processed through our payment partners. Payments are verified server-side before your wallet is credited.',
        ],
    ],

    /*
    | ⚠️ TESTIMONIALS INTENTIONALLY EMPTY.
    |
    | The marketing spec asks for a testimonials section, and the component
    | to render it exists. But publishing invented testimonials from
    | non-existent customers on a live commercial site is deceptive
    | advertising — so this array ships empty and the section simply does
    | not render until real, permissioned quotes are added here.
    |
    | Format when you have them:
    | ['quote' => '...', 'name' => '...', 'role' => '...', 'company' => '...']
    */
    /*
    | Flip to true ONLY after a qualified legal practitioner in your
    | jurisdiction has reviewed and approved every document in
    | resources/views/marketing/legal/. Until then, each legal page renders
    | a visible "draft, not binding" banner. See LEVEL 32.
    */
    'legal' => [
        'reviewed' => env('GROWZA_LEGAL_REVIEWED', false),
        'updated' => 'Not yet published',
    ],

    'testimonials' => [],
];
