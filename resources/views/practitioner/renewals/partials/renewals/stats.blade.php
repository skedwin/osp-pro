<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="p-4 border rounded-lg bg-white">
        <div class="text-xs text-slate-500">CPD Points</div>
        <div class="text-lg font-semibold text-slate-800"><?php echo e(number_format($cpdTotal ?? 0, 1)); ?></div>
        <div class="text-xs text-slate-400">Required: <?php echo e(number_format($requiredCpd ?? 20, 1)); ?></div>
    </div>

    <div class="p-4 border rounded-lg bg-white">
        <div class="text-xs text-slate-500">Days until expiry</div>
        <div class="text-lg font-semibold text-slate-800"><?php echo e($daysUntilExpiry); ?></div>
        <div class="text-xs text-slate-400"><?php echo e(isset($expiryDate) ? $expiryDate->format('d M Y') : 'Unknown'); ?></div>
    </div>

    <div class="p-4 border rounded-lg bg-white">
        <div class="text-xs text-slate-500">Eligibility</div>
        <div class="text-lg font-semibold text-slate-800"><?php echo e($renewalStatus ?? 'Unknown'); ?></div>
        <?php if(!empty($reasons)): ?>
            <div class="text-xs text-rose-600 mt-1"><?php echo e(implode(' ', $reasons)); ?></div>
        <?php endif; ?>
    </div>
</div>
