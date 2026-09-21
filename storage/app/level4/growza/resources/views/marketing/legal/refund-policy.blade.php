<x-legal-page title="Refund Policy" :updated="config('growza-marketing.legal.updated')">

    <p>This policy explains when money returns to your wallet and how that process works.</p>

    <h2>1. Campaigns that do not start</h2>
    <p>If an order is paid but cannot be started — because a service becomes unavailable, a platform rejects
    the campaign, or we cannot fulfil it — the full amount is returned to your wallet balance.</p>

    <h2>2. Partial delivery</h2>
    <p>Where a campaign delivers part of what was ordered, the order is marked as partially completed and the
    undelivered portion is refunded to your wallet. The refund appears as its own transaction with a
    reference linking it to the original order.</p>

    <h2>3. Cancellation</h2>
    <ul>
        <li>Orders that have not yet started processing can be cancelled for a full refund to your wallet</li>
        <li>Orders already in progress may be cancellable in part, depending on the service; any refundable portion returns to your wallet</li>
        <li>Completed orders are not refundable on the basis of performance alone, since campaign outcomes depend on factors outside our control</li>
    </ul>

    <h2>4. Refunds to wallet, not to card</h2>
    <p>Refunds are credited to your Growza wallet by default, which makes them immediately reusable.</p>
    {{-- REVIEW NOTE: whether customers have a statutory right to withdraw
         wallet funds back to their original payment method, and on what
         timescale, must be confirmed by counsel before publication. The
         wording below is a placeholder pending that advice. --}}
    <p>Arrangements for withdrawing a wallet balance to your original payment method will be set out here
    following legal review.</p>

    <h2>5. Breaches of the Acceptable Use Policy</h2>
    <p>Where an order is cancelled because it breached the
    <a href="{{ route('legal.acceptable-use') }}">Acceptable Use Policy</a>, refund treatment depends on
    whether any spend was already committed on your behalf.</p>
    {{-- REVIEW NOTE: confirm with counsel whether funds may be withheld in
         cases of policy breach or suspected fraud, and on what basis. --}}

    <h2>6. Disputes</h2>
    <p>If you believe a charge or refund is wrong, raise it through the
    <a href="{{ route('contact') }}">contact page</a> or a support ticket. Every wallet transaction carries a
    reference, which makes reconciliation straightforward.</p>

</x-legal-page>
