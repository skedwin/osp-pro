<?php if(isset($errors) && $errors->any()): ?>
    <div class="p-3 mb-4 border border-rose-100 bg-rose-50 rounded">
        <ul class="text-sm text-rose-700">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($err); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<?php if(!empty($reasons)): ?>
    <div class="p-3 mb-4 border border-amber-100 bg-amber-50 rounded text-sm text-amber-800">
        <strong>Cannot submit for renewal:</strong>
        <ul class="mt-2 list-disc list-inside">
            <?php $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($r); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
