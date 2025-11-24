@extends('layouts.app', ['title' => 'Invoice Details'])

@section('content')
@php
    // $invoice is provided by controller
    $inv = $invoice ?? ($application['invoice_details'] ?? null);
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Invoice Details</h1>
        <a href="{{ route('practitioner.invoices') }}" class="text-sm text-blue-600">Back to Invoices</a>
    </div>

    @if(session('error'))
        <div class="mb-4 p-3 rounded bg-red-50 border border-red-200">{{ session('error') }}</div>
    @endif

    @if($inv)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="col-span-2">
                @php
                    $inv = $inv ?? ($invoice ?? []);
                    // normalize keys
                    $number = $inv['invoice_number'] ?? $inv['billRefNumber'] ?? 'N/A';
                    $desc = $inv['invoice_desc'] ?? $inv['billDesc'] ?? 'Practice Renewal';
                    $due = isset($inv['amount_due']) ? (float)$inv['amount_due'] : (isset($inv['amountExpected']) ? (float)$inv['amountExpected'] : 0);
                    $paid = isset($inv['amount_paid']) ? (float)$inv['amount_paid'] : (isset($inv['amountPaid']) ? (float)$inv['amountPaid'] : 0);
                    $balance = isset($inv['balance_due']) ? (float)$inv['balance_due'] : ($due - $paid);
                    $currency = $inv['currency'] ?? 'KES';
                    $date = $inv['invoice_date'] ?? null;
                    $dateFormatted = $date ? \Carbon\Carbon::parse($date)->format('d M Y') : 'N/A';
                @endphp

                <div class="rounded-lg border p-4 bg-white">
                    <h3 class="text-lg font-semibold mb-3">Invoice #{{ $number }}</h3>
                    <div class="text-sm text-slate-700 mb-2">{{ $desc }}</div>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><strong>Amount Due:</strong>  {{ number_format($due, 2) }} {{ $currency }}</div>
                        <div><strong>Amount Paid:</strong>  {{ number_format($paid, 2) }} {{ $currency }}</div>
                        <div><strong>Balance:</strong>  {{ number_format($balance, 2) }} {{ $currency }}</div>
                        <div><strong>Invoice Date:</strong>  {{ $dateFormatted }}</div>
                    </div>

                    <div class="mt-4">
                        @if($balance <= 0)
                            <span class="inline-block px-3 py-1 rounded bg-green-100 text-green-700 text-sm"><strong>Status:</strong> Paid</span>
                        @else
                            <span class="inline-block px-3 py-1 rounded bg-amber-100 text-amber-700 text-sm"><strong>Status:</strong> Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                @php
                    $inv = $inv ?? ($invoice ?? []);
                    $action = $inv['pesaflow_url'] ?? $inv['pesaflow'] ?? '';
                @endphp

                <div class="rounded-lg border p-4 bg-white">
                    <h3 class="text-lg font-semibold mb-3">Payment</h3>

                    <form id="pesaflow_form_details" method="POST" action="{{ $action }}" target="pesaflow_iframe_details">
                        @php
                            $fields = ['apiClientID','serviceID','billRefNumber','amount','clientMSISDN','clientEmail','clientName','clientIDNumber','secureHash','currency','notificationURL','callBackURLOnSuccess','pictureURL','billDesc','amountExpected','amount_due','total_amount'];
                        @endphp

                        @foreach($fields as $f)
                            @php
                                $snake = \Illuminate\Support\Str::snake($f);
                                $val = $inv[$f] ?? $inv[$snake] ?? null;
                            @endphp
                            @if($val !== null)
                                <input type="hidden" name="{{ $f }}" value="{{ $val }}" />
                            @endif
                        @endforeach

                        <div class="text-sm text-slate-600 mb-3">Click the button below to open the payment interface.</div>
                        <div class="flex gap-2">
                            <button id="pesaflow_open_button_details" type="button" data-balance="{{ $balance }}" class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600">Click here to pay</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <div class="mb-3 flex items-center justify-end gap-2">
                <a id="pesaflow_open_new_details" href="{{ $inv['pesaflow_url'] ?? $inv['pesaflow'] ?? '#' }}" target="_blank" rel="noopener" class="px-3 py-1 text-sm bg-slate-100 rounded">Open in new tab</a>
                <button id="pesaflow_refresh_details" type="button" class="px-3 py-1 text-sm bg-slate-100 rounded">Refresh</button>
            </div>

            <div id="pesaflow_frame_container_details" class="relative rounded-md overflow-hidden border-2 border-black">
                <div id="pesaflow_loading_details" style="display:none;" class="absolute inset-0 z-20 flex items-center justify-center bg-black/30 backdrop-blur-sm">
                    <div class="text-white text-sm">Loading payment interface…</div>
                </div>
                <iframe name="pesaflow_iframe_details" id="pesaflow_iframe_details" class="w-full" src="about:blank" title="Payment" allowfullscreen loading="lazy" style="min-height:600px; height:80vh; border:0;"></iframe>
            </div>
        </div>
    @else
        <div class="p-4 bg-yellow-50 border border-amber-200 rounded">Invoice data not available.</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        // Wire loading overlay and refresh for details iframe
        const iframe = document.getElementById('pesaflow_iframe_details');
        const overlay = document.getElementById('pesaflow_loading_details');
        const refreshBtn = document.getElementById('pesaflow_refresh_details');
        const openBtn = document.getElementById('pesaflow_open_new_details');
        const payBtn = document.getElementById('pesaflow_open_button_details');
        const form = document.getElementById('pesaflow_form_details');

        // Helper to show/hide overlay safely
        const showOverlay = (show) => { try { if (overlay) overlay.style.display = show ? 'flex' : 'none'; } catch(e){} };

        if (iframe && overlay) {
            // hide overlay initially
            showOverlay(false);
            iframe.addEventListener('load', function(){ showOverlay(false); });
            setTimeout(()=>{ try { if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') showOverlay(false); } catch(e){} }, 1000);
        }

        if (refreshBtn) refreshBtn.addEventListener('click', ()=>{ if (iframe) { showOverlay(true); try { iframe.contentWindow.location.reload(); } catch(e){ iframe.src = iframe.src; } } });

        if (payBtn && form) {
            payBtn.addEventListener('click', function(){
                    // Prevent payment flow when invoice is already paid
                    try {
                        const balanceStr = (payBtn.getAttribute('data-balance') || '').toString();
                        const bal = parseFloat(balanceStr === '' ? '0' : balanceStr);
                        if (!isNaN(bal) && bal <= 0) {
                            // invoice already paid
                            try { showToast('Invoice already paid.', 'info', 6000); } catch(e){ alert('Invoice already paid.'); }
                            return;
                        }
                    } catch(e) {
                        console.warn('Could not parse balance for paid-check', e);
                    }

                try {
                    const action = (form.getAttribute('action') || '').trim();
                    if (!action || action === window.location.href) {
                        alert('Payment URL is not available. Please refresh the page or contact support.');
                        return;
                    }

                    // show loading overlay
                    showOverlay(true);

                    // submit into iframe
                    try { form.submit(); } catch(e) { console.error('pesaflow submit failed', e); }

                    // fallback: if iframe doesn't load within timeout open in new tab
                    let fallbackTimer = setTimeout(function(){
                        console.warn('Pesaflow: iframe did not load within timeout, opening payment in new tab as fallback.');
                        showToast('Payment iframe did not respond — opening payment in a new tab.', 'info', 8000);

                        const newForm = document.createElement('form');
                        newForm.method = 'POST';
                        newForm.action = action;
                        newForm.target = '_blank';
                        newForm.style.display = 'none';

                        Array.from(form.elements).forEach(el => {
                            if (!el.name) return;
                            const clone = document.createElement('input');
                            clone.type = 'hidden';
                            clone.name = el.name;
                            clone.value = el.value;
                            newForm.appendChild(clone);
                        });

                        document.body.appendChild(newForm);
                        try { newForm.submit(); } catch(e){ console.error('Fallback submit failed', e); }
                        try { document.body.removeChild(newForm); } catch(e){}
                        showOverlay(false);
                    }, 7000);

                    // Clear fallback on iframe load
                    const onLoad = function(){ try { clearTimeout(fallbackTimer); } catch(e){}; showOverlay(false); };
                    if (iframe) iframe.addEventListener('load', onLoad, { once: true });

                } catch(e) {
                    console.error('pesaflow init error', e);
                    showOverlay(false);
                }
            });
        }

        if (openBtn) openBtn.addEventListener('click', ()=>{});
    });
</script>
@if(session('payment_success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var msg = "{{ addslashes(session('payment_success')) }}";
            var url = "{{ route('practitioner.invoices') }}";
            showToast(msg, 'success');
            setTimeout(function() {
                window.location.href = url;
            }, 2000);
        });
    </script>
@endif
@endpush
