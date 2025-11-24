@props([
    'title' => 'Declaration',
    'subtitle' => 'Important information about your renewal',
    'items' => []
])

<div {{ $attributes->merge(['class' => 'my-6 rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-6 shadow-sm']) }}>
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <div class="rounded-xl bg-amber-100 p-3 shadow-inner">
            <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <div>
            <h4 class="text-lg font-semibold text-amber-900">{{ $title }}</h4>
            <p class="text-sm text-amber-800">{{ $subtitle }}</p>
        </div>
    </div>

    <!-- Body -->
    <div class="space-y-5">
        @forelse($items as $item)
            <div class="flex items-start gap-3">
                <div class="mt-1">
                    <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-900 mb-1">{{ $item['title'] }}</p>
                    <p class="text-sm text-amber-800 leading-relaxed">{{ $item['description'] }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-amber-800">No declarations available.</p>
        @endforelse
    </div>
</div>
