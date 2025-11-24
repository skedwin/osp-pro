<div class="flex items-center justify-between mb-4">
    <div>
        <h2 class="text-2xl font-semibold text-slate-800">Invoices & Payments</h2>
        <p class="text-sm text-slate-500">Manage your renewal invoices and payments</p>
    </div>

    <div class="text-right">
        <?php if(isset($invoice) && $invoice): ?>
            <div class="text-sm text-slate-600">Invoice loaded: <strong><?php echo e($invoice['invoice_number'] ?? $invoice['billRefNumber'] ?? 'N/A'); ?></strong></div>
        <?php endif; ?>
    </div>
</div>
