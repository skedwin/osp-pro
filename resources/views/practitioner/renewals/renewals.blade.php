@extends('layouts.app', ['title' => $title])

@section('content')
@php
    use Carbon\Carbon;

    // Load profile bundle (may be available in session or passed from controller)
    $bioProfile = session('bio_profile', []) ?: ($bioProfile ?? []);
    $licenseItems = data_get($bioProfile, 'license', []) ?: [];
    $cpdItems = data_get($bioProfile, 'cpd', []) ?: [];

    // Use controller-provided $cpdHistory if available (matches CPD index page)

    // Determine the primary expiry date (prefer to_date from license array)
    $expiryDate = null;
    foreach ($licenseItems as $lic) {
        $raw = data_get($lic, 'to_date') ?? data_get($lic, 'expiry_date') ?? data_get($lic, 'ExpiryDate') ?? data_get($lic, 'ExpirationDate') ?? data_get($lic, 'ExpiresOn') ?? data_get($lic, 'Expiry') ?? data_get($lic, 'expiry');
        if (!$raw) continue;
        try {
            $d = Carbon::parse($raw);
        } catch (\Throwable $e) {
            continue;
        }
        if (!$expiryDate || $d->greaterThan($expiryDate)) {
            $expiryDate = $d;
        }
    }

    // Fallback: try common single-field keys or set a distant date
    if (!$expiryDate) {
        $raw = data_get($bioProfile, 'license_expiry') ?? data_get($bioProfile, 'expiry_date') ?? data_get($bioProfile, 'expires_at');
        try { $expiryDate = $raw ? Carbon::parse($raw) : null; } catch(\Throwable $e) { $expiryDate = null; }
    }
    if (!$expiryDate) {
        // If we don't know expiry, set to one year from now so daysUntilExpiry is large
        $expiryDate = Carbon::now()->addYear();
        $noExpiryOnRecord = true;
    } else {
        $noExpiryOnRecord = false;
    }

    $today = Carbon::now();
    // Calculate days until expiry as integer (floor)
    $daysUntilExpiry = $expiryDate ? $today->diffInDays($expiryDate, false) : 0;
    $daysUntilExpiry = intval(floor($daysUntilExpiry));

    // Renewal window: within 60 days before expiry or up to 60 days after expiry
    $withinWindow = (abs($daysUntilExpiry) <= 60);

    // CPD totals and requirements are provided by the controller (derived from
    // backend `bio_profile.cpd`). Use the values passed from the controller so
    // the view remains minimal. Controller should provide $cpdTotal and
    // $requiredCpd; default conservatively if missing.
    $cpdTotal = isset($cpdTotal) ? floatval($cpdTotal) : 0.0;
    $requiredCpd = isset($requiredCpd) ? floatval($requiredCpd) : 20.0;

    $eligibleWindow = ($daysUntilExpiry <= 0 || ($daysUntilExpiry > 0 && $daysUntilExpiry <= 60));
    $hasCpd = ($cpdTotal >= $requiredCpd);

    $reasons = [];
    if (!$hasCpd) {
        $reasons[] = "Insufficient CPD for the current year: {$cpdTotal} of {$requiredCpd} required.";
    }
    if (!$eligibleWindow) {
        if ($daysUntilExpiry > 60) {
            $reasons[] = "License not within 60 day renewal window (expires in {$daysUntilExpiry} days).";
        } else {
            $reasons[] = "License expired more than 60 days ago (expired on {$expiryDate->format('d M Y')}).";
        }
    }

    if ($eligibleWindow && $hasCpd) {
        $renewalStatus = 'Eligible';
        $statusColor = 'green';
    } else {
        $renewalStatus = 'Not Eligible';
        $statusColor = 'red';
    }

    $statusBg = match($statusColor) {
        'green' => 'bg-green-50',
        'amber' => 'bg-amber-50',
        'blue' => 'bg-blue-50',
        'red' => 'bg-red-50',
        default => 'bg-gray-50'
    };
    $statusText = match($statusColor) {
        'green' => 'text-green-700',
        'amber' => 'text-amber-700',
        'blue' => 'text-blue-700',
        'red' => 'text-red-700',
        default => 'text-gray-700'
    };
    $statusBorder = match($statusColor) {
        'green' => 'border-green-200',
        'amber' => 'border-amber-200',
        'blue' => 'border-blue-200',
        'red' => 'border-red-200',
        default => 'border-gray-200'
    };
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 lg:p-6">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 p-6 lg:p-8 shadow-lg">
            <div class="relative z-10">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-black">
                        <h1 class="text-2xl font-bold lg:text-3xl mb-2">
                            License Renewal Application
                        </h1>
                        <p class="text-blue-100 text-sm lg:text-base">
                            Renew your professional license and continue practicing without interruption
                        </p>    
                    </div>
                    
                        

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('practitioner.invoices') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 rounded-xl hover:shadow-lg active:scale-95 whitespace-nowrap mr-2" style="background-color: #2563eb; border: 1px solid #1d4ed8;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                Go to Invoices
                            </a>
                        <button
                            type="button"
                            onclick="document.getElementById('renewal-form').scrollIntoView({ behavior: 'smooth' })"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 rounded-xl hover:shadow-lg active:scale-95 whitespace-nowrap"
                            style="background-color: #2563eb; border: 1px solid #1d4ed8;"
                            >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Apply Now
                        </button>
                    </div>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-400/20 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Renewal Status Card -->
            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:scale-[1.02]">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-600 mb-1">Renewal Status</p>
                        <p class="text-3xl font-bold {{ $statusText }} mb-2">
                            {{ $renewalStatus }}
                        </p>
                        <p class="text-xs text-slate-500 mb-2">Current License</p>
                        <p class="text-sm text-slate-600">Expiry: <strong>{{ isset($expiryDate) ? $expiryDate->format('d M Y') : 'N/A' }}</strong></p>
                        <p class="text-sm text-slate-600">CPD (this year): <strong>{{ number_format($cpdTotal, 1) }}</strong> of {{ $requiredCpd }}</p>
                        @if(!empty($reasons))
                            <div class="mt-3 p-2 rounded bg-red-50 border border-red-100">
                                <p class="text-sm font-semibold text-red-700 mb-1">Action required</p>
                                <ul class="text-sm text-red-700 list-disc list-inside">
                                    @foreach($reasons as $r)
                                        <li>{{ $r }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-green-700">Your application meets the basic eligibility checks.</p>
                        @endif
                    </div>
                    <div class="rounded-xl bg-blue-500/20 p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
            </div>

            <!-- Days Until Expiry Card -->
            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-green-50 to-emerald-100/50 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:scale-[1.02]">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-600 mb-1">Days Until Expiry</p>
                        <p class="text-3xl font-bold text-green-700 mb-2">
                            {{ $daysUntilExpiry }}
                        </p>
                        <p class="text-xs text-slate-500">Time Remaining</p>
                        
                        <div class="mt-3 p-2 rounded bg-red-50 border border-red-100">
                            <p class="mt-2 text-sm font-semibold {{ $daysUntilExpiry > 0 ? 'text-green-600' : 'text-red-600' }}">
                                License Status: {{ $daysUntilExpiry > 0 ? 'Active' : 'Expired' }}
                            </p>
                            <p class="text-sm {{ $daysUntilExpiry > 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ $daysUntilExpiry > 0 ? 'Your license is in good standing. Stay proactive and ensure timely renewals.' : 'Your license has expired. Please renew it as soon as possible to continue practicing.' }}
                            </p>
                        </div>
                    </div>
                    <div class="rounded-xl bg-green-500/20 p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 to-emerald-600"></div>
            </div>
        </div>

        <!-- Renewal Application Form Section -->
        <div id="renewal-form" class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">
                    License Renewal Application Form
                </h3>
                <p class="text-sm text-slate-500">Complete all required information to submit your license renewal application</p>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var msg = "{{ addslashes(session('success')) }}";
                        showToast(msg, 'success');
                    });
                </script>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm8-8a8 8 0 11-16 0 8 8 0 0116 0zM10 9a1 1 0 100 2 1 1 0 000-2zm0 4a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Error!</h3>
                            <div class="mt-1 text-sm text-red-700 dark:text-red-300">
                                <p>{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('practitioner.renewals.process') }}" method="POST" class="space-y-8">
                @csrf

                @if($errors->any())
                    <div class="mb-4 p-4 rounded-md bg-red-50 border border-red-200">
                        <h4 class="text-sm font-semibold text-red-800">Please fix the following errors:</h4>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($renewalStatus === 'Not Eligible')
                    <div class="mb-4 p-4 rounded-md bg-red-50 border border-red-200">
                        <h4 class="text-sm font-semibold text-red-800">You are not eligible to renew:</h4>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                            @foreach($reasons as $reason)
                                <li>{{ $reason }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Unified Practice Information Section -->
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100/50 rounded-2xl p-6 border border-slate-200">
                    <!-- Section Header -->
                    <div class="flex items-center gap-3 mb-6">
                        <div class="rounded-xl bg-blue-100 p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-800">Practice Information</h4>
                            <p class="text-sm text-slate-500">County, Employer & Workstation details combined</p>
                        </div>
                    </div>

                    <!-- Grid Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- County -->
                        <div>
                            <label for="county_id" class="block text-sm font-semibold text-slate-700 mb-3">
                                County of Practice <span class="text-red-500">*</span>
                            </label>
                            <select name="county_id" id="county_id"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm
                                    text-slate-700 transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100
                                    @error('county_id') border-red-500 @enderror"
                                required>
                                <option value="">Select County</option>
                                @foreach($counties ?? [] as $county)
                                    <option value="{{ $county['id'] ?? $county['county_id'] }}"
                                        {{ old('county_id') == ($county['id'] ?? $county['county_id']) ? 'selected' : '' }}>
                                        {{ $county['County'] ?? $county['county'] ?? $county['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('county_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Employer -->
                        <div>
                            <label for="employer_id" class="block text-sm font-semibold text-slate-700 mb-3">
                                Employer <span class="text-red-500">*</span>
                            </label>
                            <select name="employer_id" id="employer_id"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm
                                    text-slate-700 transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100
                                    @error('employer_id') border-red-500 @enderror"
                                required>
                                <option value="">Select Employer</option>
                                @foreach($employers ?? [] as $employer)
                                    <option value="{{ $employer['id'] ?? $employer['employer_id'] }}"
                                        {{ old('employer_id') == ($employer['id'] ?? $employer['employer_id']) ? 'selected' : '' }}>
                                        {{ $employer['employer'] ?? $employer['name'] ?? $employer['employer_name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employer_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Workstation -->
                        <div>
                            <label for="workstation_id" class="block text-sm font-semibold text-slate-700 mb-3">
                                Workstation <span class="text-red-400 text-xs"></span>
                            </label>
                            <div class="workstation-select-container relative">
                                <select name="workstation_id" id="workstation_id"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700
                                        focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('workstation_id') border-red-500 @enderror">
                                    <option value="">Select County First</option>
                                </select>

                                <!-- Small spinner shown when loading workstations -->
                                <div id="workstation_spinner" class="absolute right-3 top-3 hidden">
                                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-500">
                                Workstations appear after selecting a county
                            </p>
                            <!-- Workstation load error alert (hidden by default) -->
                            <div id="workstation_load_alert" class="mt-3 hidden p-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-700 flex items-start justify-between">
                                <div>
                                    <strong> wait | loading workstations.</strong>
                                    <div id="workstation_load_alert_msg" class="mt-1 text-sm text-red-700">Please try again or contact support.</div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <button id="workstation_load_alert_close" type="button" class="text-red-500 hover:text-red-700">&times;</button>
                                </div>
                            </div>
                            @error('workstation_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Workstation Type -->
                        <div>
                            <label for="workstation_type_id" class="block text-sm font-semibold text-slate-700 mb-3">
                                Workstation Type <span class="text-red-500">*</span>
                            </label>
                            <select name="workstation_type_id" id="workstation_type_id"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm
                                    text-slate-700 transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100
                                    @error('workstation_type_id') border-red-500 @enderror"
                                required>
                                <option value="">Select Workstation Type</option>
                                @foreach($workstationTypes ?? [] as $type)
                                    <option value="{{ $type['id'] ?? $type['type_id'] }}"
                                        {{ old('workstation_type_id') == ($type['id'] ?? $type['type_id']) ? 'selected' : '' }}>
                                        {{ $type['type'] ?? $type['name'] ?? $type['type_name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('workstation_type_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <!-- Workstation Name -->
                    <div class="mt-6">
                        <label for="workstation_name" class="block text-sm font-semibold text-slate-700 mb-3">
                            Workstation Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="workstation_name" id="workstation_name"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm
                                text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-100
                                @error('workstation_name') border-red-500 @enderror"
                            value="{{ old('workstation_name') }}"
                            placeholder="Auto-fills when you select a workstation, or enter manually">
                        @error('workstation_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>
                <!-- Declaration Section -->
                <div class="my-6 rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-6 shadow-sm">
                                <!-- Header -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="rounded-xl bg-amber-100 p-3 shadow-inner">
                        <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-amber-900">Declaration</h4>
                        <p class="text-sm text-amber-800">Important information about your renewal</p>
                    </div>
                </div>

                <!-- Body -->
                <div class="space-y-5">

                    <!-- Renewal date item -->
                    <div class="flex items-start gap-3">
                        <div class="mt-1">
                            <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>  

                        <div>
                            <p class="text-sm font-semibold text-amber-900 mb-1">Renewal Date</p>
                            <p class="text-sm text-amber-800 leading-relaxed">
                                Your renewal application will automatically capture the current date and time.
                            </p>
                        </div>
                    </div>

                    <!-- Workstation item -->
                    <div class="flex items-start gap-3">
                        <div class="mt-1">
                            <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>

                        <div>
                            <p class="text-sm font-semibold text-amber-900 mb-1">Workstation Assignment</p>
                            <p class="text-sm text-amber-800 leading-relaxed">
                                Your workstation ID will be automatically assigned based on the selected county.
                            </p>
                        </div>
                    </div>

                </div>
                <!-- your card content -->
                </div>            
                <!-- Action Buttons -->
                                        @if($renewalStatus === 'Eligible')
                                            <button type="submit" 
                                                    style="background-color: #2563eb"
                                                    class="flex items-center justify-center gap-3 p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600"
                                                    aria-label="Submit Renewal Application"
                                                    >
                                                <!-- Spinner shown while submitting -->
                                                <svg id="submit_spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                                <span id="submit_text">Submit Renewal Application</span>
                                            </button>
                                        @else
                                            <button type="button" disabled class="flex items-center justify-center gap-3 p-3 font-medium text-white rounded-lg bg-gray-300 cursor-not-allowed" aria-disabled="true">
                                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                                <span id="submit_text">Not Eligible</span>
                                            </button>
                                        @endif
            </form>
            <!-- Invoice / Payment Card (populated via AJAX) -->
            <div id="invoice_container" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 mb-2">Recent Invoice</h4>
                        <div id="invoice_details_area" class="text-sm text-slate-700">
                            <!-- Populated by JS -->
                            <p id="invoice_placeholder">Loading your recent applications...</p>
                        </div>
                    </div>
                    <div id="invoice_actions" class="text-right">
                        <!-- Populated by JS: total, balance, pay button -->
                    </div>
                </div>
                <!-- Pesaflow iframe modal -->
                <div id="pesaflow_modal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center">
                    <div class="bg-white rounded-none w-[95%] md:w-3/4 lg:w-[98%] lg:h-[96%] h-[90%] relative border-4 border-black">
                            <button id="pesaflow_close" class="absolute right-3 top-3 text-xl font-bold">&times;</button>
                            <iframe id="pesaflow_iframe" name="pesaflow_target" class="w-full h-full border-2 border-black rounded-b-lg" src="" title="Payment"></iframe>
                        </div>
                </div>
            </div>
        </div>

        {{-- CPD Information Card --}}
        @if (!empty($cpdItems))
            <x-profile.cpd-card :cpdItems="$cpdItems" />
        @else
            <x-profile.empty-state 
                icon="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"
                title="No CPD Information"
                description="CPD data will appear here when available." />
        @endif

        <!-- Important Notes Section -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 rounded-2xl bg-blue-100 p-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-bold text-slate-800 mb-3">Important Renewal Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-600">
                        <div class="space-y-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Ensure all CPD requirements are met before applying</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Renewal applications are processed within 30 minutes</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Keep your contact information updated for notifications</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Contact support if you encounter any issues via <b>support@nckenya.go.ke</b></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($renewalStatus === 'Not Eligible' && !empty($reasons))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('You are not eligible to renew: {{ addslashes(implode(' | ', $reasons)) }}', 'error', 12000);
    });
</script>
@endif
@endpush

@push('scripts')
<!-- Tom Select (CDN) - lightweight select replacement with remote loading support -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced select elements with better styling
        const enhanceSelect = (elementId) => {
            const element = document.getElementById(elementId);
            if (element) {
                // Add focus styles
                element.addEventListener('focus', function() {
                    this.parentElement.classList.add('ring-2', 'ring-blue-100', 'rounded-xl');
                });
                
                element.addEventListener('blur', function() {
                    this.parentElement.classList.remove('ring-2', 'ring-blue-100', 'rounded-xl');
                });

                // Add change validation
                element.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-green-500');
                    } else {
                        this.classList.remove('border-green-500');
                    }
                });
            }
        };

    // Initialize all select elements
        enhanceSelect('county_id');
        enhanceSelect('employer_id');
        enhanceSelect('workstation_type_id');

        // Form validation with enhanced UX
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('border-red-500', 'bg-red-50');
                        
                        // Add error animation
                        field.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => {
                            field.style.animation = '';
                        }, 500);
                    } else {
                        field.classList.remove('border-red-500', 'bg-red-50');
                        field.classList.add('border-green-500');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    const firstError = form.querySelector('.border-red-500');
                    if (firstError) {
                        firstError.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                    }
                }
                else {
                    // Disable submit to prevent duplicate submissions and show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.setAttribute('disabled', 'disabled');
                        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
                    }
                }
            });

            // Real-time validation
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('border-red-500', 'bg-red-50');
                        this.classList.add('border-green-500');
                    }
                });
            });
        }

        // Add smooth scrolling for the apply now button
        const applyButton = document.querySelector('button[onclick*="scrollIntoView"]');
        if (applyButton) {
            applyButton.addEventListener('click', function() {
                const formSection = document.getElementById('renewal-form');
                if (formSection) {
                    formSection.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        }
    });

    // Small helpers: hide/show spinner and alerts
    function showWorkstationSpinner(show) {
        const spinner = document.getElementById('workstation_spinner');
        if (!spinner) return;
        if (show) spinner.classList.remove('hidden'); else spinner.classList.add('hidden');
    }

    function showWorkstationAlert(message) {
        // Show both inline alert (fallback) and floating toast
        const alert = document.getElementById('workstation_load_alert');
        const alertMsg = document.getElementById('workstation_load_alert_msg');
        if (alert && alertMsg) {
            alertMsg.textContent = message;
            alert.classList.remove('hidden');
        }
        showToast(message, 'error');
    }

    function hideWorkstationAlert() {
        const alert = document.getElementById('workstation_load_alert');
        if (!alert) return;
        alert.classList.add('hidden');
    }

    // Dismiss button for the workstation alert
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'workstation_load_alert_close') {
            hideWorkstationAlert();
        }
    });

    // Toast container and helpers
    (function createToastContainer(){
        if (document.getElementById('toast_container')) return;
        const container = document.createElement('div');
        container.id = 'toast_container';
        container.setAttribute('aria-live', 'polite');
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    })();

    function showToast(message, type = 'info', timeout = 5000) {
        const container = document.getElementById('toast_container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'max-w-sm w-full rounded-lg shadow-lg p-3 text-sm flex items-center gap-3 justify-between';
        toast.setAttribute('role','status');
        toast.setAttribute('aria-atomic','true');

        if (type === 'error') {
            toast.classList.add('bg-red-50','border','border-red-200','text-red-700');
        } else if (type === 'success') {
            toast.classList.add('bg-green-50','border','border-green-200','text-green-700');
        } else {
            toast.classList.add('bg-slate-50','border','border-slate-200','text-slate-800');
        }

        toast.style.opacity = '0';
        toast.style.transition = 'opacity 250ms ease-in-out, transform 250ms ease-in-out';
        toast.style.transform = 'translateY(-6px)';

        const msg = document.createElement('div');
        msg.textContent = message;

        const close = document.createElement('button');
        close.className = 'ml-4 text-xs';
        close.innerHTML = '&times;';
        close.setAttribute('aria-label','Dismiss');
        close.addEventListener('click', () => {
            container.removeChild(toast);
        });

        toast.appendChild(msg);
        toast.appendChild(close);
        container.appendChild(toast);

        // animate in
        requestAnimationFrame(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; });

        // auto-hide
        setTimeout(()=>{
            try { if (toast.parentNode) toast.parentNode.removeChild(toast); } catch(e){}
        }, timeout);
    }

    // Add shake animation for form errors and other UI transitions
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .fade-in-transition { transition: opacity 300ms ease-in-out; }
        .opacity-0 { opacity: 0; }
        /* Toast styles fallback in case Tailwind classes are not sufficient */
        #toast_container div { max-width: 20rem; }

        /* Tom Select styling to match Tailwind-like inputs (w-full, rounded-xl, border, padding) */
        .ts-control {
            display: block !important;
            width: 100% !important;
            border-radius: 0.75rem !important; /* rounded-xl */
            border: 1px solid #cbd5e1 !important; /* slate-300 */
            background: #ffffff !important;
            padding: 0.625rem 1rem !important; /* approx px-4 py-3 */
            font-size: 0.875rem !important; /* text-sm */
            color: #0f172a !important; /* text-slate-700 */
            box-shadow: none !important;
            line-height: 1.25;
        }

        .ts-control.ts-focus, .ts-control:focus-within {
            border-color: #3b82f6 !important; /* blue-500 */
            box-shadow: 0 0 0 8px rgba(59,130,246,0.08) !important; /* focus ring */
            outline: none !important;
        }

        .ts-control .ts-input {
            padding: 0 !important;
            margin: 0 !important;
            min-height: 1rem !important;
            height: auto !important;
            color: inherit !important;
        }

        .ts-dropdown {
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px rgba(2,6,23,0.08) !important;
            border: 1px solid rgba(15,23,42,0.06) !important;
        }

        /* Select2 styling adjustments to match inputs */
        .select2-container--default .select2-selection--single {
            height: auto !important;
            min-height: 3rem !important;
            padding: 0.625rem 1rem !important;
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            background: #fff !important;
            color: #0f172a !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.25 !important;
            margin-top: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 2.25rem !important;
            right: 0.5rem !important;
            top: 0.6rem !important;
        }

        /* Smooth transitions for controls */
        .ts-control, .select2-container--default .select2-selection--single { transition: border-color 150ms ease, box-shadow 150ms ease; }

        /* Make space for the spinner inside the select wrapper */
        .workstation-select-container .ts-control, .workstation-select-container select { padding-right: 2.5rem !important; }
    `;
    document.head.appendChild(style);
</script>

<!-- Include jQuery first (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Include Select2 for enhanced select boxes -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Immediate test to verify script is loading
console.log('=== RENEWALS SCRIPT FILE LOADED ===');
console.log('Current time:', new Date().toISOString());

// Wait for both DOM and jQuery to be ready
(function() {
    function init() {
        console.log('=== RENEWALS PAGE INITIALIZATION STARTING ===');
        
        // Check if jQuery is loaded
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            console.error('ERROR: jQuery is not loaded!');
            alert('Error: jQuery is required but not loaded. Please refresh the page.');
            return;
        }
        
        console.log('✓ jQuery version:', $.fn.jquery);
        console.log('✓ Select2 available:', typeof $.fn.select2 !== 'undefined');
        console.log('✓ County select exists:', $('#county_id').length > 0);
        console.log('✓ Workstation select exists:', $('#workstation_id').length > 0);
        
        try {
            initializeSelects();
            // Load any existing applications (invoices) for this user
            try { if (typeof loadApplications === 'function') loadApplications(); } catch(e) { console.warn('loadApplications invocation failed', e); }
        } catch (error) {
            console.error('ERROR initializing selects:', error);
            alert('Error initializing dropdowns: ' + error.message);
        }
    }
    
    // If jQuery is already loaded, initialize immediately
    if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
        $(document).ready(init);
    } else {
        // Wait for jQuery to load
        document.addEventListener('DOMContentLoaded', function() {
            // Give a small delay for jQuery to load if it's loading
            setTimeout(function() {
                if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
                    console.error('jQuery still not loaded after DOM ready');
                    // Try loading jQuery
                    const script = document.createElement('script');
                    script.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
                    script.onload = function() {
                        console.log('jQuery loaded dynamically');
                        init();
                    };
                    script.onerror = function() {
                        console.error('Failed to load jQuery');
                        alert('Error: Could not load jQuery. Please check your internet connection.');
                    };
                    document.head.appendChild(script);
                } else {
                    init();
                }
            }, 100);
        });
    }
})();

function initializeSelects() {
    console.log('Initializing Select2 dropdowns...');
    
    // Initialize other selects first
    $('#county_id').select2({
        placeholder: 'Select County',
        allowClear: true,
        width: '100%'
    });

    $('#employer_id').select2({
        placeholder: 'Select Employer',
        allowClear: true,
        width: '100%'
    });

    $('#workstation_type_id').select2({
        placeholder: 'Select Workstation Type',
        allowClear: true,
        width: '100%'
    });

    // Initialize workstation select using Tom Select (remote load per-county)
    let tomWorkstation = null;
    const workstationSelectEl = document.getElementById('workstation_id');

    if (workstationSelectEl && typeof TomSelect !== 'undefined') {
        tomWorkstation = new TomSelect(workstationSelectEl, {
            valueField: 'id',
            labelField: 'name',
            searchField: ['name'],
            create: false,
            placeholder: 'Select County first',
            maxOptions: 100,
            render: {
                option: function(item, escape) {
                    return `<div>${escape(item.name)}</div>`;
                }
            },
            load: function(query, callback) {
                // We won't perform free-text remote search here; loading is driven by county change.
                callback();
            },
            onInitialize: function() {
                this.disable(); // keep disabled until a county is chosen
            }
        });

        // When Tom Select value changes, update workstation_name input
        tomWorkstation.on('change', function(value) {
            if (!value) {
                document.getElementById('workstation_name').value = '';
                return;
            }
            const opt = this.options[value];
            document.getElementById('workstation_name').value = (opt && opt.name) ? opt.name : '';
        });
    }

    // Test if county select exists
    console.log('County select element:', $('#county_id').length);
    console.log('Workstation select element:', $('#workstation_id').length);
    
    // Load workstations when county changes
    // Use Select2's change event which fires after selection
    $('#county_id').on('change', function() {
        const countyId = $(this).val();
        console.log('=== County change event triggered ===');
        console.log('County change event - countyId:', countyId, 'Type:', typeof countyId);
        console.log('Selected option data:', $(this).find('option:selected').data());
        console.log('Selected option text:', $(this).find('option:selected').text());
        
        // Ensure countyId is a valid integer string or number
        const countyIdNum = countyId ? parseInt(countyId, 10) : null;
        console.log('Parsed county ID:', countyIdNum, 'Is valid:', !isNaN(countyIdNum));
        
        if (countyIdNum && !isNaN(countyIdNum) && countyIdNum > 0) {
            loadWorkstationsForCounty(countyIdNum.toString());
        } else {
            // Reset workstation dropdown if county is cleared
            const workstationSelect = $('#workstation_id');
            workstationSelect.empty().append('<option value="">Select County first</option>');
            workstationSelect.prop('disabled', true);
            workstationSelect.trigger('change.select2');
            $('#workstation_name').val('');
        }
    });
    
    // Also listen for Select2 specific select event (fires when option is selected)
    $('#county_id').on('select2:select', function(e) {
        // Get the ID from the selected data or the select value
        const countyId = e.params?.data?.id || $(this).val();
        console.log('=== Select2 select event triggered ===');
        console.log('Select2 event data:', e.params?.data);
        console.log('Select2 select event - countyId:', countyId, 'Type:', typeof countyId);
        
        // Ensure countyId is a valid integer string or number
        const countyIdNum = countyId ? parseInt(countyId, 10) : null;
        console.log('Parsed county ID from Select2:', countyIdNum, 'Is valid:', !isNaN(countyIdNum));
        
        if (countyIdNum && !isNaN(countyIdNum) && countyIdNum > 0) {
            loadWorkstationsForCounty(countyIdNum.toString());
        }
    });
    
    // Debug: Log all Select2 events
    $('#county_id').on('select2:open select2:close select2:selecting select2:unselect', function(e) {
        console.log('Select2 event:', e.type, 'Value:', $(this).val());
    });

    function loadWorkstationsForCounty(countyId) {
        console.log('loadWorkstationsForCounty called with:', countyId);
        
        const workstationSelect = $('#workstation_id');
        const workstationNameInput = $('#workstation_name');

        if (!countyId || countyId === '' || countyId === null) {
            console.log('No county ID, resetting workstation dropdown');
            // Reset to initial state
            workstationSelect.empty().append('<option value="">Select County first</option>');
            workstationSelect.prop('disabled', true);
            workstationSelect.trigger('change.select2');
            workstationNameInput.val('');
            return;
        }

        // Show loading state
    console.log('Setting loading state for workstations');
    if (tomWorkstation) {
        tomWorkstation.clearOptions();
        tomWorkstation.disable();
    } else {
        workstationSelect.empty().append('<option value="">Loading workstations...</option>');
        workstationSelect.prop('disabled', true);
        // Notify Select2 listeners if present
        workstationSelect.trigger('change.select2');
        workstationSelect.trigger('change');
    }
    // Show a transient loading alert to the user and spinner
    showWorkstationSpinner(true);
    showWorkstationAlert('Loading workstations...');
        workstationNameInput.val('');

        // Fetch workstations
        const routeUrl = '{{ route("practitioner.renewals.workstations") }}';
        const url = `${routeUrl}?county_id=${countyId}`;
        console.log('=== FETCHING WORKSTATIONS ===');
        console.log('Route URL:', routeUrl);
        console.log('Full URL:', url);
        console.log('County ID being sent:', countyId, 'Type:', typeof countyId);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Fetch response status:', response.status, response.statusText);
            console.log('Fetch response headers:', [...response.headers.entries()]);

            if (!response.ok) {
                console.error('Response not OK:', response.status, response.statusText);
                return response.text().then(text => {
                    console.error('Error response body:', text);
                    // Return a fallback object rather than throwing to allow graceful handling
                    return { success: false, workstations: [], error: `HTTP ${response.status}` };
                });
            }

            return response.text().then(text => {
                console.log('Raw response text:', text);
                try {
                    const json = JSON.parse(text);
                    console.log('Parsed JSON:', json);
                    return json;
                } catch (e) {
                    console.warn('Initial JSON.parse failed, attempting to recover. Error:', e);

                    // Try to recover JSON if the API prepends stray characters (e.g. BOM, stray colons)
                    const firstBrace = text.indexOf('{');
                    const firstBracket = text.indexOf('[');
                    let start = -1;
                    if (firstBrace === -1 && firstBracket === -1) {
                        // No obvious JSON start, return fallback and include raw text for debugging
                        console.error('No JSON start found in response text');
                        return { success: false, workstations: [], error: 'Invalid JSON response', raw: text };
                    }

                    if (firstBrace === -1) start = firstBracket; else if (firstBracket === -1) start = firstBrace; else start = Math.min(firstBrace, firstBracket);

                    try {
                        const sliced = text.substring(start);
                        console.log('Attempting to parse JSON from substring starting at', start, sliced.substring(0,200));
                        const recovered = JSON.parse(sliced);
                        console.log('Recovered JSON:', recovered);
                        return recovered;
                    } catch (e2) {
                        console.error('Recovered parse also failed', e2);
                        return { success: false, workstations: [], error: 'Invalid JSON response after recovery', raw: text };
                    }
                }
            });
        })
        .then(data => {
            console.log('Workstations response received:', data);
            console.log('Response type:', typeof data);
            console.log('Response keys:', data ? Object.keys(data) : 'null/undefined');
            
            // Clear existing options / hide any previous load error
            $('#workstation_load_alert').addClass('hidden');

            if (data && data.success && data.workstations && Array.isArray(data.workstations) && data.workstations.length > 0) {
                console.log(`Adding ${data.workstations.length} workstations to dropdown`);
                const items = data.workstations.map(w => {
                    const id = w.id || w.workstation_id;
                    const name = w.workstation || w.name || `Workstation ${id}`;
                    return { id: id, name: name };
                });

                if (tomWorkstation) {
                    tomWorkstation.addOptions(items);
                    tomWorkstation.enable();
                } else {
                    workstationSelect.append('<option value="">Select Workstation</option>');
                    workstationSelect.addClass('opacity-0 fade-in-transition');
                    items.forEach(it => {
                        workstationSelect.append(
                            $('<option></option>')
                                .attr('value', it.id)
                                .attr('data-workstation-name', it.name)
                                .text(it.name)
                        );
                    });
                    workstationSelect.prop('disabled', false);
                    setTimeout(() => {
                        workstationSelect.removeClass('opacity-0 fade-in-transition');
                    }, 20);
                    // Notify Select2 listeners if present
                    workstationSelect.trigger('change.select2');
                    workstationSelect.trigger('change');
                }

                console.log('Workstations added successfully');
                showWorkstationSpinner(false);
                hideWorkstationAlert();
            } else {
                console.log('No workstations found or invalid response:', data);
                if (tomWorkstation) {
                    tomWorkstation.clearOptions();
                    tomWorkstation.enable();
                } else {
                    workstationSelect.append('<option value="">No workstations available</option>');
                    workstationSelect.prop('disabled', false);
                    workstationSelect.trigger('change.select2');
                    workstationSelect.trigger('change');
                }

                showWorkstationSpinner(false);
                const msg = (data && data.error) ? data.error : 'No workstations available for the selected county.';
                showWorkstationAlert('Unable to load workstations: ' + msg);
            }
        })
        .catch(error => {
            console.error('Error fetching workstations:', error);
            workstationSelect.empty().append('<option value="">Error loading workstations</option>');
            workstationSelect.prop('disabled', false);
            workstationSelect.trigger('change.select2');
            workstationSelect.trigger('change');

            // Show friendly alert to the user and hide spinner
            showWorkstationSpinner(false);
            showWorkstationAlert('Error loading workstations. Please try again later.');
        });
    }

    // Auto-fill workstation name when workstation is selected
    $('#workstation_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const workstationName = selectedOption.data('workstation-name');
        
        if (workstationName) {
            $('#workstation_name').val(workstationName);
        }
    });

    // Allow manual workstation name input without clearing dropdown
    $('#workstation_name').on('input', function() {
        // If user starts typing manually, you might want to clear the workstation_id
        // or keep them independent based on your business logic
        if ($(this).val().trim() !== '') {
            // Optional: Clear workstation select if user types manually
            // $('#workstation_id').val('').trigger('change.select2');
        }
    });
    
    console.log('=== SELECT2 INITIALIZATION COMPLETE ===');
    console.log('County select initialized:', $('#county_id').length > 0);
    console.log('Workstation select initialized:', $('#workstation_id').length > 0);
    
    // Test: Try to manually trigger a change event
    setTimeout(function() {
        console.log('Testing county select value:', $('#county_id').val());
        console.log('Testing if change event works...');
        $('#county_id').trigger('change');
    }, 1000);
}
</script>

<script>
    // Fetch and render practitioner applications (invoices)
    async function loadApplications() {
        const url = '{{ route("practitioner.applications") }}';
        try {
            const resp = await fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (!resp.ok) {
                console.warn('Failed to fetch applications', resp.status);
                document.getElementById('invoice_placeholder').textContent = 'Unable to load applications.';
                document.getElementById('invoice_container').classList.remove('hidden');
                return;
            }

            const data = await resp.json();
            if (!data.success || !Array.isArray(data.applications) || data.applications.length === 0) {
                document.getElementById('invoice_placeholder').textContent = 'No recent applications found.';
                document.getElementById('invoice_container').classList.remove('hidden');
                return;
            }

            // Pick the latest application (assume last element or sort by renewal_date)
            const apps = data.applications.slice();
            apps.sort((a,b) => new Date(b.renewal_date || b.invoice_details?.invoice_date || 0) - new Date(a.renewal_date || a.invoice_details?.invoice_date || 0));
            const app = apps[0];
            renderInvoice(app);
            document.getElementById('invoice_container').classList.remove('hidden');
        } catch (err) {
            console.error('Error loading applications', err);
            document.getElementById('invoice_placeholder').textContent = 'Error loading applications.';
            document.getElementById('invoice_container').classList.remove('hidden');
        }
    }

    function renderInvoice(app) {
        const detailsArea = document.getElementById('invoice_details_area');
        const actions = document.getElementById('invoice_actions');
        detailsArea.innerHTML = '';
        actions.innerHTML = '';

        const invoice = app.invoice_details || {};
        const invoiceNumber = invoice.invoice_number || invoice.billRefNumber || app.application_id || 'N/A';
        const invoiceDate = invoice.invoice_date || invoice.renewal_date || '';
        const desc = invoice.invoice_desc || invoice.billDesc || 'Practice Renewal';
        const total = invoice.total_amount || invoice.amountExpected || invoice.amount_expected || invoice.amount || '0';
        const amountDue = invoice.amount_due ?? invoice.amount_due ?? invoice.amountDue ?? invoice.amountExpected ?? total;
        const amountPaid = invoice.amount_paid ?? invoice.amount_paid ?? 0;

        const left = document.createElement('div');
        left.innerHTML = `
            <p class="text-sm text-slate-500 mb-2">Invoice # <strong>${invoiceNumber}</strong></p>
            <p class="text-sm text-slate-500 mb-2">Date: <strong>${invoiceDate}</strong></p>
            <p class="text-sm text-slate-700 mb-1">${desc}</p>
        `;

        const right = document.createElement('div');
        right.className = 'text-right';
        right.innerHTML = `
            <p class="text-sm text-slate-500">Total</p>
            <p class="text-2xl font-bold text-slate-800">${total}</p>
            <p class="text-sm text-slate-500 mt-2">Balance: <strong>${invoice.balance_due ?? invoice.balanceDue ?? (total - amountPaid)}</strong></p>
        `;

        detailsArea.appendChild(left);
        actions.appendChild(right);

        // Pay button (only if amount due > 0)
        const payNow = document.createElement('button');
        payNow.type = 'button';
        payNow.className = 'mt-4 inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl';
        payNow.style.backgroundColor = '#3b82f6';
        payNow.textContent = 'Click to Pay Now';
        payNow.addEventListener('click', function() {
            openPesaflow(invoice);
            // Start polling for payment status
            startPaymentPolling(app.application_id || app.applicationID || invoice.billRefNumber);
        });

        // Only show if there is an outstanding balance
        const balance = Number(invoice.balance_due ?? invoice.balanceDue ?? (total - amountPaid));
        if (!isNaN(balance) && balance > 0) {
            actions.appendChild(document.createElement('div'));
            actions.appendChild(payNow);
        } else {
            const paidBadge = document.createElement('div');
            paidBadge.className = 'mt-4 text-sm text-green-700 font-semibold';
            paidBadge.textContent = 'Paid';
            actions.appendChild(paidBadge);
        }
    }

    function openPesaflow(invoice) {
        const modal = document.getElementById('pesaflow_modal');
        const iframe = document.getElementById('pesaflow_iframe');

        // Ensure iframe has a name to target
        const iframeName = iframe.name || 'pesaflow_target';

        // Build form and POST into the iframe (preferred over GET querystring)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = (invoice.pesaflow_url || invoice.pesaflowUrl || invoice.pesaflow);
        form.target = iframeName;
        form.style.display = 'none';

        // Include all known invoice fields expected by Pesaflow (use keys as provided by API)
        const fields = {
            convenience_fee: invoice.convenience_fee || invoice.convenienceFee || invoice.convenience || undefined,
            apiClientID: invoice.apiClientID || invoice.apiClientId || undefined,
            serviceID: invoice.serviceID || invoice.serviceId || undefined,
            notificationURL: invoice.notificationURL || invoice.notificationUrl || invoice.notification_url || undefined,
            callBackURLOnSuccess: invoice.callBackURLOnSuccess || invoice.callBackUrlOnSuccess || invoice.callBackURL || invoice.callBackUrl || undefined,
            pictureURL: invoice.pictureURL || invoice.pictureUrl || invoice.picture_url || undefined,
            billRefNumber: invoice.billRefNumber || invoice.bill_ref_number || invoice.invoice_number || undefined,
            currency: invoice.currency || 'KES',
            amountExpected: invoice.amountExpected || invoice.amount_expected || invoice.total_amount || invoice.amount || undefined,
            billDesc: invoice.billDesc || invoice.invoice_desc || invoice.billDesc || undefined,
            clientMSISDN: invoice.clientMSISDN || invoice.clientMsisdn || invoice.client_msisdn || undefined,
            clientIDNumber: invoice.clientIDNumber || invoice.client_id_number || undefined,
            clientEmail: invoice.clientEmail || invoice.client_email || undefined,
            clientName: invoice.clientName || invoice.client_name || undefined,
            secureHash: invoice.secureHash || invoice.secure_hash || undefined
        };

        Object.keys(fields).forEach(key => {
            const val = fields[key];
            if (val === undefined || val === null) return;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = String(val);
            form.appendChild(input);
        });

        document.body.appendChild(form);

        // Defensive: ensure there is an action URL to POST to
        if (!form.action) {
            console.warn('Pesaflow: missing action URL on invoice, cannot open iframe.');
            showToast('Payment is temporarily unavailable. Please try again later or contact support.', 'warning', 8000);
            try { document.body.removeChild(form); } catch(e){}
            return;
        }

        // Show modal and submit the form into the iframe
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Create a simple overlay inside the modal while loading
        let overlay = document.createElement('div');
        overlay.className = 'absolute inset-0 z-20 flex items-center justify-center bg-black/30 backdrop-blur-sm';
        overlay.id = 'pesaflow_loading_modal';
        overlay.innerHTML = '<div class="text-white text-center">Opening payment... If this takes longer than a few seconds we will open the payment in a new tab.</div>';
        // Try to attach overlay to modal content area
        try {
            const inner = modal.querySelector('div > div') || modal.firstElementChild;
            if (inner) inner.appendChild(overlay);
        } catch(e) { console.warn('Unable to attach loading overlay', e); }

        try { form.submit(); } catch (e) { console.error('Pesaflow form submit failed', e); }

        // Set up a fallback: if iframe doesn't load within timeout, open payment in a new tab
        let fallbackTimer = setTimeout(function() {
            console.warn('Pesaflow: iframe did not load within timeout, opening payment in new tab as fallback.');
            showToast('Payment iframe did not respond — opening payment in a new tab.', 'info', 8000);

            // Build a form that targets a new tab and submit it
            const newForm = document.createElement('form');
            newForm.method = 'POST';
            newForm.action = form.action;
            newForm.target = '_blank';
            newForm.style.display = 'none';
            Array.from(form.elements).forEach(el => {
                if (!el.name) return;
                const clone = document.createElement('input');
                clone.type = 'hidden';
                clone.name = el.name;
                clone.value = el.value;
                newForm.appendChild(clone);
            });
            document.body.appendChild(newForm);
            try { newForm.submit(); } catch(e){ console.error('Fallback submit failed', e); }
            // Cleanup: remove forms and hide modal
            try { document.body.removeChild(newForm); } catch(e){}
            try { document.body.removeChild(form); } catch(e){}
            try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}
            try { iframe.src = 'about:blank'; } catch(e){}
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 7000);

        // Clear fallback if iframe loads successfully
        const onIframeLoad = function() {
            clearTimeout(fallbackTimer);
            try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}
            // keep modal open so user can interact with payment iframe
        };
        iframe.addEventListener('load', onIframeLoad, { once: true });

        // Close handler clears iframe content and hides modal
        document.getElementById('pesaflow_close').onclick = function() {
            try { iframe.src = 'about:blank'; } catch(e){}
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            try { clearTimeout(fallbackTimer); } catch(e){}
            try { document.body.removeChild(form); } catch(e){}
            try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}
        };
    }

    // Polling: check applications endpoint every 30s up to 30 minutes
    let _paymentPollInterval = null;
    let _paymentPollAttempts = 0;
    function startPaymentPolling(applicationId) {
        _paymentPollAttempts = 0;
        if (_paymentPollInterval) clearInterval(_paymentPollInterval);
        _paymentPollInterval = setInterval(async function() {
            _paymentPollAttempts++;
            console.log('Payment poll attempt', _paymentPollAttempts);
            try {
                const resp = await fetch('{{ route("practitioner.applications") }}', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                if (!resp.ok) return;
                const data = await resp.json();
                if (!data.success) return;
                const apps = data.applications || [];
                const found = apps.find(a => (a.application_id == applicationId) || (a.applicationID == applicationId) || (a.invoice_details && (a.invoice_details.billRefNumber == applicationId || a.invoice_details.invoice_number == applicationId)));
                if (found) {
                    const paid = Number(found.invoice_details?.amount_paid ?? found.invoice_details?.amountPaid ?? 0);
                    const expected = Number(found.invoice_details?.amountExpected ?? found.invoice_details?.total_amount ?? 0);
                    if (!isNaN(paid) && !isNaN(expected) && paid >= expected && expected > 0) {
                        // Success
                        showToast('Payment received. Your license will be available within 30 minutes.', 'success', 10000);
                        clearInterval(_paymentPollInterval);
                        _paymentPollInterval = null;
                    }
                }
            } catch (e) {
                console.warn('Polling error', e);
            }
            // Stop after 60 attempts (30 minutes)
            if (_paymentPollAttempts >= 60) {
                clearInterval(_paymentPollInterval);
                _paymentPollInterval = null;
                showToast('Payment may have been processed. If your license is not available after 30 minutes, contact support.', 'info', 15000);
            }
        }, 30 * 1000);
    }
</script>
@endpush