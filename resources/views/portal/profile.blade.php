@extends('layouts.app', ['title' => 'Profile'])

@section('content')
    @php
        use Carbon\Carbon;

        $profileSummary    = data_get($bioProfile, 'profile', []);
        $educationItems    = data_get($bioProfile, 'education', []);
        $registrationItems = data_get($bioProfile, 'registration', []);
        $licenseItems      = data_get($bioProfile, 'license', []);
        $cpdItems          = data_get($bioProfile, 'cpd', []);
        $avatar            = data_get($bioProfile, 'avatar');
        $hasProfile        = filled($profileSummary);

        // Extract name parts
        $fullName = data_get($profileSummary, 'Name', '');
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? $fullName;

        // Date formatting helper
        function formatProfileDate($dateString) {
            if (!$dateString) return ['formatted' => '—', 'age' => '', 'iso' => null];
            
            try {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
                    $date = Carbon::createFromFormat('Y-m-d', $dateString);
                } else {
                    $date = Carbon::parse($dateString);
                }
                return [
                    'formatted' => $date->format('d M Y'),
                    'age' => $date->age,
                    'iso' => $date->format('Y-m-d')
                ];
            } catch (\Exception $e) {
                return ['formatted' => $dateString, 'age' => '', 'iso' => null];
            }
        }

        $dob = formatProfileDate(data_get($profileSummary, 'DateOfBirth'));
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-white p-5 lg:p-6" 
         x-data="profileManager()" 
         x-init="init()">
        
        {{-- Success Notification --}}
        <div x-show="showSuccess" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200"
             x-cloak>
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium" x-text="successMessage"></p>
                <button @click="showSuccess = false" class="ml-auto text-green-600 hover:text-green-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Error Notification --}}
        <div x-show="showError" 
             x-transition
             class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200"
             x-cloak>
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 font-medium" x-text="errorMessage"></p>
                <button @click="showError = false" class="ml-auto text-red-600 hover:text-red-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between mb-5 lg:mb-7">
            <h3 class="text-lg font-semibold text-slate-800">
                Profile
            </h3>
            <button
                @click="editModalOpen = true"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition rounded-lg bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                aria-label="Edit profile information"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Profile</span>
            </button>
        </div>

        {{-- Profile Overview Card --}}
        <x-profile.overview-card 
            :avatar="$avatar" 
            :firstName="$firstName"
            :fullName="$fullName"
            :registrationItems="$registrationItems"
            :profileSummary="$profileSummary" />

        {{-- Personal Information Card --}}
        <x-profile.personal-info-card 
            :profileSummary="$profileSummary"
            :firstName="$firstName"
            :lastName="$lastName"
            :dob="$dob" />

        {{-- Address Card --}}
        <x-profile.address-card :profileSummary="$profileSummary" />

        {{-- Education History Card --}}
        @if (!empty($educationItems))
            <x-profile.education-card :educationItems="$educationItems" />
        @else
            <x-profile.empty-state 
                icon="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"
                title="No Education History"
                description="Education information will appear here when available." />
        @endif

        {{-- Registration Records Card --}}
        @if (!empty($registrationItems))
            <x-profile.registration-card :registrationItems="$registrationItems" />
        @else
            <x-profile.empty-state 
                icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                title="No Registration Records"
                description="Registration information will appear here when available." />
        @endif

        {{-- Licenses Card --}}
        @if (!empty($licenseItems))
            <x-profile.license-card :licenseItems="$licenseItems" />
        @else
            <x-profile.empty-state 
                icon="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                title="No License Information"
                description="License information will appear here when available." />
        @endif

        {{-- CPD Information Card --}}
        @if (!empty($cpdItems))
            <x-profile.cpd-card :cpdItems="$cpdItems" />
        @else
            <x-profile.empty-state 
                icon="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"
                title="No CPD Information"
                description="CPD data will appear here when available." />
        @endif

        {{-- Edit Profile Modal --}}
        <div
            x-show="editModalOpen"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            @keydown.escape.window="editModalOpen = false"
        >
            <div
                class="relative w-full max-w-3xl max-h-[80vh] overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl lg:p-8"
                @click.outside="editModalOpen = false"
            >
                <button
                    type="button"
                    class="absolute top-4 right-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50"
                    @click="editModalOpen = false"
                    aria-label="Close modal"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <path d="M6 6L18 18M6 18L18 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </button>

                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-slate-900" id="edit-profile-title">
                        Edit Personal Information
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Update your contact details to keep your profile up to date.
                    </p>
                </div>

                <form method="POST" action="{{ route('portal.profile.update') }}" 
                      @submit.prevent="submitForm($el)"
                      class="space-y-8">
                    @csrf
                    <input type="hidden" name="index_id" value="{{ data_get($profileSummary, 'IndexNo') }}">

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7">
                        <div>
                            <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                                Email Address
                            </label>
                            <input
                                type="email"
                                name="email"
                                value="{{ data_get($profileSummary, 'Email', '') }}"
                                class="block w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-blue-600 focus:ring-blue-100"
                                required
                                :class="{ 'border-red-300': errors.email }"
                            >
                            <p x-show="errors.email" class="mt-1 text-xs text-red-600" x-text="errors.email ? errors.email[0] : ''"></p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                                Mobile Number
                            </label>
                            <input
                                type="tel"
                                name="mobile_no"
                                value="{{ data_get($profileSummary, 'MobileNo', '') }}"
                                class="block w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-blue-600 focus:ring-blue-100"
                                required
                                :class="{ 'border-red-300': errors.mobile_no }"
                            >
                            <p x-show="errors.mobile_no" class="mt-1 text-xs text-red-600" x-text="errors.mobile_no ? errors.mobile_no[0] : ''"></p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Address
                        </label>
                        <textarea
                            name="address"
                            rows="3"
                            class="block w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-blue-600 focus:ring-blue-100"
                            :class="{ 'border-red-300': errors.address }"
                        >{{ data_get($profileSummary, 'Address', '') }}</textarea>
                        <p x-show="errors.address" class="mt-1 text-xs text-red-600" x-text="errors.address ? errors.address[0] : ''"></p>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Profile Picture URL
                        </label>
                        <input
                            type="url"
                            name="profile_pic"
                            value="{{ $avatar }}"
                            class="block w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm transition focus:border-blue-600 focus:ring-blue-100"
                            placeholder="https://example.com/path/to/photo.jpg"
                            :class="{ 'border-red-300': errors.profile_pic }"
                        >
                        <p x-show="errors.profile_pic" class="mt-1 text-xs text-red-600" x-text="errors.profile_pic ? errors.profile_pic[0] : ''"></p>
                        @if ($avatar)
                            <p class="mt-2 text-xs text-slate-500">
                                Current: <a href="{{ $avatar }}" target="_blank" class="text-blue-600 hover:underline">View photo</a>
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button
                            type="button"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50"
                            @click="editModalOpen = false"
                            :disabled="loading"
                        >
                            Close
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="loading"
                        >
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loading ? 'Saving...' : 'Save Changes'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function profileManager() {
            return {
                editMode: false,
                editModalOpen: false,
                loading: false,
                errors: {},
                showSuccess: false,
                showError: false,
                successMessage: '',
                errorMessage: '',

                init() {
                    // Check for success message in URL
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.get('success') === 'true') {
                        this.showSuccessMessage('Profile updated successfully!');
                    }
                },

                async submitForm(form) {
                    this.loading = true;
                    this.errors = {};
                    this.hideNotifications();

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showSuccessMessage(data.message || 'Profile updated successfully!');
                            this.editModalOpen = false;
                            this.editMode = false;
                            
                            // Reload page after a short delay to show success message
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            if (data.errors) {
                                this.errors = data.errors;
                                this.showErrorMessage('Please fix the errors below.');
                            } else {
                                this.showErrorMessage(data.message || 'Error updating profile.');
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showErrorMessage('Network error occurred. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                },

                showSuccessMessage(message) {
                    this.successMessage = message;
                    this.showSuccess = true;
                    this.scrollToTop();
                },

                showErrorMessage(message) {
                    this.errorMessage = message;
                    this.showError = true;
                    this.scrollToTop();
                },

                hideNotifications() {
                    this.showSuccess = false;
                    this.showError = false;
                },

                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection