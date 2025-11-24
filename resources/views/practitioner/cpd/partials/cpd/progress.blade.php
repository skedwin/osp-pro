<!-- Progress Overview Section -->
<div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
    <div class="mb-6">
        <h3 class="text-xl font-bold text-slate-800 mb-2">
            Progress Overview - {{ $currentYear }}
        </h3>
        <p class="text-sm text-slate-500">Track your CPD progress and monthly trends throughout the year</p>
    </div>
    
    <div class="space-y-6">
        <!-- Main Progress Bar -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold text-slate-800">
                        {{ number_format($currentYearTotal, 1) }}
                    </span>
                    <span class="text-sm text-slate-500">of</span>
                    <span class="text-lg font-bold text-slate-800">
                        {{ $requiredPoints }}
                    </span>
                    <span class="text-sm text-slate-500">points</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span class="text-sm font-semibold text-blue-700">
                        {{ number_format($progressPercentage, 1) }}%
                    </span>
                </div>
            </div>
            <div class="relative w-full h-4 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                <div 
                    class="absolute top-0 left-0 h-full rounded-full bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 transition-all duration-1000 ease-out shadow-lg"
                    style="<?php echo $progressBarWidth; ?>"
                >
                    <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                </div>
            </div>
        </div>

        <!-- Progress Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Monthly Progress Trend Card -->
            <div class="lg:col-span-2 bg-slate-50 rounded-xl p-6 border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-slate-800">Monthly Progress Trend</h4>
                    <div class="flex items-center gap-4 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span>Your Progress</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-slate-300 rounded-full"></div>
                            <span>Expected</span>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="flex items-end justify-between h-40 gap-1 pb-8 border-b border-slate-200">
                        @for($month = 1; $month <= 12; $month++)
                            @php
                                $monthProgress = $cumulativeProgress[$month] ?? 0;
                                $yourProgressHeight = $monthlyYourHeights[$month] ?? 0;
                                $expectedProgressHeight = $monthlyExpectedHeights[$month] ?? 0;
                                $isCurrentMonth = $month == $currentMonth;
                                $isFutureMonth = $month > $currentMonth;
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div 
                                        class="w-full rounded-t-lg transition-all duration-500 ease-out relative group"
                                        style="<?php echo $monthlyYourStyles[$month] ?? ''; ?>"
                                        title="{{ $monthNames[$month-1] }}: {{ number_format($monthProgress, 1) }} points"
                                    >
                                    @if($yourProgressHeight > 0)
                                        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 hidden group-hover:block bg-slate-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
                                            {{ number_format($monthProgress, 1) }} pts
                                        </div>
                                    @endif
                                </div>
                                
                                <div 
                                    class="w-full rounded-t-lg bg-slate-300/50 transition-all duration-500 ease-out"
                                    style="<?php echo $monthlyExpectedStyles[$month] ?? ''; ?>"
                                ></div>
                                
                                <div class="text-xs font-medium mt-2 {{ $isCurrentMonth ? 'text-blue-600 font-bold' : ($isFutureMonth ? 'text-slate-400' : 'text-slate-600') }}">
                                    {{ $monthNames[$month-1] }}
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Progress Metrics Sidebar -->
            <div class="space-y-6">
                <!-- Progress Status Card -->
                <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 rounded-xl p-6 border border-slate-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="rounded-lg bg-blue-100 p-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h5 class="font-semibold text-slate-800">Progress Status</h5>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Current Progress:</span>
                            <span class="text-sm font-semibold text-slate-800">{{ number_format($currentYearTotal, 1) }} pts</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Expected by Now:</span>
                            <span class="text-sm font-semibold text-slate-800">{{ number_format($expectedProgressToDate, 1) }} pts</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Status:</span>
                            <span class="text-sm font-semibold {{ $aheadOfSchedule ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $aheadOfSchedule ? 'Ahead' : 'Behind' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Activity Card -->
                <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 rounded-xl p-6 border border-slate-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="rounded-lg bg-purple-100 p-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h5 class="font-semibold text-slate-800">Monthly Activity</h5>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Total Activities:</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $currentYearActivitiesCount }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">{{ $monthNames[$currentMonth-1] }} Activities:</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $monthlyActivities[$currentMonth] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Monthly Average:</span>
                            <span class="text-sm font-semibold text-slate-800">
                                {{ $currentYearActivitiesCount > 0 ? number_format($currentYearActivitiesCount / max($currentMonth, 1), 1) : 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Message Card -->
        @if($pointsRemaining > 0)
            <div class="flex items-start gap-4 p-6 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200">
                <div class="flex-shrink-0 rounded-full bg-amber-100 p-3">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-amber-900 mb-2">
                        {{ $aheadOfSchedule ? 'Good Progress!' : 'Keep Going!' }}
                    </h4>
                    <p class="text-sm text-amber-800">
                        @if($aheadOfSchedule)
                            You're ahead of schedule! You need <span class="font-bold">{{ number_format($pointsRemaining, 1) }}</span> more points.
                        @else
                            You need <span class="font-bold">{{ number_format($pointsRemaining, 1) }}</span> more points. Consider increasing your CPD activities.
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-emerald-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-emerald-900">Congratulations!</h4>
                        <p class="mt-1 text-sm text-emerald-800 leading-relaxed">
                            You’ve successfully met the annual CPD requirement with 
                            <span class="font-semibold">{{ number_format($currentYearActivitiesCount) }}</span> activities. 
                            Keep up the excellent work!
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
