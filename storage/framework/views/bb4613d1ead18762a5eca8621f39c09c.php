<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => 'info', 'title' => null, 'dismissible' => false]));

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

foreach (array_filter((['variant' => 'info', 'title' => null, 'dismissible' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $variants = [
        'success' => ['bg' => 'bg-success-50 border-success-500 text-success-700'],
        'warning' => ['bg' => 'bg-warning-50 border-warning-500 text-warning-700'],
        'danger' => ['bg' => 'bg-danger-50 border-danger-500 text-danger-700'],
        'info' => ['bg' => 'bg-info-50 border-info-500 text-info-700'],
    ];
    $style = $variants[$variant] ?? $variants['info'];
?>

<div
    <?php if($dismissible): ?> x-data="{ show: true }" x-show="show" <?php endif; ?>
    <?php echo e($attributes->merge(['class' => 'border-l-4 rounded px-4 py-3 text-sm ' . $style['bg']])); ?>

    role="alert"
>
    <div class="flex items-start justify-between gap-3">
        <div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
                <p class="font-medium mb-0.5"><?php echo e($title); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div><?php echo e($slot); ?></div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dismissible): ?>
            <button type="button" @click="show = false" class="shrink-0 opacity-60 hover:opacity-100" aria-label="Dismiss">
                &times;
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/alert.blade.php ENDPATH**/ ?>