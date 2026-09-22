<x-legal-page title="Privacy Policy" :updated="config('growza-marketing.legal.updated')">

    <p>This policy explains what personal data Growza collects, why, and what you can do about it.</p>

    <h2>1. Data we collect</h2>
    <h3>Information you give us</h3>
    <ul>
        <li>Account details: name, email address, phone number</li>
        <li>Campaign information you submit when placing an order</li>
        <li>Messages you send through the contact form or support tickets</li>
    </ul>

    <h3>Information collected automatically</h3>
    <ul>
        <li>IP address and browser user agent, used for security, fraud prevention and rate limiting</li>
        <li>Session data required to keep you logged in</li>
        <li>Records of actions taken on your account, retained in our audit log</li>
    </ul>

    <h3>Information from payment providers</h3>
    <p>When you fund your wallet, our payment providers process the transaction. We receive confirmation of
    payment status and a transaction reference. <strong>We do not receive or store your full card number.</strong></p>

    <h2>2. How we use it</h2>
    <ul>
        <li>To provide and deliver the campaigns you order</li>
        <li>To process payments and maintain an accurate financial record</li>
        <li>To send service communications about your account, orders and payments</li>
        <li>To detect and prevent fraud and abuse of the platform</li>
        <li>To meet legal and accounting obligations</li>
    </ul>
    <p>You can control non-essential notifications in your account settings. Security notifications, such as
    password changes, cannot be disabled.</p>

    <h2>3. Sharing</h2>
    <p>We share data only where necessary to run the service:</p>
    <ul>
        <li>Payment providers, to process transactions</li>
        <li>Advertising platforms, where a campaign requires it</li>
        <li>Infrastructure providers who host the platform</li>
        <li>Authorities, where we are legally required to do so</li>
    </ul>
    <p>We do not sell personal data.</p>

    <h2>4. Retention</h2>
    <p>Account and transaction records are retained while your account is active and afterwards for as long as
    required for legal, tax and accounting purposes.</p>
    {{-- REVIEW NOTE: specify exact retention periods per data category once
         counsel has confirmed the applicable NDPA and tax requirements. --}}

    <h2>5. Your rights</h2>
    <p>Subject to applicable law, you may request access to the personal data we hold about you, ask us to
    correct inaccurate data, or request deletion where we are not required to retain it.</p>
    {{-- REVIEW NOTE: confirm the response window and verification process
         required under the Nigeria Data Protection Act. --}}

    <h2>6. Security</h2>
    <p>Passwords are stored hashed, never in readable form. Access to production data is restricted, and
    sensitive actions are recorded in an audit log. No system is perfectly secure, but we design for the
    assumption that any component may be compromised.</p>

    <h2>7. Cookies</h2>
    <p>See our <a href="{{ route('legal.cookie-policy') }}">Cookie Policy</a>.</p>

    <h2>8. Contact</h2>
    <p>Privacy questions can be sent through the <a href="{{ route('contact') }}">contact page</a>.</p>
    {{-- REVIEW NOTE: if a Data Protection Officer is required, their contact
         details must be published here. --}}

</x-legal-page>
