@extends('layouts.app', ['title' => 'Private Practice Invoice Details'])

@section('content')
@php
    $inv = $invoice ?? ($application['invoice_details'] ?? null);
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Invoice Details</h1>
            <p class="text-sm text-slate-500">Review the invoice summary and launch the Pesaflow interface to complete payment.</p>
        </div>
        <a href="{{ route('practitioner.private-practice.invoices') }}" class="text-sm text-blue-600 hover:text-blue-500">Back to invoices</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($inv)
        @php
            $number = $inv['invoice_number'] ?? $inv['billRefNumber'] ?? 'N/A';
            $desc = $inv['invoice_desc'] ?? $inv['billDesc'] ?? 'Private Practice Application';
            $due = isset($inv['amount_due']) ? (float)$inv['amount_due'] : (isset($inv['amountExpected']) ? (float)$inv['amountExpected'] : 0);
            $paid = isset($inv['amount_paid']) ? (float)$inv['amount_paid'] : (isset($inv['amountPaid']) ? (float)$inv['amountPaid'] : 0);
            $balance = isset($inv['balance_due']) ? (float)$inv['balance_due'] : ($due - $paid);
            $currency = $inv['currency'] ?? 'KES';
            $date = $inv['invoice_date'] ?? null;
            $dateFormatted = $date ? \Carbon\Carbon::parse($date)->format('d M Y') : 'N/A';
            $action = $inv['pesaflow_url'] ?? $inv['pesaflow'] ?? '';
        @endphp

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-slate-100 p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase text-slate-400">Invoice</p>
                        <h2 class="text-xl font-semibold text-slate-900">#{{ $number }}</h2>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $balance <= 0 ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $balance <= 0 ? 'Paid' : 'Unpaid' }}
                    </span>
                </div>
                <p class="text-sm text-slate-600 mb-5">{{ $desc }}</p>

                <div class="grid grid-cols-2 gap-4 text-sm text-slate-700">
                    <div>
                        <p class="text-xs uppercase text-slate-400">Proposed Practice</p>
                        <p class="text-lg font-semibold text-slate-900">{{ $application['proposed_practice'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400">Practice Mode</p>
                        <p class="text-lg font-semibold text-slate-900">{{ $application['practice_mode'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400">County</p>
                        <p class="text-lg font-semibold text-slate-900">{{ $application['county'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400">Workstation</p>
                        <p class="text-lg font-semibold text-slate-900">{{ $application['workstation_name'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400">Amount Due</p>
                        <p class="text-lg font-semibold text-slate-900">{{ number_format($due, 2) }} {{ $currency }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400">Amount Paid</p>
                        <p class="text-lg font-semibold text-slate-900">{{ number_format($paid, 2) }} {{ $currency }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400">Balance</p>
                        <p class="text-lg font-semibold text-slate-900">{{ number_format($balance, 2) }} {{ $currency }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400">Invoice Date</p>
                        <p class="text-lg font-semibold text-slate-900">{{ $dateFormatted }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-1">Payment</h3>
                <p class="text-sm text-slate-600 mb-4">Launch Pesaflow inside the secure iframe or open in a new tab.</p>

                <form id="pesaflow_form_private_practice" method="POST" action="{{ $action }}" target="pesaflow_iframe_private_practice">
                    @php
                        $fields = [
                            'apiClientID','serviceID','billRefNumber','amount','clientMSISDN','clientEmail',
                            'clientName','clientIDNumber','secureHash','currency','notificationURL','callBackURLOnSuccess',
                            'pictureURL','billDesc','amountExpected','amount_due','total_amount'
                        ];
                    @endphp

                    @foreach($fields as $f)
                        @php
                            $snake = \Illuminate\Support\Str::snake($f);
                            $val = $inv[$f] ?? $inv[$snake] ?? null;
                        @endphp
                        @if($val !== null)
                            <input type="hidden" name="{{ $f }}" value="{{ $val }}">
                        @endif
                    @endforeach

                    <button type="button"
                            id="pesaflow_open_button_private_practice"
                            data-balance="{{ $balance }}"
                            class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600">
                        {{ $balance <= 0 ? 'Invoice Paid' : 'Click here to pay' }}
                    </button>
                </form>

                <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                    <a id="pesaflow_open_new_private_practice"
                       href="{{ $inv['pesaflow_url'] ?? $inv['pesaflow'] ?? '#' }}"
                       target="_blank"
                       class="text-blue-600 hover:text-blue-500">Open in new tab</a>
                    <button id="pesaflow_refresh_private_practice" type="button" class="text-slate-500 hover:text-slate-700">Refresh</button>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <div id="pesaflow_frame_container_private_practice" class="relative w-full overflow-hidden rounded-2xl border-2 border-slate-900">
                <div id="pesaflow_loading_private_practice" class="absolute inset-0 z-20 hidden items-center justify-center bg-slate-900/40 text-white">
                    Loading payment interface…
                </div>
                <iframe name="pesaflow_iframe_private_practice"
                        id="pesaflow_iframe_private_practice"
                        src="about:blank"
                        title="Pesaflow Payment"
                        class="w-full border-0"
                        style="min-height: 800px; height: 85vh;"
                        allowfullscreen
                        loading="lazy"></iframe>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Invoice data not available.
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const iframe = document.getElementById('pesaflow_iframe_private_practice');
        const overlay = document.getElementById('pesaflow_loading_private_practice');
        const refreshBtn = document.getElementById('pesaflow_refresh_private_practice');
        const openBtn = document.getElementById('pesaflow_open_new_private_practice');
        const payBtn = document.getElementById('pesaflow_open_button_private_practice');
        const form = document.getElementById('pesaflow_form_private_practice');

        const showOverlay = (show) => {
            if (!overlay) return;
            overlay.style.display = show ? 'flex' : 'none';
        };

        if (payBtn && form) {
            payBtn.addEventListener('click', () => {
                const balance = parseFloat(payBtn.dataset.balance || '0');
                if (balance <= 0) {
                    alert('This invoice is already paid.');
                    return;
                }
                showOverlay(true);
                form.submit();
                setTimeout(() => {
                    showOverlay(false);
                    if (iframe) {
                        iframe.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 1500);
            });
        }

        if (refreshBtn && iframe) {
            refreshBtn.addEventListener('click', () => {
                iframe.src = iframe.src;
            });
        }

        if (openBtn) {
            openBtn.addEventListener('click', (e) => {
                if (payBtn) {
                    const balance = parseFloat(payBtn.dataset.balance || '0');
                    if (balance <= 0) {
                        e.preventDefault();
                        alert('This invoice is already paid.');
                        return false;
                    }
                }
            });
        }
    });
</script>
@endpush
