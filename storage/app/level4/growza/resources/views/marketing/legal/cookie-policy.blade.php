<x-legal-page title="Cookie Policy" :updated="config('growza-marketing.legal.updated')">

    <p>This policy explains the cookies Growza uses and why.</p>

    <h2>1. Essential cookies</h2>
    <p>These are required for the platform to work and cannot be switched off:</p>
    <ul>
        <li><strong>Session cookie</strong> — keeps you logged in as you move between pages</li>
        <li><strong>CSRF token cookie</strong> — protects forms against cross-site request forgery</li>
    </ul>

    <h2>2. Analytics and marketing cookies</h2>
    {{-- REVIEW NOTE: This section currently describes the platform as built.
         If analytics or advertising pixels are added later, this policy and
         the consent mechanism below must be updated BEFORE they are
         deployed — not after. --}}
    <p>The Growza platform does not currently set analytics or advertising cookies. If that changes, this
    policy will be updated and an appropriate consent mechanism will be presented before any such cookie is
    set.</p>

    <h2>3. Managing cookies</h2>
    <p>Most browsers let you block or delete cookies. Blocking essential cookies will prevent you from
    logging in and using the dashboard.</p>

    <h2>4. Contact</h2>
    <p>Questions about cookies can be sent through the <a href="{{ route('contact') }}">contact page</a>.</p>

</x-legal-page>
