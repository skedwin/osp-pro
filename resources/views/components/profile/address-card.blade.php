@props(['profileSummary'])

<div class="p-5 mb-6 border border-slate-200 rounded-2xl lg:p-6" x-data="{ editMode: false }">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="w-full">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-semibold text-slate-800">
                    Address
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
                    <p class="mb-2 text-xs leading-normal text-slate-500">Address</p>
                    <p class="text-sm font-medium text-slate-800">{{ data_get($profileSummary, 'Address', '—') }}</p>
                </div>
            </div>

            {{-- Edit Mode --}}
        <form method="POST" action="{{ route('portal.profile.update') }}" x-show="editMode" x-cloak
            @submit.prevent="submitForm($el)">
                @csrf
                <input type="hidden" name="section" value="address">
                
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-xs font-medium leading-normal text-slate-500">
                            Address
                        </label>
                        <textarea
                            name="address"
                            rows="3"
                            class="block w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-blue-600 focus:ring-blue-100"
                        >{{ data_get($profileSummary, 'Address', '') }}</textarea>
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
                        style="background-color: #2563eb;">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>