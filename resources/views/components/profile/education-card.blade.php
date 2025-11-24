@props(['educationItems'])

<div class="p-5 mb-6 border border-slate-200 rounded-2xl lg:p-6">
    <h4 class="text-lg font-semibold text-slate-800 lg:mb-6">
        Education History
    </h4>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7">
        @foreach ($educationItems as $item)
            <div class="p-4 border border-slate-200 rounded-xl">
                <p class="text-sm font-semibold text-slate-800 mb-2">
                    {{ data_get($item, 'cadre_text', '—') }}
                </p>
                <p class="text-xs text-slate-500 mb-1">
                    Institution: {{ data_get($item, 'institution', '—') }}
                </p>
                <p class="text-xs text-slate-500">
                    Admission: {{ data_get($item, 'admission_date', '—') }}
                </p>
            </div>
        @endforeach
    </div>
</div>