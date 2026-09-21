<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['question', 'open' => false]));

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

foreach (array_filter((['question', 'open' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>


<details class="group border-b border-ink-200 py-5" <?php if($open): ?> open <?php endif; ?>>
    <summary class="flex items-start justify-between gap-4 cursor-pointer list-none">
        <h3 class="font-medium text-ink-900 text-base"><?php echo e($question); ?></h3>
        <span class="shrink-0 mt-1 text-ink-400 transition-transform group-open:rotate-45" aria-hidden="true">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
        </span>
    </summary>
    <div class="mt-3 text-ink-600 max-w-prose"><?php echo e($slot); ?></div>
</details>
<?php /**PATH C:\Users\DELL\growza\growza\resources\views/components/faq-item.blade.php ENDPATH**/ ?>