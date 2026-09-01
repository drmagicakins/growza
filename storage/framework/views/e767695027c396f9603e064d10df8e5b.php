<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary', // primary | secondary | ghost | danger
    'size' => 'md',         // sm | md | lg
    'as' => 'button',        // button | a
]));

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

foreach (array_filter(([
    'variant' => 'primary', // primary | secondary | ghost | danger
    'size' => 'md',         // sm | md | lg
    'as' => 'button',        // button | a
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $variants = [
        'primary' => 'bg-ink-900 text-white hover:bg-ink-800 active:bg-ink-950 disabled:bg-ink-300',
        'secondary' => 'bg-white text-ink-800 border border-ink-200 hover:bg-ink-50 active:bg-ink-100 disabled:text-ink-300',
        'ghost' => 'bg-transparent text-ink-700 hover:bg-ink-100 active:bg-ink-200 disabled:text-ink-300',
        'danger' => 'bg-danger-500 text-white hover:bg-danger-700 active:bg-danger-700 disabled:bg-danger-50 disabled:text-danger-500',
        'accent' => 'bg-ember-500 text-ink-950 hover:bg-ember-400 active:bg-ember-600 disabled:bg-ember-100',
    ];

    $sizes = [
        'sm' => 'text-sm px-3 py-1.5 gap-1.5',
        'md' => 'text-sm px-4 py-2.5 gap-2',
        'lg' => 'text-base px-5 py-3 gap-2',
    ];

    $classes = 'inline-flex items-center justify-center rounded font-medium transition-colors duration-150 '
        . 'disabled:cursor-not-allowed '
        . ($variants[$variant] ?? $variants['primary']) . ' '
        . ($sizes[$size] ?? $sizes['md']);
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($as === 'a'): ?>
    <a <?php echo e($attributes->merge(['class' => $classes])); ?>><?php echo e($slot); ?></a>
<?php else: ?>
    <button <?php echo e($attributes->merge(['type' => 'button', 'class' => $classes])); ?>><?php echo e($slot); ?></button>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /var/www/html/resources/views/components/button.blade.php ENDPATH**/ ?>