<?php

/*
|--------------------------------------------------------------------------
| Marketing Site Content (LEVEL 2)
|--------------------------------------------------------------------------
| The master prompt forbids hard-coding services into frontend code. The
| real, database-driven catalogue arrives at LEVEL 6 (platforms /
| service_categories / services tables). Until then, marketing-page content
| lives here in config rather than being inlined into Blade templates, so:
|
|   1. No Blade file contains a hard-coded service list.
|   2. LEVEL 6's migration path is a straight swap — the marketing
|      controllers start reading from the Catalogue domain instead of this
|      file, and the Blade templates don't change at all.
|
| IMPORTANT — product scope. Growza is explicitly NOT a follower/engagement
| delivery panel. Per §1 of the master spec the platform must not be built
| around fake engagement, bots, deceptive followers or platform
| manipulation. Every service described below is therefore a legitimate
| marketing service: managed ad spend, creator partnerships, editorial and
| curator outreach, content production, and analytics. Nothing here
| promises a quantity of followers, plays, or likes for a fee. Keep it that
| way when LEVEL 6 seeds the real catalogue.
*/

return [

    'platforms' => [
        ['name' => 'Instagram', 'slug' => 'instagram', 'blurb' => 'Paid social, creator partnerships, and Reels-first content strategy.'],
        ['name' => 'TikTok',    'slug' => 'tiktok',    'blurb' => 'Spark Ads, creator briefs, and sound-led campaign planning.'],
        ['name' => 'YouTube',   'slug' => 'youtube',   'blurb' => 'In-stream and Shorts campaigns, channel strategy, and retention review.'],
        ['name' => 'Facebook',  'slug' => 'facebook',  'blurb' => 'Audience building, Advantage+ campaign management, and retargeting.'],
        ['name' => 'X',         'slug' => 'x',         'blurb' => 'Announcement amplification and conversation-led campaign support.'],
        ['name' => 'LinkedIn',  'slug' => 'linkedin',  'blurb' => 'B2B demand generation, thought-leadership programmes, and lead forms.'],
        ['name' => 'Spotify',   'slug' => 'spotify',   'blurb' => 'Editorial and independent curator pitching, plus release-day planning.'],
        ['name' => 'Audiomack', 'slug' => 'audiomack', 'blurb' => 'Release positioning and placement outreach across African markets.'],
        ['name' => 'SoundCloud','slug' => 'soundcloud','blurb' => 'Repost network outreach and early-stage audience development.'],
    ],

    /*
     | Service categories shown on /services. `starting_price` is a display
     | figure for the marketing page only — real, authoritative pricing comes
     | from the `services` / `service_price_tiers` tables at LEVEL 6, and the
     | pricing page links through to the catalogue rather than transacting.
     */
    'services' => [
        [
            'name' => 'Managed Ad Campaigns',
            'slug' => 'managed-ad-campaigns',
            'summary' => 'We plan, launch, and optimise paid campaigns on the platforms your audience actually uses — and you keep full ownership of the ad account.',
            'includes' => [
                'Campaign strategy and audience research',
                'Creative briefing and ad build',
                'Daily optimisation and budget pacing',
                'Weekly performance reporting',
            ],
            'starting_price' => 'From ₦150,000 / month + ad spend',
            'platforms' => ['Instagram', 'TikTok', 'YouTube', 'Facebook', 'LinkedIn'],
        ],
        [
            'name' => 'Creator & Influencer Partnerships',
            'slug' => 'creator-partnerships',
            'summary' => 'Matching with vetted creators whose audience fits your brief, with contracts, deliverables, and disclosure handled properly.',
            'includes' => [
                'Creator sourcing and audience vetting',
                'Rate negotiation and contracting',
                'Brief development and content approval',
                'Disclosure compliance (ASA / FTC-style labelling)',
            ],
            'starting_price' => 'From ₦200,000 per campaign',
            'platforms' => ['Instagram', 'TikTok', 'YouTube'],
        ],
        [
            'name' => 'Music Release Campaigns',
            'slug' => 'music-release-campaigns',
            'summary' => 'Release-day planning and legitimate curator outreach for artists and labels. We pitch — curators decide.',
            'includes' => [
                'Release timeline and asset checklist',
                'Editorial and independent curator pitching',
                'Press and blog outreach',
                'Post-release performance review',
            ],
            'starting_price' => 'From ₦120,000 per release',
            'platforms' => ['Spotify', 'Audiomack', 'SoundCloud'],
        ],
        [
            'name' => 'Content Strategy & Production',
            'slug' => 'content-strategy',
            'summary' => 'A content plan built on what your analytics actually show, plus the production support to ship it consistently.',
            'includes' => [
                'Channel and competitor audit',
                'Monthly content calendar',
                'Short-form video production support',
                'Copywriting and caption frameworks',
            ],
            'starting_price' => 'From ₦180,000 / month',
            'platforms' => ['Instagram', 'TikTok', 'YouTube', 'LinkedIn'],
        ],
        [
            'name' => 'Analytics & Reporting',
            'slug' => 'analytics-reporting',
            'summary' => 'Consolidated reporting across your channels, so you can see what is working without exporting five dashboards by hand.',
            'includes' => [
                'Cross-platform performance dashboard',
                'Campaign attribution review',
                'Monthly written analysis',
                'Quarterly strategy session',
            ],
            'starting_price' => 'From ₦90,000 / month',
            'platforms' => ['Instagram', 'TikTok', 'YouTube', 'Facebook', 'X', 'LinkedIn'],
        ],
        [
            'name' => 'Brand & Press Placement',
            'slug' => 'brand-press-placement',
            'summary' => 'Earned coverage through genuine editorial relationships — pitched on merit, never paid-for-placement disguised as editorial.',
            'includes' => [
                'Story angle development',
                'Media list building',
                'Pitch writing and follow-up',
                'Coverage tracking',
            ],
            'starting_price' => 'From ₦250,000 per campaign',
            'platforms' => ['X', 'LinkedIn'],
        ],
    ],

    /*
     | Marketing-page pricing tiers. These mirror the `service_price_tiers`
     | concept from ARCHITECTURE.md §22 (Retail / Reseller / Agency /
     | Enterprise) but are display-only until LEVEL 6/20 make them real.
     */
    'pricing_tiers' => [
        [
            'name' => 'Starter',
            'price' => '₦90,000',
            'cadence' => 'per month',
            'best_for' => 'Solo creators and early-stage brands running one channel properly.',
            'features' => [
                'One managed channel',
                'Monthly content calendar',
                'Consolidated analytics dashboard',
                'Email support',
            ],
            'featured' => false,
        ],
        [
            'name' => 'Growth',
            'price' => '₦280,000',
            'cadence' => 'per month',
            'best_for' => 'Established brands and artists running paid campaigns alongside organic.',
            'features' => [
                'Up to three managed channels',
                'Managed ad campaigns (ad spend billed separately)',
                'Creator partnership sourcing',
                'Weekly reporting and a monthly strategy call',
                'Priority support',
            ],
            'featured' => true,
        ],
        [
            'name' => 'Agency',
            'price' => 'Custom',
            'cadence' => 'billed per client',
            'best_for' => 'Agencies and labels managing campaigns for multiple clients.',
            'features' => [
                'Multi-client workspaces',
                'Team roles and permissions',
                'Reseller pricing tiers',
                'API access',
                'Dedicated account manager',
            ],
            'featured' => false,
        ],
    ],

    'audiences' => [
        ['title' => 'Creators', 'body' => 'You are making the work already. We handle distribution strategy, paid amplification, and the reporting that tells you which posts actually moved the needle.'],
        ['title' => 'Musicians & labels', 'body' => 'Release planning, curator and press pitching, and campaign support built around how streaming platforms actually surface new music.'],
        ['title' => 'Businesses & brands', 'body' => 'Demand generation that ties back to revenue, not vanity metrics — with the ad account and audience data staying in your name.'],
        ['title' => 'Agencies', 'body' => 'Multi-client workspaces, reseller pricing, and an API so Growza fits inside the workflow you already run.'],
    ],

    'process' => [
        ['step' => '01', 'title' => 'Tell us the objective', 'body' => 'Not "more followers" — something measurable. Ticket sales, stream growth in a specific market, qualified leads, launch-week reach.'],
        ['step' => '02', 'title' => 'We scope and quote', 'body' => 'You get a written scope with deliverables, timeline, and what success will be measured against, before any money moves.'],
        ['step' => '03', 'title' => 'Fund your wallet', 'body' => 'Top up once and draw down across campaigns, or pay per campaign. Every transaction is recorded in a ledger you can audit line by line.'],
        ['step' => '04', 'title' => 'Campaign runs', 'body' => 'Track status from your dashboard as work moves through queued, processing, and completed — with a full status history, not a silent progress bar.'],
        ['step' => '05', 'title' => 'Review and iterate', 'body' => 'Written performance analysis at the end of each cycle, including what underperformed and what we would change next time.'],
    ],

    'faqs' => [
        [
            'q' => 'Do you sell followers, likes, streams, or views?',
            'a' => 'No. Growza does not sell engagement metrics and does not work with providers who generate them artificially. Buying engagement violates the terms of service of every major platform, risks your account, and produces an audience that never converts. What we sell is managed advertising, creator partnerships, editorial outreach, content production, and analytics.',
        ],
        [
            'q' => 'Who owns the ad account and audience data?',
            'a' => 'You do. We run campaigns inside your ad account wherever the platform allows it. If you stop working with us, your account, your pixel data, and your custom audiences stay with you.',
        ],
        [
            'q' => 'How does the wallet work?',
            'a' => 'You fund a Growza wallet, and campaign costs are drawn from that balance. Every credit and debit is written to a permanent transaction ledger with its own reference, so your spending history is auditable at any time. Wallet balances and ad spend are tracked separately.',
        ],
        [
            'q' => 'Can I get a refund?',
            'a' => 'Refund eligibility depends on how far a campaign has progressed and whether third-party costs such as ad spend have already been committed. The specifics are set out on our refund policy page. Approved refunds are returned to your Growza wallet or original payment method.',
        ],
        [
            'q' => 'What payment methods do you accept?',
            'a' => 'Card and bank transfer payments are processed through Paystack and Flutterwave. Growza never stores your full card details — payment information is handled by the gateway.',
        ],
        [
            'q' => 'How quickly do campaigns start?',
            'a' => 'Scoping usually takes two to three business days. Once a campaign is approved and funded, ad campaigns typically go live within 48 hours. Creator partnerships and press outreach depend on third-party response times, so we give a realistic window in the written scope rather than a fixed promise.',
        ],
        [
            'q' => 'Do you work with clients outside Nigeria?',
            'a' => 'Yes. Our team works primarily in Nigerian and wider African markets, which is where our creator and press relationships are strongest, but campaigns can target any market the advertising platforms support.',
        ],
        [
            'q' => 'What happens if a campaign underperforms?',
            'a' => 'You get an honest written account of what happened and what we would change. Underperformance is reported, not hidden behind a favourable chart. Where a campaign failed because of something on our side, we discuss remediation — that conversation is covered by the refund policy.',
        ],
    ],

    'contact' => [
        'email' => env('MARKETING_CONTACT_EMAIL', 'hello@growza.example'),
        'response_time' => 'We reply to enquiries within one business day.',
        'subjects' => [
            'general' => 'General enquiry',
            'sales' => 'New campaign / pricing',
            'support' => 'Existing campaign support',
            'billing' => 'Billing or refunds',
            'partnership' => 'Partnership or press',
        ],
    ],
];
