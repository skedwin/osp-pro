@props(['profileSummary', 'firstName', 'lastName', 'dob'])

<div class="p-5 mb-6 border border-slate-200 rounded-2xl lg:p-6" x-data="{ editMode: false }">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="w-full">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-semibold text-slate-800">
                    Personal Information
                </h4>
                <button
                    @click="editMode = !editMode"
                    type="button"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 transition rounded-lg border border-slate-300 hover:bg-slate-50"
                    x-text="editMode ? 'Cancel' : 'Edit'"
                ></button>
            </div>

            {{-- View Mode --}}
            <div x-show="!editMode" class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">First Name</p>
                    <p class="text-sm font-medium text-slate-800">{{ $firstName ?: '—' }}</p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">Last Name</p>
                    <p class="text-sm font-medium text-slate-800">{{ $lastName ?: '—' }}</p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">Email address</p>
                    <p class="text-sm font-medium text-slate-800">{{ data_get($profileSummary, 'Email', '—') }}</p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">Phone</p>
                    <p class="text-sm font-medium text-slate-800">{{ data_get($profileSummary, 'MobileNo', '—') }}</p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">ID Number</p>
                    <p class="text-sm font-medium text-slate-800">{{ data_get($profileSummary, 'IdNumber', '—') }}</p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">Passport Number</p>
                    <p class="text-sm font-medium text-slate-800">{{ data_get($profileSummary, 'PassportNumber', '—') }}</p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">Birth Certificate Number</p>
                    <p class="text-sm font-medium text-slate-800">{{ data_get($profileSummary, 'BirthCertNo', '—') }}</p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">Index Number</p>
                    <p class="text-sm font-medium text-slate-800">{{ data_get($profileSummary, 'IndexNo', '—') }}</p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">Date of Birth</p>
                    <p class="text-sm font-medium text-slate-800">
                        {{ $dob['formatted'] }}{{ $dob['age'] ? ' (' . $dob['age'] . ' years)' : '' }}
                    </p>
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-slate-500">Gender</p>
                    <p class="text-sm font-medium text-slate-800">
                        @php
                            $gender = strtoupper(data_get($profileSummary, 'Gender', ''));
                            $genderDisplay = match($gender) {
                                'M' => 'Male',
                                'F' => 'Female',
                                default => $gender ?: '—'
                            };
                        @endphp
                        {{ $genderDisplay }}
                    </p>
                </div>
            </div>

            {{-- Edit Mode --}}
            <form method="POST" action="{{ route('portal.profile.update') }}" x-show="editMode" x-cloak
                  @submit.prevent="submitForm($el)" x-data>
                @csrf
                <input type="hidden" name="section" value="personal_info">
                
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            First Name
                        </label>
                        <input
                            type="text"
                            name="first_name"
                            value="{{ $firstName }}"
                            disabled
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Last Name
                        </label>
                        <input
                            type="text"
                            name="last_name"
                            value="{{ $lastName }}"
                            disabled
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Email address
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ data_get($profileSummary, 'Email', '') }}"
                            class="block w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-blue-600 focus:ring-blue-100"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Phone
                        </label>
                        <input
                            type="tel"
                            name="mobile_no"
                            value="{{ data_get($profileSummary, 'MobileNo', '') }}"
                            class="block w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-blue-600 focus:ring-blue-100"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            ID Number <span class="text-slate-400"></span>
                        </label>
                        <input
                            type="text"
                            value="{{ data_get($profileSummary, 'IdNumber', '') }}"
                            disabled
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Passport Number <span class="text-slate-400"></span>
                        </label>
                        <input
                            type="text"
                            value="{{ data_get($profileSummary, 'PassportNumber', '—') }}"
                            disabled
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500"
                        >
                    </div>
                    
                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                        Birth Certificate Number <span class="text-slate-400"></span>
                        </label>
                        <input
                            type="text"
                            value="{{ data_get($profileSummary, 'BirthCertNo', '—') }}"
                            disabled
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Index Number <span class="text-slate-400"></span>
                        </label>
                        <input
                            type="text"
                            value="{{ data_get($profileSummary, 'IndexNo', '') }}"
                            disabled
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Date of Birth
                        </label>
                        <input
                            type="date"
                            name="date_of_birth"
                            value="{{ $dob['iso'] }}"
                            disabled
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Gender
                        </label>
                        <select
                            name="gender"
                            disabled
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500"
                        >
                            <option value="">Select Gender</option>
                            <option value="M" {{ strtoupper(data_get($profileSummary, 'Gender', '')) === 'M' ? 'selected' : '' }}>Male</option>
                            <option value="F" {{ strtoupper(data_get($profileSummary, 'Gender', '')) === 'F' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="editMode = false"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        style="background-color: #2563eb;"
                        >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>