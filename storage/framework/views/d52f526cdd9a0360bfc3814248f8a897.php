<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'title' => null, 'maxWidth' => 'md']));

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

foreach (array_filter((['name', 'title' => null, 'maxWidth' => 'md']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $maxWidths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl'];
?>


<div
    x-data="{ open: false }"
    x-on:open-modal-<?php echo e($name); ?>.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-ink-950/50" x-on:click="open = false"></div>

    <div
        x-show="open"
        x-transition
        class="relative bg-white rounded-lg shadow-floating w-full <?php echo e($maxWidths[$maxWidth] ?? $maxWidths['md']); ?> p-6"
        role="dialog"
        aria-modal="true"
    >
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-ink-900"><?php echo e($title); ?></h3>
                <button type="button" x-on:click="open = false" class="text-ink-400 hover:text-ink-700" aria-label="Close">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/modal.blade.php ENDPATH**/ ?>