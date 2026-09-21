<?php if (isset($component)) { $__componentOriginal9f98b5f781e6741ece0049186c74547f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f98b5f781e6741ece0049186c74547f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.legal-page','data' => ['title' => 'Cookie Policy','updated' => config('growza-marketing.legal.updated')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('legal-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Cookie Policy','updated' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('growza-marketing.legal.updated'))]); ?>

    <p>This policy explains the cookies Growza uses and why.</p>

    <h2>1. Essential cookies</h2>
    <p>These are required for the platform to work and cannot be switched off:</p>
    <ul>
        <li><strong>Session cookie</strong> — keeps you logged in as you move between pages</li>
        <li><strong>CSRF token cookie</strong> — protects forms against cross-site request forgery</li>
    </ul>

    <h2>2. Analytics and marketing cookies</h2>
    
    <p>The Growza platform does not currently set analytics or advertising cookies. If that changes, this
    policy will be updated and an appropriate consent mechanism will be presented before any such cookie is
    set.</p>

    <h2>3. Managing cookies</h2>
    <p>Most browsers let you block or delete cookies. Blocking essential cookies will prevent you from
    logging in and using the dashboard.</p>

    <h2>4. Contact</h2>
    <p>Questions about cookies can be sent through the <a href="<?php echo e(route('contact')); ?>">contact page</a>.</p>

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
<?php /**PATH C:\Users\DELL\growza\growza\resources\views/marketing/legal/cookie-policy.blade.php ENDPATH**/ ?>