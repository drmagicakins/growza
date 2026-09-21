<?php if (isset($component)) { $__componentOriginal50bf70515bc668868963a2292f9e0961 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal50bf70515bc668868963a2292f9e0961 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.errors.layout','data' => ['code' => '404','title' => 'We couldn\'t find that page','message' => 'The page may have moved, or the link that brought you here may be out of date. Nothing on your account has changed.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('errors.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['code' => '404','title' => 'We couldn\'t find that page','message' => 'The page may have moved, or the link that brought you here may be out of date. Nothing on your account has changed.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal50bf70515bc668868963a2292f9e0961)): ?>
<?php $attributes = $__attributesOriginal50bf70515bc668868963a2292f9e0961; ?>
<?php unset($__attributesOriginal50bf70515bc668868963a2292f9e0961); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal50bf70515bc668868963a2292f9e0961)): ?>
<?php $component = $__componentOriginal50bf70515bc668868963a2292f9e0961; ?>
<?php unset($__componentOriginal50bf70515bc668868963a2292f9e0961); ?>
<?php endif; ?>
<?php /**PATH C:\Users\DELL\growza\growza\resources\views/errors/404.blade.php ENDPATH**/ ?>