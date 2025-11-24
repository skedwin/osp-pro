@props(['icon', 'title', 'description'])

<div class="p-8 mb-6 text-center border border-dashed border-slate-300 rounded-2xl">
    <svg class="w-12 h-12 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
    </svg>
    <h4 class="mt-4 text-lg font-medium text-slate-600">{{ $title }}</h4>
    <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>
</div>