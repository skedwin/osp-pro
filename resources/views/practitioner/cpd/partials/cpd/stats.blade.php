<!-- Statistics Cards -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Total Points Card -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:scale-[1.02]">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-600 mb-1">Total Points</p>
                <p class="text-3xl font-bold text-blue-700 mb-2">
                    {{ number_format($currentYearTotal, 1) }}
                </p>
                <p class="text-xs text-slate-500">{{ $currentYear }} Year</p>
            </div>
            <div class="rounded-xl bg-blue-500/20 p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
    </div>

    <!-- Required Points Card -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-green-50 to-emerald-100/50 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:scale-[1.02]">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-600 mb-1">Required Points</p>
                <p class="text-3xl font-bold text-green-700 mb-2">
                    {{ $requiredPoints }}
                </p>
                <p class="text-xs text-slate-500">Annual Target</p>
            </div>
            <div class="rounded-xl bg-green-500/20 p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 to-emerald-600"></div>
    </div>

    <!-- Activities Card -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-purple-50 to-purple-100/50 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:scale-[1.02]">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-600 mb-1">Activities</p>
                <p class="text-3xl font-bold text-purple-700 mb-2">
                    {{ $currentYearActivitiesCount }}
                </p>
                <p class="text-xs text-slate-500">{{ $currentYear }} Year</p>
            </div>
            <div class="rounded-xl bg-purple-500/20 p-3">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-400 to-purple-600"></div>
    </div>

    <!-- Status Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-600">Current Progress</span>
                <span class="text-sm font-semibold text-slate-900">
                    {{ number_format($currentYearTotal, 1) }} pts / {{ $requiredPoints }} pts
                </span>
            </div>
                <div class="mt-3 h-2.5 rounded-full bg-slate-200 overflow-hidden">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500"
                    style="<?php echo $progressBarWidth; ?>"
                ></div>
            </div>
            <div class="mt-2 flex justify-between text-xs text-slate-500">
                <span>{{ number_format($progressPercentage ?? 0, 1) }}% complete</span>
                <span>{{ number_format($pointsRemaining ?? 0, 1) }} pts remaining</span>
            </div>
        </div>
    </div>
</div>
