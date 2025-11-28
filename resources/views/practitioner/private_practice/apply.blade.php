@extends('layouts.app', ['title' => 'Private Practice Application'])

@section('content')
@php
    $bioProfile = session('bio_profile', []) ?: ($bioProfile ?? []);
    $profileSummary = data_get($bioProfile, 'profile', []);
    $fullName = data_get($profileSummary, 'Name') ?? data_get($profileSummary, 'FullName');
    $indexNo = data_get($profileSummary, 'IndexNo');
    $indexId = data_get($profileSummary, 'id');
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 lg:p-8 shadow-sm space-y-6">
    <header class="rounded-2xl bg-gradient-to-br from-indigo-500 via-blue-500 to-cyan-500 p-6 text-black shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-widest opacity-90 font-bold">Practice Setup</p>
                <h1 class="text-3xl font-bold">Private Practice Renewal Application</h1>
                <p class="text-sm lg:text-base mt-2 max-w-2xl text-white/80">
                    Apply for or renew your private practice license.
                </p>
            </div>
            <div class="flex flex-col gap-3">
                <div class="bg-white/10 rounded-2xl p-4 backdrop-blur border border-white/30 min-w-[220px]">
                    <p class="text-xs uppercase tracking-widest text-white/70">Practitioner</p>
                    <p class="text-lg font-semibold">{{ $fullName ?? 'Authenticated User' }}</p>
                    <p class="text-sm text-white/70">Index: {{ $indexNo ?? 'N/A' }}</p>
                </div>
                <a href="{{ route('practitioner.private-practice.invoices') }}"
                   class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    My Invoices
                </a>
            </div>
        </div>
        <div class="absolute inset-y-0 right-0 w-48 bg-white/10 blur-3xl translate-x-1/3"></div>
    </header>

    <form action="{{ route('practitioner.private-practice.apply') }}" method="POST" class="space-y-8" id="pp_form">
        @csrf
        
        {{-- Hidden field to explicitly track index_id for debugging --}}
        <input type="hidden" name="index_id_debug" value="{{ $indexId ?? '' }}">
        <input type="hidden" name="index_id" value="{{ $indexId ?? '' }}">
        
        @if(session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 p-6 bg-slate-50/70 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Renewal Date</label>
                    <input type="datetime-local" name="renewal_date" value="{{ old('renewal_date', now()->format('Y-m-d\TH:i')) }}"
                           readonly
                           class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-600 cursor-not-allowed @error('renewal_date') border-red-500 @enderror">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Proposed Practice <span class="text-red-500">*</span></label>
                    <select name="proposed_practice_id" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm @error('proposed_practice_id') border-red-500 @enderror">
                        <option value="">Select proposed practice</option>
                        @foreach(($proposedPractices ?? []) as $pp)
                            <option value="{{ $pp['id'] }}" {{ old('proposed_practice_id') == ($pp['id'] ?? null) ? 'selected' : '' }}>
                                {{ $pp['name'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('proposed_practice_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Practice Mode <span class="text-red-500">*</span></label>
                    <select name="practice_mode_id" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm @error('practice_mode_id') border-red-500 @enderror">
                        <option value="">Select practice mode</option>
                        @foreach(($practiceModes ?? []) as $pm)
                            <option value="{{ $pm['id'] }}" {{ old('practice_mode_id') == ($pm['id'] ?? null) ? 'selected' : '' }}>
                                {{ $pm['name'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('practice_mode_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">County <span class="text-red-500">*</span></label>
                    <select name="county_id" id="pp_county_id" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm @error('county_id') border-red-500 @enderror">
                        <option value="">Select county</option>
                        @foreach(($counties ?? []) as $county)
                            @php
                                $countyId = $county['id'] ?? $county['county_id'] ?? null;
                                $countyName = $county['County'] ?? $county['county'] ?? $county['name'] ?? null;
                            @endphp
                            @if($countyId && $countyName)
                                <option value="{{ $countyId }}" {{ old('county_id') == $countyId ? 'selected' : '' }}>
                                    {{ $countyName }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Town</label>
                    <input type="text" name="town" value="{{ old('town') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Workstation</label>
                    <div class="relative">
                        <select name="workstation_id" id="pp_workstation_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                            <option value="">Select county first</option>
                        </select>
                        <div id="pp_workstation_spinner" class="hidden absolute right-3 top-3">
                            <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Workstation Name</label>
                    <input type="text" name="workstation_name" id="pp_workstation_name" value="{{ old('workstation_name') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="Auto-filled when selecting workstation">
                </div>
            </div>
        </section>

        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            <div class="text-sm text-slate-500">
                Need help? Email <span class="text-slate-700 font-semibold">support@nckenya.go.ke</span>.
            </div>
            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 font-semibold text-white rounded-lg bg-brand-500 text-sm hover:bg-brand-600" id="pp_submit_btn">
                <svg id="pp_submit_spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span id="pp_submit_text">Submit Private Practice Application</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const countySelect = document.getElementById('pp_county_id');
    const workstationSelect = document.getElementById('pp_workstation_id');
    const workstationNameInput = document.getElementById('pp_workstation_name');
    const spinnerEl = document.getElementById('pp_workstation_spinner');
    const submitBtn = document.getElementById('pp_submit_btn');
    const spinner = document.getElementById('pp_submit_spinner');
    const submitText = document.getElementById('pp_submit_text');

    const toggleSpinner = (show) => spinnerEl && spinnerEl.classList.toggle('hidden', !show);

    const loadWorkstations = (countyId) => {
        if (!countyId) { workstationSelect.innerHTML = '<option value="">Select county first</option>'; return; }
        toggleSpinner(true);
        const wsUrl = "{{ route('practitioner.renewals.workstations') }}";
        fetch(wsUrl + '?county_id=' + encodeURIComponent(countyId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                workstationSelect.innerHTML = '<option value="">Select workstation</option>';
                const list = data.success ? (data.workstations || []) : [];
                list.forEach(w => {
                    const id = w.id ?? w.workstation_id;
                    const name = w.workstation ?? w.name ?? w.workstation_name;
                    if (id && name) {
                        const opt = document.createElement('option'); opt.value = id; opt.textContent = name; workstationSelect.appendChild(opt);
                    }
                });
            })
            .catch(err => { console.error('Workstations load error', err); alert('Could not load workstations.'); })
            .finally(() => toggleSpinner(false));
    };

    countySelect?.addEventListener('change', e => { workstationNameInput.value = ''; loadWorkstations(e.target.value); });
    workstationSelect?.addEventListener('change', e => { const txt = e.target.options[e.target.selectedIndex]?.text || ''; workstationNameInput.value = txt; });

    const form = document.getElementById('pp_form');
    form?.addEventListener('submit', () => { submitBtn.disabled = true; spinner.classList.remove('hidden'); submitText.textContent = 'Submitting...'; });
});
</script>
@endpush

@endsection
