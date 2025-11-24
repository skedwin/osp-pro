@props(['registrationItems'])

<div class="p-5 mb-6 border border-slate-200 rounded-2xl lg:p-6">
    <h4 class="text-lg font-semibold text-slate-800 lg:mb-6">
        Registration Records
    </h4>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7">
        @foreach ($registrationItems as $item)
            <div class="p-4 border border-slate-200 rounded-xl">
                <p class="text-sm font-semibold text-slate-800 mb-2">
                    {{ data_get($item, 'cadre_text', '—') }}
                </p>
                <p class="text-xs text-slate-500 mb-1">
                    Reg. No: {{ data_get($item, 'reg_no', '—') }}
                </p>
                <p class="text-xs text-slate-500">
                    Cadre: {{ data_get($item, 'cadre', '—') }}
                </p>
            </div>
        @endforeach
    </div>

</div>