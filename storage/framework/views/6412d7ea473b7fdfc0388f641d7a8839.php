<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['align' => 'right']));

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

foreach (array_filter((['align' => 'right']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div
    x-data="{ open: false }"
    class="relative inline-block"
>
    <div
        x-on:click.stop="open = !open"
        :aria-expanded="open"
    >
        <?php echo e($trigger); ?>

    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-on:click.outside="open = false"
        x-on:keydown.escape.window="open = false"
        class="absolute <?php echo e($align === 'right' ? 'right-0' : 'left-0'); ?> mt-2 w-56 rounded-md bg-white border border-ink-200 shadow-raised py-1 z-40"
    >
        <?php echo e($slot); ?>

    </div>
</div><?php /**PATH /var/www/html/resources/views/components/dropdown.blade.php ENDPATH**/ ?>