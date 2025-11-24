<!-- Action Buttons -->
<div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a
        href="{{ route('practitioner.cpd') }}"
        class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600"
        aria-label="Cancel and return to CPD list"  style="background-color: rgba(212, 92, 23, 1); border: 1px solid #eb7d17ff;"
        >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Cancel
    </a>
    <button
        type="submit"
        class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg active:scale-95"
        style="background-color: #2563eb; border: 1px solid #1d4ed8;"
        
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Submit CPD Report
    </button>
</div>
