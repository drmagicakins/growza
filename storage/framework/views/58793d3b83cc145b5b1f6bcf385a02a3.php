<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['href', 'active' => false, 'mobile' => false]));

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

foreach (array_filter((['href', 'active' => false, 'mobile' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    // Mobile touch targets are deliberately larger (py-3.5 vs py-2.5) —
    // a real adjustment for the input method, not the same markup shrunk
    // to fit. See DESIGN_SYSTEM.md / LEVEL 30.
    $padding = $mobile ? 'px-4 py-3.5' : 'px-3 py-2.5';
?>

<a
    href="<?php echo e($href); ?>"
    <?php echo e($attributes->merge([
        'class' => "flex items-center gap-3 {$padding} rounded text-sm font-medium transition-colors "
            . ($active ? 'bg-ink-900 text-white' : 'text-ink-600 hover:bg-ink-100 hover:text-ink-900')
    ])); ?>

>
    <?php echo e($slot); ?>

</a>
<?php /**PATH C:\Users\DELL\growza\growza\resources\views/components/dashboard-nav-link.blade.php ENDPATH**/ ?>