<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 p-6 lg:p-8 shadow-lg">
    <div class="relative z-10">
        <div class="flex items-center justify-between">
            <div class="text-blue">
                <h1 class="text-2xl font-bold lg:text-3xl mb-2">
                    Report CPD Activity
                </h1>
                <p class="text-indigo-100 text-sm lg:text-base">
                    Self-report your continuing professional development activity
                </p>
            </div>
            <a
                href="{{ route('practitioner.cpd') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 rounded-xl bg-white/20 backdrop-blur-sm hover:bg-white/30 hover:shadow-lg active:scale-95 border border-white/30"
                aria-label="Cancel and return to CPD list"  style="background-color: hsla(14, 94%, 50%, 1.00); border: 1px solid #e52705ff;"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Return to CPD List
            </a>
        </div>
    </div>
    <!-- Decorative Elements -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-purple-400/20 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
</div>
