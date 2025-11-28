@extends('layouts.app', ['title' => 'Private Practice Invoices'])

@section('content')
@php
    $invoicePayload = $invoice_payload ?? session('private_practice_invoice_payload');
    $applicationsCollection = $applications ?? [];
    $appsArray = (is_object($applicationsCollection) && method_exists($applicationsCollection, 'items'))
        ? $applicationsCollection->items()
        : $applicationsCollection;
    $recentInvoice = $appsArray[0]['invoice_details'] ?? $appsArray[0] ?? null;
    if (!$recentInvoice && $invoicePayload) {
        $recentInvoice = $invoicePayload['invoice_details'] ?? $invoicePayload;
    }
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-400">Payments</p>
            <h1 class="text-2xl font-bold text-slate-900">Private Practice Invoices</h1>
            <p class="text-sm text-slate-500 mt-1">Review your private practice applications and payments.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('practitioner.private-practice') }}"
               class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Application
            </a>
        </div>
    </div>

    @if($recentInvoice)
        @php
            $inv = $recentInvoice;
            $amountDue = isset($inv['amount_due']) ? (float)$inv['amount_due'] : (isset($inv['amountExpected']) ? (float)$inv['amountExpected'] : 0);
            $amountPaid = isset($inv['amount_paid']) ? (float)$inv['amount_paid'] : (isset($inv['amountPaid']) ? (float)$inv['amountPaid'] : 0);
            $balance = isset($inv['balance_due']) ? (float)$inv['balance_due'] : ($amountDue - $amountPaid);
            $currency = $inv['currency'] ?? 'KES';
            $invoiceDate = $inv['invoice_date'] ?? null;
            $invoiceDateFormatted = $invoiceDate ? \Carbon\Carbon::parse($invoiceDate)->format('d M Y') : 'N/A';
            $recentIsPaid = $balance <= 0;
            $recentId = $inv['billRefNumber'] ?? $inv['invoice_number'] ?? null;
        @endphp
        <div class="mb-8 rounded-2xl border border-slate-100 bg-gradient-to-r from-blue-50 to-white p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-blue-500 font-semibold">Most Recent</p>
                    <h2 class="text-xl font-semibold text-slate-900 mb-1">Invoice {{ $inv['invoice_number'] ?? $inv['billRefNumber'] ?? '' }}</h2>
                    <p class="text-sm text-slate-600 mb-2">{{ $inv['invoice_desc'] ?? $inv['billDesc'] ?? 'Private Practice' }}</p>
                    <div class="grid grid-cols-2 gap-4 text-sm text-slate-600">
                        <div>
                            <p class="text-xs uppercase text-slate-400">Amount Due</p>
                            <p class="font-semibold text-slate-900">{{ number_format($amountDue, 2) }} {{ $currency }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-slate-400">Balance</p>
                            <p class="font-semibold text-slate-900">{{ number_format($balance, 2) }} {{ $currency }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-slate-400">Invoice Date</p>
                            <p>{{ $invoiceDateFormatted }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-slate-400">Status</p>
                            <p class="{{ $recentIsPaid ? 'text-green-600 font-semibold' : 'text-amber-600 font-semibold' }}">
                                {{ $recentIsPaid ? 'Paid' : 'Unpaid' }}
                            </p>
                        </div>
                    </div>
                </div>
            <div>
                <div class="flex gap-3">
                    @if($recentId)
                        <a href="{{ route('practitioner.private-practice.invoices.show', ['id' => $recentId]) }}"
                           class="inline-flex items-center justify-center px-5 py-2.5 font-semibold text-white rounded-lg bg-brand-500 text-sm hover:bg-brand-600">
                            {{ $recentIsPaid ? 'View Receipt' : 'Pay Now' }}
                        </a>
                    @else
                        <button type="button"
                                class="inline-flex items-center justify-center rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-500 cursor-not-allowed"
                                disabled>
                            Payment link unavailable
                        </button>
                    @endif
                </div>
            </div>
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-100">
        <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-800">All Private Practice Applications</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Invoice</th>
                        <th class="px-4 py-3 text-left font-semibold">County</th>
                        <th class="px-4 py-3 text-left font-semibold">Renewal Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Amount</th>
                        <th class="px-4 py-3 text-left font-semibold">Paid</th>
                        <th class="px-4 py-3 text-left font-semibold">Balance</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Workstation</th>
                        <th class="px-4 py-3 text-left font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($applicationsCollection as $app)
                        @php
                            $inv = $app['invoice_details'] ?? $app;
                            $invoiceId = $inv['billRefNumber'] ?? $inv['invoice_number'] ?? null;
                            $amountDue = isset($inv['amount_due']) ? (float)$inv['amount_due'] : (isset($inv['amountExpected']) ? (float)$inv['amountExpected'] : 0);
                            $amountPaid = isset($inv['amount_paid']) ? (float)$inv['amount_paid'] : (isset($inv['amountPaid']) ? (float)$inv['amountPaid'] : 0);
                            $balanceDue = isset($inv['balance_due']) ? (float)$inv['balance_due'] : ($amountDue - $amountPaid);
                            $status = $balanceDue <= 0 ? 'Paid' : 'Unpaid';
                            $invoiceDate = $inv['invoice_date'] ?? null;
                            $invoiceDateFormatted = $invoiceDate ? \Carbon\Carbon::parse($invoiceDate)->format('d M Y') : 'N/A';
                        @endphp
                        <tr class="text-slate-700">
                            <td class="px-4 py-3 font-semibold">{{ $inv['invoice_number'] ?? $inv['billRefNumber'] ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $app['county'] ?? '—' }}</td>
                            <td class="px-4 py-3">{{ isset($app['renewal_date']) ? \Carbon\Carbon::parse($app['renewal_date'])->format('d M Y, H:i') : 'N/A' }}</td>
                            <td class="px-4 py-3">{{ number_format($amountDue, 2) }} {{ $inv['currency'] ?? 'KES' }}</td>
                            <td class="px-4 py-3">{{ number_format($amountPaid, 2) }} {{ $inv['currency'] ?? 'KES' }}</td>
                            <td class="px-4 py-3">{{ number_format($balanceDue, 2) }} {{ $inv['currency'] ?? 'KES' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $status === 'Paid' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $app['workstation_name'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($invoiceId)
                                    <a href="{{ route('practitioner.private-practice.invoices.show', ['id' => $invoiceId]) }}"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</a>
                                @else
                                    <span class="rounded-lg border border-dashed border-slate-200 px-3 py-1.5 text-xs text-slate-400">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-sm text-slate-500">No private practice applications found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(is_object($applicationsCollection) && method_exists($applicationsCollection, 'hasPages') && $applicationsCollection->hasPages())
            <div class="flex flex-col gap-4 px-4 py-4 border-t border-slate-100 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    Showing {{ $applicationsCollection->firstItem() }} to {{ $applicationsCollection->lastItem() }} of {{ $applicationsCollection->total() }} results
                </div>
                <div>
                    {{ $applicationsCollection->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
