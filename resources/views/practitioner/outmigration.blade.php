@extends('layouts.app', ['title' => $title ?? 'Outmigration Application'])

@section('content')
@php
    $bioProfile = session('bio_profile', []) ?: ($bioProfile ?? []);
    $profileSummary = data_get($bioProfile, 'profile', []);
    $fullName = data_get($profileSummary, 'Name') ?? data_get($profileSummary, 'FullName');
    $indexNo = data_get($profileSummary, 'IndexNo') ?? data_get($profileSummary, 'id');
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 lg:p-8 shadow-sm space-y-6">
    <header class="rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500 p-6 text-black shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-widest opacity-90 font-bold">International Opportunities</p>
                <h1 class="text-3xl font-bold">Outmigration Application</h1>
                <p class="text-sm lg:text-base mt-2 max-w-2xl text-white/80">
                    Submit your request to work or study outside the country. We gather key professional details to verify your credentials and notify relevant authorities.
                </p>
            </div>
            <div class="flex flex-col gap-3">
                <div class="bg-white/10 rounded-2xl p-4 backdrop-blur border border-white/30 min-w-[220px]">
                    <p class="text-xs uppercase tracking-widest text-white/70">Practitioner</p>
                    <p class="text-lg font-semibold">{{ $fullName ?? 'Authenticated User' }}</p>
                    <p class="text-sm text-white/70">Index: {{ $indexNo ?? 'N/A' }}</p>
                </div>
                <a href="{{ route('practitioner.outmigration.invoices') }}"
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

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            {{ session('info') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold mb-2">Please review the highlighted fields:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('practitioner.outmigration.apply') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="outmigration_form">
        @csrf

        <section class="rounded-2xl border border-slate-200 p-6 bg-slate-50/70 space-y-6">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-white p-3 border border-slate-200">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zM6 20v-1a4 4 0 014-4h4a4 4 0 014 4v1"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Personal & Destination Details</h2>
                    <p class="text-sm text-slate-500">Tell us where you’re headed and your family background</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Destination Country <span class="text-red-500">*</span>
                    </label>
                    <select name="country_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                        <option value="">Select country</option>
                        @foreach(($countries ?? []) as $country)
                            @php
                                $cId = $country['id'] ?? $country['country_id'] ?? $country['CountryID'] ?? null;
                                $cName = $country['name'] ?? $country['Country'] ?? $country['country'] ?? null;
                            @endphp
                            @if($cId && $cName)
                                <option value="{{ $cId }}" {{ old('country_id') == $cId ? 'selected' : '' }}>
                                    {{ $cName }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('country_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Marital Status <span class="text-red-500">*</span>
                    </label>
                    <select name="marital_status" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                        <option value="">Select marital status</option>
                        @foreach(($maritalStatusOptions ?? []) as $status)
                            <option value="{{ $status['id'] }}" {{ old('marital_status') == $status['id'] ? 'selected' : '' }}>
                                {{ $status['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('marital_status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Dependants <span class="text-red-500">*</span>
                    </label>
                    <input type="number" min="0" name="dependants" value="{{ old('dependants', '0') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                    @error('dependants') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Application Date
                    </label>
                    <input type="datetime-local" name="application_date" value="{{ old('application_date', now()->format('Y-m-d\TH:i')) }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    <p class="text-xs text-slate-500 mt-1">Defaults to current date/time if left blank.</p>
                    @error('application_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                    
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 p-6 space-y-6">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-blue-50 p-3 border border-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Employment Information</h2>
                    <p class="text-sm text-slate-500">Details about your current role and work history</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Employment Status <span class="text-red-500">*</span>
                    </label>
                    <select name="employment_status" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                        <option value="">Select status</option>
                        @foreach(($employmentStatusOptions ?? []) as $status)
                            <option value="{{ $status['id'] }}" {{ old('employment_status') == $status['id'] ? 'selected' : '' }}>
                                {{ $status['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('employment_status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Current Employer <span class="text-red-500">*</span>
                    </label>
                    <select name="current_employer" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                        <option value="">Select employer</option>
                        @foreach(($employers ?? []) as $employer)
                            @php
                                $eId = $employer['id'] ?? $employer['employer_id'] ?? null;
                                $eName = $employer['employer'] ?? $employer['name'] ?? $employer['employer_name'] ?? null;
                            @endphp
                            @if($eId && $eName)
                                <option value="{{ $eId }}" {{ old('current_employer') == $eId ? 'selected' : '' }}>
                                    {{ $eName }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('current_employer') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Workstation Type <span class="text-red-500">*</span>
                    </label>
                    <select name="workstation_type" id="workstation_type"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                        <option value="">Select workstation type</option>
                        @foreach(($workstationTypes ?? []) as $type)
                            @php
                                $typeId = $type['id'] ?? $type['type_id'] ?? null;
                                $typeName = $type['type'] ?? $type['name'] ?? $type['type_name'] ?? null;
                            @endphp
                            @if($typeId && $typeName)
                                <option value="{{ $typeId }}" {{ old('workstation_type') == $typeId ? 'selected' : '' }}>
                                    {{ $typeName }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('workstation_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        County of Work (for workstation lookup)
                    </label>
                    <select name="county_id" id="county_id"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
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
                    <p class="text-xs text-slate-500 mt-1">Use this to filter workstations below.</p>
                </div>

                <div class="lg:col-span-2 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Workstation <span class="text-xs text-slate-500">(must select county first)</span>
                        </label>
                        <div class="relative workstation-select-container">
                            <select name="workstation_id" id="workstation_id"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                                <option value="">{{ old('workstation_id') ? 'Currently selected' : 'Select county first' }}</option>
                            </select>
                            <div id="workstation_spinner_out" class="hidden absolute right-3 top-3">
                                <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('workstation_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Workstation Name
                        </label>
                        <input type="text" name="workstation_name" id="workstation_name"
                               value="{{ old('workstation_name') }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                               placeholder="Auto-filled when selecting workstation">
                        @error('workstation_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Department
                    </label>
                    <input type="text" name="department" value="{{ old('department') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    @error('department') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Current Position
                    </label>
                    <input type="text" name="current_position"
                           value="{{ old('current_position') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    @error('current_position') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Years of Experience
                    </label>
                    <input type="number" min="0" name="experience_years"
                           value="{{ old('experience_years') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    @error('experience_years') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Duration with Current Employer (years)
                    </label>
                    <input type="number" min="0" name="duration_current_employer"
                           value="{{ old('duration_current_employer') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    @error('duration_current_employer') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Do you plan to return? <span class="text-red-500">*</span>
                    </label>
                    <select name="planning_return" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                        <option value="">Select response</option>
                        @foreach(($planningReturnOptions ?? []) as $option)
                            <option value="{{ $option['id'] }}" {{ old('planning_return') == $option['id'] ? 'selected' : '' }}>
                                {{ $option['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('planning_return') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 p-6 space-y-6 bg-gradient-to-br from-slate-50 to-slate-100">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-white p-3 border border-slate-200">
                    <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-7H3v7a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Documentation & Verification</h2>
                    <p class="text-sm text-slate-500">Attach supporting documents and list cadres to verify</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Outmigration Reason <span class="text-red-500">*</span>
                    </label>
                    <select name="outmigration_reason" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                        <option value="">Select reason</option>
                        @foreach(($outmigrationReasonOptions ?? []) as $reason)
                            <option value="{{ $reason['id'] }}" {{ old('outmigration_reason') == $reason['id'] ? 'selected' : '' }}>
                                {{ $reason['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('outmigration_reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Verification Cadres <span class="text-xs text-slate-500">(comma separated index numbers)</span>
                    </label>
                    <input type="text" name="verification_cadres"
                           value="{{ old('verification_cadres') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                           placeholder="Example: 41898,9271,3068">
                    @error('verification_cadres') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Attach Supporting Document (PDF, DOC, JPG) – max 10MB
                </label>
                <input type="file" name="form_attached"
                       class="w-full rounded-xl border border-dashed border-slate-300 px-4 py-6 text-sm bg-white cursor-pointer">
                @error('form_attached') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 space-y-2">
                <p class="font-semibold">Declaration</p>
                <p>
                    By submitting this application, I confirm the information provided is accurate and that I will
                    comply with Nursing Council of Kenya’s verification process. Misrepresentation can lead to disciplinary action.
                </p>
            </div>
        </section>

        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            <div class="text-sm text-slate-500">
                Need help? Email <span class="text-slate-700 font-semibold">support@nckenya.go.ke</span> or call <span class="font-semibold">+254 20 </span>.
            </div>
            <button type="submit"
                    class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600"
                    id="outmigration_submit_btn">
                <svg id="outmigration_submit_spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span id="outmigration_submit_text">Submit Outmigration Application</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('outmigration_form');
        const submitBtn = document.getElementById('outmigration_submit_btn');
        const spinner = document.getElementById('outmigration_submit_spinner');
        const submitText = document.getElementById('outmigration_submit_text');
        const countySelect = document.getElementById('county_id');
        const workstationSelect = document.getElementById('workstation_id');
        const workstationNameInput = document.getElementById('workstation_name');
        const spinnerEl = document.getElementById('workstation_spinner_out');

        const toggleSpinner = (show) => {
            if (!spinnerEl) return;
            spinnerEl.classList.toggle('hidden', !show);
        };

        const populateWorkstations = (items) => {
            if (!workstationSelect) return;
            workstationSelect.innerHTML = '<option value="">Select workstation</option>';
            if (!Array.isArray(items) || !items.length) {
                return;
            }
            const oldWorkstationId = "{{ old('workstation_id') }}";
            items.forEach(item => {
                const wId = item.id ?? item.workstation_id ?? null;
                const wName = item.workstation ?? item.name ?? item.workstation_name ?? null;
                if (!wId || !wName) return;
                const option = document.createElement('option');
                option.value = wId;
                option.textContent = wName;
                if (oldWorkstationId === String(wId)) {
                    option.selected = true;
                    workstationNameInput.value = wName;
                }
                workstationSelect.appendChild(option);
            });
        };

        const loadWorkstations = (countyId) => {
            if (!countyId) {
                populateWorkstations([]);
                return;
            }
            toggleSpinner(true);
            const workstationsUrl = '{{ route("practitioner.renewals.workstations") }}';
            fetch(workstationsUrl + '?county_id=' + encodeURIComponent(countyId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        populateWorkstations(data.workstations || []);
                    } else {
                        throw new Error(data.error || 'Failed to load workstations');
                    }
                })
                .catch(err => {
                    console.error('Workstation load failed', err);
                    alert('Could not load workstations for the selected county. Please try again.');
                })
                .finally(() => toggleSpinner(false));
        };

        if (countySelect) {
            countySelect.addEventListener('change', (event) => {
                workstationNameInput.value = '';
                loadWorkstations(event.target.value);
            });

            const oldCountyId = "{{ old('county_id') }}";
            if (oldCountyId) {
                loadWorkstations(oldCountyId);
            }
        }

        if (workstationSelect && workstationNameInput) {
            workstationSelect.addEventListener('change', (event) => {
                const selectedText = event.target.options[event.target.selectedIndex]?.text || '';
                workstationNameInput.value = selectedText;
            });
        }

        if (form && submitBtn && spinner && submitText) {
            form.addEventListener('submit', () => {
                submitBtn.disabled = true;
                spinner.classList.remove('hidden');
                submitText.textContent = 'Submitting...';
            });
        }
    });
</script>
@endpush

