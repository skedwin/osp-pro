@props(['licenseItems'])

@php
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Pagination\Paginator;
    
    // Paginate the license items - 5 per page
    $perPage = 5;
    $currentPage = Paginator::resolveCurrentPage() ?: 1;
    $currentItems = $licenseItems instanceof \Illuminate\Pagination\AbstractPaginator 
        ? $licenseItems 
        : new LengthAwarePaginator(
            array_slice($licenseItems, ($currentPage - 1) * $perPage, $perPage),
            count($licenseItems),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
@endphp

<div class="p-5 mb-6 border border-slate-200 rounded-2xl lg:p-6">
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <h4 class="text-lg font-semibold text-slate-800">
            Licenses
        </h4>
        
        @if(count($licenseItems) > 0)
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <span>Showing {{ $currentItems->firstItem() ?? 0 }} to {{ $currentItems->lastItem() ?? 0 }} of {{ $currentItems->total() }} entries</span>
            </div>
        @endif
    </div>
    
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <!-- Table Header -->
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                    License Number
                                </p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                    Workstation
                                </p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                    Valid From
                                </p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                    Valid To
                                </p>
                            </div>
                        </th>
                        <th class="px-5 py-3 sm:px-6">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                    Status
                                </p>
                            </div>
                        </th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($currentItems as $item)
                        @php
                            $fromDate = data_get($item, 'from_date', '—');
                            $toDate = data_get($item, 'to_date', '—');
                            
                            // Determine license status using a signed days-until-expiry value
                            $status = 'Active';
                            $statusClass = 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500';
                            $daysUntilExpiry = null;

                            if ($toDate && $toDate !== '—') {
                                try {
                                    $expiryDate = \Carbon\Carbon::parse($toDate)->startOfDay();
                                    // signed difference: positive = days until expiry, negative = days since expired
                                    $daysUntilExpiry = now()->startOfDay()->diffInDays($expiryDate, false);

                                    if ($daysUntilExpiry < 0) {
                                        $status = 'Expired';
                                        $statusClass = 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500';
                                    } elseif ($daysUntilExpiry <= 60) {
                                        // 0..60 days remaining -> Expiring
                                        $status = 'Expiring';
                                        $statusClass = 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400';
                                    } else {
                                        $status = 'Active';
                                        $statusClass = 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500';
                                    }
                                } catch (\Exception $e) {
                                    // If date parsing fails, keep default status
                                }
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150">
                            <!-- License Number -->
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                        {{ data_get($item, 'license_no', '—') }}
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Workstation -->
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ data_get($item, 'workStation', '—') }}
                                    </p>
                                </div>
                            </td>
                            
                            <!-- Valid From -->
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $fromDate }}
                                    </p>
                                </div>
                            </td>
                            
                            <!-- Valid To -->
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $toDate }}
                                    </p>
                                </div>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <p class="rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $statusClass }}">
                                        {{ $status }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <!-- Empty State -->
                        <tr>
                            <td colspan="5" class="px-5 py-8 sm:px-6">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h4 class="mt-4 text-lg font-medium text-gray-600 dark:text-gray-400">No License Information</h4>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">License information will appear here when available.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($currentItems->hasPages())
            <div class="flex flex-col items-center justify-between gap-4 px-5 py-4 border-t border-gray-100 dark:border-gray-800 sm:flex-row sm:px-6">
                <!-- Showing info -->
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ $currentItems->firstItem() }} to {{ $currentItems->lastItem() }} of {{ $currentItems->total() }} results
                </div>

                <!-- Pagination Links -->
                <nav class="flex items-center gap-1">
                    <!-- Previous Page Link -->
                    @if($currentItems->onFirstPage())
                        <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Previous
                        </span>
                    @else
                        <a href="{{ $currentItems->previousPageUrl() }}" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Previous
                        </a>
                    @endif

                    <!-- Page Numbers -->
                    <div class="hidden sm:flex items-center gap-1">
                        @foreach($currentItems->getUrlRange(1, $currentItems->lastPage()) as $page => $url)
                            @if($page == $currentItems->currentPage())
                                <span class="relative inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg dark:bg-blue-500 dark:border-blue-500">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="relative inline-flex items-center px-3.5 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    </div>

                    <!-- Next Page Link -->
                    @if($currentItems->hasMorePages())
                        <a href="{{ $currentItems->nextPageUrl() }}" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                            Next
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">
                            Next
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    @endif
                </nav>
            </div>
        @endif
    </div>
</div>