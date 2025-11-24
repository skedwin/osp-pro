<!-- Activity Description Card -->
<div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
    <div class="flex items-center gap-3 mb-6">
        <div class="rounded-xl bg-purple-100 p-3">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-800">Activity Description</h3>
            <p class="text-sm text-slate-500">Describe what you learned and how it contributes to your development</p>
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Description <span class="text-red-500">*</span>
        </label>
        <textarea
            name="activity_description"
            rows="6"
            required
            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-purple-500 focus:ring-4 focus:ring-purple-100 resize-none @error('activity_description') border-red-300 focus:border-red-500 focus:ring-red-100 @enderror"
            placeholder="Describe the CPD activity in detail. Include:&#10;- What you learned&#10;- Key takeaways&#10;- How it contributes to your professional development&#10;- Skills or knowledge gained"
        >{{ old('activity_description') }}</textarea>
        <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Maximum 1000 characters. Be detailed and specific.
        </p>
        @error('activity_description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
