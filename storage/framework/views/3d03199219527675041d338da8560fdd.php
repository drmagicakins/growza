<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    // Single source of truth for dashboard navigation — shared between the
    // desktop sidebar and the mobile drawer so the two can never drift.
    // 'route' names are checked defensively: LEVEL 5 links every one of
    // these, but a later level renaming a route here is a one-line fix
    // rather than a hunt through two separate nav markups.
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Services', 'route' => 'dashboard.services'],
        ['label' => 'Orders', 'route' => 'dashboard.orders'],
        ['label' => 'Wallet', 'route' => 'dashboard.wallet'],
        ['label' => 'Transactions', 'route' => 'dashboard.transactions'],
        ['label' => 'Referrals', 'route' => 'dashboard.referrals'],
        ['label' => 'Support', 'route' => 'dashboard.support'],
        ['label' => 'Notifications', 'route' => 'dashboard.notifications'],
        ['label' => 'Settings', 'route' => 'dashboard.settings'],
    ];
?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
    <?php if (isset($component)) { $__componentOriginal42da61123f891e63201d7be28f403427 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42da61123f891e63201d7be28f403427 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.seo','data' => ['title' => $title,'noindex' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('seo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'noindex' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $attributes = $__attributesOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__attributesOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $component = $__componentOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__componentOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-ink-50 text-ink-800 font-sans" x-data="{ drawerOpen: false }">

    
    <aside class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-60 md:flex-col border-r border-ink-200 bg-white">
        <div class="h-16 flex items-center px-6 border-b border-ink-200">
            <a href="<?php echo e(route('home')); ?>" class="font-display text-lg font-semibold text-ink-900">Growza</a>
        </div>
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginal6f0f38b956b6b145f1aa2b3c8617d5f5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f0f38b956b6b145f1aa2b3c8617d5f5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-nav-link','data' => ['href' => route($item['route']),'active' => request()->routeIs($item['route'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route($item['route'])),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs($item['route']))]); ?>
                    <?php echo e($item['label']); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6f0f38b956b6b145f1aa2b3c8617d5f5)): ?>
<?php $attributes = $__attributesOriginal6f0f38b956b6b145f1aa2b3c8617d5f5; ?>
<?php unset($__attributesOriginal6f0f38b956b6b145f1aa2b3c8617d5f5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6f0f38b956b6b145f1aa2b3c8617d5f5)): ?>
<?php $component = $__componentOriginal6f0f38b956b6b145f1aa2b3c8617d5f5; ?>
<?php unset($__componentOriginal6f0f38b956b6b145f1aa2b3c8617d5f5); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </nav>
        <div class="p-4 border-t border-ink-200">
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-sm text-ink-500 hover:text-ink-800">Log out</button>
            </form>
        </div>
    </aside>

    
    <div
        x-show="drawerOpen"
        x-cloak
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-ink-950/50 md:hidden"
        x-on:click="drawerOpen = false"
    ></div>

    <div
        x-show="drawerOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-white flex flex-col md:hidden"
        role="dialog"
        aria-modal="true"
    >
        <div class="h-16 flex items-center justify-between px-5 border-b border-ink-200">
            <span class="font-display text-lg font-semibold text-ink-900">Growza</span>
            <button type="button" x-on:click="drawerOpen = false" class="text-ink-500" aria-label="Close menu">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginal6f0f38b956b6b145f1aa2b3c8617d5f5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f0f38b956b6b145f1aa2b3c8617d5f5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-nav-link','data' => ['href' => route($item['route']),'active' => request()->routeIs($item['route']),'mobile' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route($item['route'])),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs($item['route'])),'mobile' => true]); ?>
                    <?php echo e($item['label']); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6f0f38b956b6b145f1aa2b3c8617d5f5)): ?>
<?php $attributes = $__attributesOriginal6f0f38b956b6b145f1aa2b3c8617d5f5; ?>
<?php unset($__attributesOriginal6f0f38b956b6b145f1aa2b3c8617d5f5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6f0f38b956b6b145f1aa2b3c8617d5f5)): ?>
<?php $component = $__componentOriginal6f0f38b956b6b145f1aa2b3c8617d5f5; ?>
<?php unset($__componentOriginal6f0f38b956b6b145f1aa2b3c8617d5f5); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </nav>
        <div class="p-4 border-t border-ink-200">
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-sm text-ink-500 hover:text-ink-800 px-4 py-2">Log out</button>
            </form>
        </div>
    </div>

    
    <div class="md:pl-60">
        <header class="h-16 border-b border-ink-200 bg-white flex items-center justify-between px-4 md:px-8 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button type="button" x-on:click="drawerOpen = true" class="md:hidden text-ink-700" aria-label="Open menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($heading)): ?>
                    <h1 class="font-display text-lg text-ink-900"><?php echo e($heading); ?></h1>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right']); ?>
                 <?php $__env->slot('trigger', null, []); ?> 
                    <button type="button" class="flex items-center gap-2 text-sm text-ink-700">
                        <span class="w-8 h-8 rounded-full bg-ink-900 text-white flex items-center justify-center text-xs font-medium">
                            <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                        </span>
                        <span class="hidden sm:inline"><?php echo e(auth()->user()->name); ?></span>
                    </button>
                 <?php $__env->endSlot(); ?>

                <?php if (isset($component)) { $__componentOriginal6b1d0d55421798f4a1c7b596bea6c022 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-item','data' => ['href' => route('dashboard.settings')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('dashboard.settings'))]); ?>Settings <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022)): ?>
<?php $attributes = $__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022; ?>
<?php unset($__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1d0d55421798f4a1c7b596bea6c022)): ?>
<?php $component = $__componentOriginal6b1d0d55421798f4a1c7b596bea6c022; ?>
<?php unset($__componentOriginal6b1d0d55421798f4a1c7b596bea6c022); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal6b1d0d55421798f4a1c7b596bea6c022 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-item','data' => ['href' => route('settings.security')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('settings.security'))]); ?>Security <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022)): ?>
<?php $attributes = $__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022; ?>
<?php unset($__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1d0d55421798f4a1c7b596bea6c022)): ?>
<?php $component = $__componentOriginal6b1d0d55421798f4a1c7b596bea6c022; ?>
<?php unset($__componentOriginal6b1d0d55421798f4a1c7b596bea6c022); ?>
<?php endif; ?>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginal6b1d0d55421798f4a1c7b596bea6c022 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-item','data' => ['as' => 'button','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'submit']); ?>Log out <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022)): ?>
<?php $attributes = $__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022; ?>
<?php unset($__attributesOriginal6b1d0d55421798f4a1c7b596bea6c022); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1d0d55421798f4a1c7b596bea6c022)): ?>
<?php $component = $__componentOriginal6b1d0d55421798f4a1c7b596bea6c022; ?>
<?php unset($__componentOriginal6b1d0d55421798f4a1c7b596bea6c022); ?>
<?php endif; ?>
                </form>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
        </header>

        <main class="p-4 md:p-8 max-w-5xl">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['variant' => 'success','class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','class' => 'mb-6']); ?><?php echo e(session('status')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php echo e($slot); ?>

        </main>
    </div>
</body>
</html>
<?php /**PATH C:\Users\DELL\growza\growza\resources\views/components/layouts/dashboard.blade.php ENDPATH**/ ?>