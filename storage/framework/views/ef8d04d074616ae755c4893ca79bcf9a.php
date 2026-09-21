<?php if (isset($component)) { $__componentOriginalf103f87f9e6975b672a2453f5462c100 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf103f87f9e6975b672a2453f5462c100 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.marketing','data' => ['seoTitle' => 'Services','seoDescription' => 'Paid social campaigns, content strategy, music promotion, creator partnerships, search visibility and campaign analytics — delivered through legitimate channels.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.marketing'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['seo-title' => 'Services','seo-description' => 'Paid social campaigns, content strategy, music promotion, creator partnerships, search visibility and campaign analytics — delivered through legitimate channels.']); ?>

    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['eyebrow' => 'Services','title' => 'Campaign work, scoped and measured','subtitle' => 'Six service lines. Each one starts from a defined objective and reports against it.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['eyebrow' => 'Services','title' => 'Campaign work, scoped and measured','subtitle' => 'Six service lines. Each one starts from a defined objective and reports against it.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

    <div class="max-w-content mx-auto px-6 py-20 space-y-16">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('growza-marketing.services'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section class="grid gap-8 md:grid-cols-3 border-b border-ink-200 pb-16 last:border-0">
                <div class="md:col-span-1">
                    <h2 class="text-display-sm font-display"><?php echo e($service['name']); ?></h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($service['platforms'])): ?>
                        <div class="mt-4 flex flex-wrap gap-1.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $service['platforms']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $platform): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'neutral']); ?><?php echo e($platform); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="md:col-span-2 max-w-prose">
                    <p class="text-lg text-ink-700"><?php echo e($service['summary']); ?></p>
                    <p class="mt-4 text-ink-600"><?php echo e($service['detail']); ?></p>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if (isset($component)) { $__componentOriginalb9eda212fea7921b4a03feb1b01f47d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb9eda212fea7921b4a03feb1b01f47d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.cta-band','data' => ['title' => 'Not sure which of these you need?','description' => 'Describe the outcome you are chasing and we will tell you which service actually fits — including if the answer is none of them.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('cta-band'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Not sure which of these you need?','description' => 'Describe the outcome you are chasing and we will tell you which service actually fits — including if the answer is none of them.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb9eda212fea7921b4a03feb1b01f47d7)): ?>
<?php $attributes = $__attributesOriginalb9eda212fea7921b4a03feb1b01f47d7; ?>
<?php unset($__attributesOriginalb9eda212fea7921b4a03feb1b01f47d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb9eda212fea7921b4a03feb1b01f47d7)): ?>
<?php $component = $__componentOriginalb9eda212fea7921b4a03feb1b01f47d7; ?>
<?php unset($__componentOriginalb9eda212fea7921b4a03feb1b01f47d7); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf103f87f9e6975b672a2453f5462c100)): ?>
<?php $attributes = $__attributesOriginalf103f87f9e6975b672a2453f5462c100; ?>
<?php unset($__attributesOriginalf103f87f9e6975b672a2453f5462c100); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf103f87f9e6975b672a2453f5462c100)): ?>
<?php $component = $__componentOriginalf103f87f9e6975b672a2453f5462c100; ?>
<?php unset($__componentOriginalf103f87f9e6975b672a2453f5462c100); ?>
<?php endif; ?>
<?php /**PATH C:\Users\DELL\growza\growza\resources\views/marketing/services.blade.php ENDPATH**/ ?>