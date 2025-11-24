<!-- CPD Activities Table Section -->
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-slate-50 to-slate-100/50 px-6 py-4 border-b border-slate-200">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-800">CPD Activities & History</h3>
                <p class="text-sm text-slate-500 mt-1">View all your recorded CPD activities</p>
            </div>
            <a
                href="{{ route('practitioner.cpd.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 rounded-lg bg-blue-600 hover:bg-blue-700 hover:shadow-md active:scale-95"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Activity
            </a>
        </div>
    </div>

    @if(!empty($cpdHistory) && count($cpdHistory) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Activity/Event</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Points</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Location/Provider</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Remarks</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @foreach($cpdHistory as $activity)
                        @php
                            $status = data_get($activity, 'approval_status', 'Pending');
                            $statusColor = match(strtolower($status)) {
                                'approved' => 'green-100 text-green-800 border-green-200',
                                'rejected' => 'red-100 text-red-800 border-red-200',
                                default => 'amber-100 text-amber-800 border-amber-200'
                            };
                        @endphp
                        <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ data_get($activity, 'activity_category') }}
                                </div>
                                @if(data_get($activity, 'activity_category'))
                                    <div class="text-xs text-slate-500 mt-1 max-w-md truncate">
                                        {{ data_get($activity, 'activity', 'N/A') }}
                                    </div>
                                @endif
                                @if(data_get($activity, 'activity_evidence'))
                                    <a href="{{ data_get($activity, 'activity_evidence') }}" target="_blank" class="text-blue-600 underline text-xs mt-1 block">View Evidence</a>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                    {{ data_get($activity, 'provider', 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-700">
                                    {{ \Carbon\Carbon::parse(data_get($activity, 'activity_date', now()))->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold rounded-full bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200">
                                    {{ number_format(data_get($activity, 'points_earned', 0), 1) }} pts
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-600">
                                    {{ data_get($activity, 'activity_location', 'N/A') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-600">
                                    {{ data_get($activity, 'approval_comments', 'N/A') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-{{ $statusColor }}">
                                    {{ $status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-16 px-6">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 mb-4">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h4 class="text-xl font-bold text-slate-800 mb-2">No CPD Activities Found</h4>
            <p class="text-sm text-slate-500 mb-6 max-w-md mx-auto">
                Start tracking your professional development by reporting your first CPD activity.
            </p>
            <a
                href="{{ route('practitioner.cpd.create') }}"
                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg active:scale-95"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Report Your First Activity
            </a>
        </div>
    @endif
</div>
