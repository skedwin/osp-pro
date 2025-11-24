<?php
    $inv = $inv ?? ($invoice ?? []);
    $action = $inv['pesaflow_url'] ?? $inv['pesaflow'] ?? '';
?>

<div class="rounded-lg border p-4 bg-white">
    <h3 class="text-lg font-semibold mb-3">Payment</h3>

    <form id="pesaflow_form_details" method="POST" action="<?php echo e($action); ?>" target="pesaflow_iframe_details">
        
        <?php $__currentLoopData = [ 'apiClientID','serviceID','billRefNumber','amount','clientMSISDN','clientEmail','clientName','clientIDNumber','secureHash','currency','notificationURL','callBackURLOnSuccess','pictureURL','billDesc']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(isset($inv[$f]) || isset($inv[\Illuminate\Support\Str::snake($f)])): ?>
                <input type="hidden" name="<?php echo e($f); ?>" value="<?php echo e($inv[$f] ?? $inv[\Illuminate\Support\Str::snake($f)] ?? ''); ?>" />
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="text-sm text-slate-600 mb-3">Click the button below to open the payment interface.</div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('pesaflow_form_details').submit();" class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600">Click here to pay</button>
    
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        try {
            const form = document.getElementById('pesaflow_form_details');
            const btn = form ? form.querySelector('button[type="button"]') : null;
            if (!btn || !form) return;

            btn.addEventListener('click', function(e){
                // defensive logging for debugging
                console.log('pesaflow: open button clicked');
                const action = (form.getAttribute('action') || '').trim();
                // If there's no action or action points to current page, warn and abort
                if (!action || action === window.location.href) {
                    // Try to surface a helpful message rather than silently failing
                    alert('Payment URL is not available. Please refresh the page or contact support.');
                    return;
                }

                // show loading overlay if present
                const overlay = document.getElementById('pesaflow_loading_details');
                if (overlay) overlay.style.display = 'flex';

                // submit the form into the iframe target
                try {
                    form.submit();
                } catch (err) {
                    console.error('pesaflow: submit failed', err);
                    if (overlay) overlay.style.display = 'none';
                    alert('Failed to open payment interface.');
                }
            });
        } catch (e) {
            console.error('pesaflow init error', e);
        }
    });
</script>
