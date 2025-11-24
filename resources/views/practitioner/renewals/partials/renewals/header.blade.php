<div class="flex items-start justify-between">
    <div>
        <h2 class="text-2xl font-semibold text-slate-800">License Renewal</h2>
        <p class="text-sm text-slate-500">Expiry: <?php echo e(isset($expiryDate) ? $expiryDate->format('d M Y') : 'Unknown'); ?></p>
        <?php if(isset($noExpiryOnRecord) && $noExpiryOnRecord): ?>
            <p class="text-xs text-amber-600">No expiry date found on your profile. Please confirm your license details before applying.</p>
        <?php endif; ?>
    </div>

    <div class="text-right">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-sm <?php echo e($statusBg ?? 'bg-gray-50'); ?> <?php echo e($statusText ?? 'text-gray-700'); ?> <?php echo e($statusBorder ?? 'border-gray-200'); ?>">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v4h2V7zm0 6H9v2h2v-2z"/></svg>
            <strong><?php echo e($renewalStatus ?? 'Unknown'); ?></strong>
        </span>
    </div>
</div>

<hr class="my-4" />
