<footer class="bg-ink-900 text-ink-300 mt-24">
    <div class="max-w-content mx-auto px-6 py-16">
        <div class="grid gap-10 md:grid-cols-4">
            <div class="md:col-span-1">
                <p class="font-display text-xl font-semibold text-white">Growza</p>
                <p class="mt-2 text-sm text-ink-400 max-w-xs"><?php echo e(config('growza-marketing.company.tagline')); ?></p>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-white mb-3">Platform</h2>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo e(route('services')); ?>" class="hover:text-white">Services</a></li>
                    <li><a href="<?php echo e(route('pricing')); ?>" class="hover:text-white">Pricing</a></li>
                    <li><a href="<?php echo e(route('how-it-works')); ?>" class="hover:text-white">How it works</a></li>
                    <li><a href="<?php echo e(route('why-growza')); ?>" class="hover:text-white">Why Growza</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-white mb-3">Support</h2>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo e(route('faq')); ?>" class="hover:text-white">FAQ</a></li>
                    <li><a href="<?php echo e(route('contact')); ?>" class="hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-white mb-3">Legal</h2>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo e(route('legal.terms')); ?>" class="hover:text-white">Terms of Service</a></li>
                    <li><a href="<?php echo e(route('legal.privacy')); ?>" class="hover:text-white">Privacy Policy</a></li>
                    <li><a href="<?php echo e(route('legal.refund-policy')); ?>" class="hover:text-white">Refund Policy</a></li>
                    <li><a href="<?php echo e(route('legal.acceptable-use')); ?>" class="hover:text-white">Acceptable Use</a></li>
                    <li><a href="<?php echo e(route('legal.cookie-policy')); ?>" class="hover:text-white">Cookie Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-ink-800 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between text-sm text-ink-400">
            <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('growza-marketing.company.name')); ?>. All rights reserved.</p>
            <p>Growza does not provide artificial engagement or any service that breaches platform terms.</p>
        </div>
    </div>
</footer>
<?php /**PATH C:\Users\DELL\growza\growza\resources\views/components/footer.blade.php ENDPATH**/ ?>