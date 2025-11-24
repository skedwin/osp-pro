<!-- Activity Information Card -->
<div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
    <div class="flex items-center gap-3 mb-6">
        <div class="rounded-xl bg-blue-100 p-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-800">Activity Information</h3>
            <p class="text-sm text-slate-500">Provide details about your CPD activity</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Event Title -->
        <div class="lg:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Activity/Event Title <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                name="event_title"
                value="{{ old('event_title') }}"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('event_title') border-red-300 focus:border-red-500 focus:ring-red-100 @enderror"
                placeholder="e.g., Advanced Cardiac Life Support (ACLS) Certification Course"
            >
            @error('event_title')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- CPD Category -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                CPD Category <span class="text-red-500">*</span>
            </label>
            <select
                name="category_id"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('category_id') border-red-300 focus:border-red-500 focus:ring-red-100 @enderror"
            >
                <option value="">Select CPD Category</option>
                @foreach($cpdCategories ?? [] as $category)
                    <option value="{{ $category['category_id'] ?? $category['id'] ?? '' }}" {{ old('category_id') == ($category['category_id'] ?? $category['id'] ?? '') ? 'selected' : '' }}>
                        {{ $category['category'] ?? $category['name'] ?? 'Unknown Category' }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Event Date -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Event Date <span class="text-red-500">*</span>
            </label>
            <input
                type="date"
                name="event_date"
                value="{{ old('event_date') }}"
                required
                max="{{ date('Y-m-d') }}"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('event_date') border-red-300 focus:border-red-500 focus:ring-red-100 @enderror"
            >
            @error('event_date')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Event Location -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Event Location <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                name="event_location"
                value="{{ old('event_location') }}"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('event_location') border-red-300 focus:border-red-500 focus:ring-red-100 @enderror"
                placeholder="e.g., Nairobi Hospital, Online Webinar, Conference Center"
            >
            @error('event_location')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Duration -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Duration <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                name="duration"
                value="{{ old('duration') }}"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('duration') border-red-300 focus:border-red-500 focus:ring-red-100 @enderror"
                placeholder="e.g., 8 hours, 2 days, 1 week"
            >
            <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Specify the duration of the activity
            </p>
            @error('duration')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
