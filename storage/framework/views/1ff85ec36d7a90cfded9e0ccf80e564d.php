<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
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
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $siteName = config('growza-marketing.company.name', 'Growza');
    $fullTitle = $title ? $title . ' — ' . $siteName : $siteName . ' — ' . config('growza-marketing.company.tagline');
    $canonical = url()->current();
    $ogImage = $image ?: asset('images/growza-og.png');
?>

<title><?php echo e($fullTitle); ?></title>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
    <meta name="description" content="<?php echo e($description); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<link rel="canonical" href="<?php echo e($canonical); ?>">


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($noindex || ! config('app.allow_indexing')): ?>
    <meta name="robots" content="noindex, nofollow">
<?php else: ?>
    <meta name="robots" content="index, follow">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<meta property="og:site_name" content="<?php echo e($siteName); ?>">
<meta property="og:type" content="<?php echo e($type); ?>">
<meta property="og:title" content="<?php echo e($fullTitle); ?>">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
    <meta property="og:description" content="<?php echo e($description); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<meta property="og:url" content="<?php echo e($canonical); ?>">
<meta property="og:image" content="<?php echo e($ogImage); ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo e($fullTitle); ?>">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
    <meta name="twitter:description" content="<?php echo e($description); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<meta name="twitter:image" content="<?php echo e($ogImage); ?>">

<?php echo e($slot ?? ''); ?>

<?php /**PATH C:\Users\DELL\growza\growza\resources\views/components/seo.blade.php ENDPATH**/ ?>