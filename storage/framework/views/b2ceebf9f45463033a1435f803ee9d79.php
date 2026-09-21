<?php if (isset($component)) { $__componentOriginal9f98b5f781e6741ece0049186c74547f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f98b5f781e6741ece0049186c74547f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.legal-page','data' => ['title' => 'Terms of Service','updated' => config('growza-marketing.legal.updated')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('legal-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Terms of Service','updated' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('growza-marketing.legal.updated'))]); ?>

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
    <p>Your use of Growza is subject to our <a href="<?php echo e(route('legal.acceptable-use')); ?>">Acceptable Use Policy</a>,
    which forms part of these terms.</p>

    <h2>4. Wallet and payments</h2>
    <ul>
        <li>Campaign costs are paid from your Growza wallet balance</li>
        <li>Wallet funds are credited only after a payment is verified with our payment provider</li>
        <li>Every credit and debit is recorded with a reference and is visible in your transaction history</li>
        <li>Wallet balances are held in Nigerian Naira unless otherwise agreed in writing</li>
    </ul>
    

    <h2>5. Orders and delivery</h2>
    <ul>
        <li>Estimated delivery timelines are shown before you confirm an order and are estimates, not guarantees</li>
        <li>Campaign performance depends on factors outside our control, including platform policies, auction dynamics and audience behaviour</li>
        <li>We do not guarantee any specific commercial result, ranking, reach or conversion figure</li>
    </ul>

    <h2>6. Refunds</h2>
    <p>Refunds are governed by our <a href="<?php echo e(route('legal.refund-policy')); ?>">Refund Policy</a>.</p>

    <h2>7. Intellectual property</h2>
    <p>You retain ownership of the content, trade marks and materials you supply. You grant us the licence
    necessary to use those materials for the purpose of delivering the campaigns you order. The Growza
    platform, its software and branding remain our property.</p>

    <h2>8. Suspension and termination</h2>
    <p>We may suspend or close an account that breaches these terms or the Acceptable Use Policy. You may
    close your account at any time.</p>
    

    <h2>9. Liability</h2>
    
    <p>The limitation of liability applying to these terms will be set out here following legal review.</p>

    <h2>10. Governing law</h2>
    
    <p>The governing law and dispute resolution provisions will be set out here following legal review.</p>

    <h2>11. Changes to these terms</h2>
    <p>We may update these terms. Where changes are material we will give notice through the platform or by
    email before they take effect.</p>

    <h2>12. Contact</h2>
    <p>Questions about these terms can be sent through the <a href="<?php echo e(route('contact')); ?>">contact page</a>.</p>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f98b5f781e6741ece0049186c74547f)): ?>
<?php $attributes = $__attributesOriginal9f98b5f781e6741ece0049186c74547f; ?>
<?php unset($__attributesOriginal9f98b5f781e6741ece0049186c74547f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f98b5f781e6741ece0049186c74547f)): ?>
<?php $component = $__componentOriginal9f98b5f781e6741ece0049186c74547f; ?>
<?php unset($__componentOriginal9f98b5f781e6741ece0049186c74547f); ?>
<?php endif; ?>
<?php /**PATH C:\Users\DELL\growza\growza\resources\views/marketing/legal/terms.blade.php ENDPATH**/ ?>