<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm text-slate-600">License / PIN</label>
        <input type="text" name="license_number" value="<?php echo e(old('license_number', data_get($bioProfile, 'license_number') ?? '')); ?>" class="mt-2 w-full border rounded px-3 py-2" />
    </div>

    <div>
        <label class="block text-sm text-slate-600">County</label>
        <select id="county_id" name="county_id" class="mt-2 w-full border rounded px-3 py-2">
            <option value="">Select County</option>
            <?php $__currentLoopData = $counties ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $cid = data_get($c, 'id'); $cname = data_get($c, 'name'); ?>
                <option value="<?php echo e($cid); ?>" <?php echo e((old('county_id') == $cid) ? 'selected' : ''); ?>><?php echo e($cname); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div>
        <label class="block text-sm text-slate-600">Workstation</label>
        <select id="workstation_id" name="workstation_id" class="mt-2 w-full border rounded px-3 py-2">
            <option value="">Select Workstation</option>
        </select>
    </div>

    <div>
        <label class="block text-sm text-slate-600">Workstation name (optional)</label>
        <input id="workstation_name" type="text" name="workstation_name" value="<?php echo e(old('workstation_name', '')); ?>" class="mt-2 w-full border rounded px-3 py-2" />
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-slate-600">Notes (optional)</label>
        <textarea name="notes" class="mt-2 w-full border rounded px-3 py-2" rows="3"><?php echo e(old('notes')); ?></textarea>
    </div>
</div>
