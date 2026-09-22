<x-legal-page title="Terms of Service" :updated="config('growza-marketing.legal.updated')">

    <p>These terms govern your use of the Growza platform. By creating an account you agree to them.</p>

    <h2>1. The service</h2>
    <p>Growza provides digital marketing and campaign management services, including paid advertising
    management, content strategy, creator partnerships and promotional campaigns, delivered through the
    official advertising and editorial channels of third-party platforms.</p>

    <h2>2. Accounts</h2>
    <ul>
        <li>You must provide accurate information when registering and keep it up to date</li>
        <li>You are responsible for maintaining the security of your password and API credentials</li>
        <li>You must notify us promptly of any unauthorised use of your account</li>
        <li>Accounts may not be transferred without our written consent</li>
    </ul>

    <h2>3. Acceptable use</h2>
    <p>Your use of Growza is subject to our <a href="{{ route('legal.acceptable-use') }}">Acceptable Use Policy</a>,
    which forms part of these terms.</p>

    <h2>4. Wallet and payments</h2>
    <ul>
        <li>Campaign costs are paid from your Growza wallet balance</li>
        <li>Wallet funds are credited only after a payment is verified with our payment provider</li>
        <li>Every credit and debit is recorded with a reference and is visible in your transaction history</li>
        <li>Wallet balances are held in Nigerian Naira unless otherwise agreed in writing</li>
    </ul>
    {{-- REVIEW NOTE: whether stored wallet balances constitute a regulated
         activity under CBN rules must be confirmed by counsel. Also confirm
         treatment of dormant balances and whether funds are held in trust. --}}

    <h2>5. Orders and delivery</h2>
    <ul>
        <li>Estimated delivery timelines are shown before you confirm an order and are estimates, not guarantees</li>
        <li>Campaign performance depends on factors outside our control, including platform policies, auction dynamics and audience behaviour</li>
        <li>We do not guarantee any specific commercial result, ranking, reach or conversion figure</li>
    </ul>

    <h2>6. Refunds</h2>
    <p>Refunds are governed by our <a href="{{ route('legal.refund-policy') }}">Refund Policy</a>.</p>

    <h2>7. Intellectual property</h2>
    <p>You retain ownership of the content, trade marks and materials you supply. You grant us the licence
    necessary to use those materials for the purpose of delivering the campaigns you order. The Growza
    platform, its software and branding remain our property.</p>

    <h2>8. Suspension and termination</h2>
    <p>We may suspend or close an account that breaches these terms or the Acceptable Use Policy. You may
    close your account at any time.</p>
    {{-- REVIEW NOTE: notice periods, wind-down of in-flight campaigns and
         treatment of remaining wallet balance on closure need counsel input. --}}

    <h2>9. Liability</h2>
    {{-- REVIEW NOTE: This section is intentionally left for counsel to draft.
         Limitation-of-liability wording is jurisdiction-specific and drafting
         it without qualified review would be irresponsible. --}}
    <p>The limitation of liability applying to these terms will be set out here following legal review.</p>

    <h2>10. Governing law</h2>
    {{-- REVIEW NOTE: confirm governing law, jurisdiction and dispute
         resolution mechanism (courts vs arbitration) with counsel. --}}
    <p>The governing law and dispute resolution provisions will be set out here following legal review.</p>

    <h2>11. Changes to these terms</h2>
    <p>We may update these terms. Where changes are material we will give notice through the platform or by
    email before they take effect.</p>

    <h2>12. Contact</h2>
    <p>Questions about these terms can be sent through the <a href="{{ route('contact') }}">contact page</a>.</p>

</x-legal-page>
