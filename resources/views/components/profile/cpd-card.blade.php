<div class="rounded-2xl border border-slate-200 bg-white p-5 lg:p-6">
    <h3 class="text-lg font-semibold text-slate-800 mb-4">CPD Information</h3>

    <ul class="divide-y divide-slate-200">
        @foreach ($cpdItems as $cpd)
            <li class="py-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">CPD Requirement:</span>
                    <span class="text-sm text-slate-800">{{ $cpd['cpd_requirement'] }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-sm font-medium text-slate-600">Current Points:</span>
                    <span class="text-sm text-slate-800">{{ $cpd['current_points'] }}</span>
                </div>
            </li>
        @endforeach
    </ul>
</div>