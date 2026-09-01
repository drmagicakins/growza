<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $__env->yieldContent('title', 'Growza — Grow Smarter. Reach Further.'); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body>
    <?php echo $__env->yieldContent('content'); ?>
</body>
</html><?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>