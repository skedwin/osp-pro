@props(['avatar', 'firstName', 'fullName', 'registrationItems', 'profileSummary'])

<div class="p-5 mb-6 border border-slate-200 rounded-2xl lg:p-6">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-col items-center w-full gap-6 xl:flex-row">
            <div class="w-20 h-20 overflow-hidden border border-slate-200 rounded-full">
                @if ($avatar)
                    <img src="{{ $avatar }}" alt="Profile photo" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-slate-100">
                        <span class="text-2xl font-semibold text-slate-500">
                            {{ strtoupper(substr($firstName, 0, 1)) }}
                        </span>
                    </div>
                @endif
            </div>
            <div class="order-3 xl:order-2">
                <h4 class="mb-2 text-lg font-semibold text-center text-slate-800 xl:text-left">
                    {{ $fullName ?: 'Practitioner Name' }}
                </h4>
                <div class="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left">
                    @if (!empty($registrationItems))
                        <p class="text-sm text-slate-500">
                            {{ data_get($registrationItems[0], 'cadre_text', 'Nurse') }}
                        </p>
                        <div class="hidden h-3.5 w-px bg-slate-300 xl:block"></div>
                    @endif
                    <p class="text-sm text-slate-500">
                        {{ data_get($profileSummary, 'Address', 'Kenya') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>