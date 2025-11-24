<div class="mt-6 flex items-center justify-between">
    <div>
        @if(!empty($reasons))
            <div class="text-sm text-rose-600">You cannot submit: {{ implode(' | ', $reasons) }}</div>
        @endif
    </div>

    <div>
        <button type="submit" class="btn btn-primary px-4 py-2 rounded" @if(($renewalStatus ?? '') !== 'Eligible') disabled @endif>
            Submit Renewal Application
        </button>
    </div>
</div>
