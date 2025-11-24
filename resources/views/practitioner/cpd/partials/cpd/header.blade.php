<!-- Header Section -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 p-6 lg:p-8 shadow-lg">
    <div class="relative z-10">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="text-black">
                <h1 class="text-2xl font-bold lg:text-3xl mb-2">
                    Continuing Professional Development
                </h1>
                <p class="Text-blue-100 text-sm lg:text-base">
                    Track and manage your continuing professional development activities for {{ $currentYear }}
                </p>    
            </div>
            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    @click="claimTokenModalOpen = true"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 rounded-xl hover:shadow-lg active:scale-95 whitespace-nowrap"
                    style="background-color: #2563eb; border: 1px solid #1d4ed8;"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Claim Token
                </button>
                <a
                    href="{{ route('practitioner.cpd.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 rounded-xl hover:shadow-lg active:scale-95 whitespace-nowrap"
                    style="background-color: #2563eb; border: 1px solid #1d4ed8;"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Report CPD Activity
                </a>
            </div>
        </div>
    </div>
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-400/20 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
</div>
