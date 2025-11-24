<!-- Points & Evidence Card -->
<div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
    <div class="flex items-center gap-3 mb-6">
        <div class="rounded-xl bg-green-100 p-3">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-800">Points & Evidence</h3>
            <p class="text-sm text-slate-500">Provide points awarded and supporting evidence</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- CPD Points -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                CPD Points Awarded <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    type="number"
                    name="points"
                    value="{{ old('points') }}"
                    step="0.5"
                    min="0.5"
                    max="50"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-green-500 focus:ring-4 focus:ring-green-100 @error('points') border-red-300 focus:border-red-500 focus:ring-red-100 @enderror"
                    placeholder="e.g., 5.0"
                >
                <div class="absolute right-4 top-1/2 -translate-y-1/2">
                    <span class="text-sm font-semibold text-green-600">pts</span>
                </div>
            </div>
            <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Minimum: 0.5 points | Maximum: 50 points
            </p>
            @error('points')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Evidence URL -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Evidence URL <span class="text-red-500">*</span>
            </label>
            <input
                type="url"
                name="cpd_evidence"
                value="{{ old('cpd_evidence') }}"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-green-500 focus:ring-4 focus:ring-green-100 @error('cpd_evidence') border-red-300 focus:border-red-500 focus:ring-red-100 @enderror"
                placeholder="https://example.com/certificate.pdf"
            >
            <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Link to certificate, transcript, or proof of participation
            </p>
            @error('cpd_evidence')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
