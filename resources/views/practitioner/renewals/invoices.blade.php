@extends('layouts.app', ['title' => 'Invoice'])

@section('content')
@php
    $invoicePayload = session('invoice_payload', null) ?? ($invoice_payload ?? null);
    $applications = $applications ?? [];

    // Prepare a plain array to pick the recent invoice when controller used server-side pagination
    $appsArray = (is_object($applications) && method_exists($applications, 'items')) ? $applications->items() : $applications;
    $recentInvoice = $appsArray[0] ?? null;
    if ($recentInvoice) {
        $recentInvoice = $recentInvoice['invoice_details'] ?? $recentInvoice;
    }

    // Normalize invoice_details if nested
    $invoice = null;
    if ($invoicePayload) {
        if (isset($invoicePayload['invoice_details'])) {
            $invoice = $invoicePayload['invoice_details'];
        } else {
            // Sometimes payload is the invoice object directly
            $invoice = $invoicePayload;
        }
    }
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 shadow-sm">
    @include('practitioner.renewals.partials.invoices.header')

    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-50 border border-green-200">{{ session('success') }}</div>
    @endif

    {{-- licenses will be displayed below the invoices table --}}

    {{-- recent invoice already prepared above (controller sorts/paginates) --}}

    @if($recentInvoice)
    @php
        $rDue = isset($recentInvoice['amount_due']) ? (float)$recentInvoice['amount_due'] : (isset($recentInvoice['amountDue']) ? (float)$recentInvoice['amountDue'] : 0);
        $rPaid = isset($recentInvoice['amount_paid']) ? (float)$recentInvoice['amount_paid'] : (isset($recentInvoice['amountPaid']) ? (float)$recentInvoice['amountPaid'] : 0);
        $rBalance = isset($recentInvoice['balance_due']) ? (float)$recentInvoice['balance_due'] : (isset($recentInvoice['balanceDue']) ? (float)$recentInvoice['balanceDue'] : ($rDue - $rPaid));
        $recentIsPaid = ($rBalance <= 0 && $rDue > 0);
    @endphp
    <div class="rounded-lg border p-6 mb-6 bg-gradient-to-r from-blue-50 to-white shadow-md">
        <h3 class="font-semibold text-lg mb-4 text-blue-700">Recent Invoice</h3>
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                <p class="mb-1"><strong>Invoice No:</strong> {{ $recentInvoice['invoice_number'] ?? $recentInvoice['billRefNumber'] ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Description:</strong> {{ $recentInvoice['invoice_desc'] ?? $recentInvoice['billDesc'] ?? 'Practice Renewal' }}</p>
                <p class="mb-1"><strong>Amount Due:</strong> {{ number_format($rDue, 2) }} {{ $recentInvoice['currency'] ?? 'KES' }}</p>
                <p class="mb-1"><strong>Invoice Date:</strong> {{ isset($recentInvoice['invoice_date']) ? \Carbon\Carbon::parse($recentInvoice['invoice_date'])->format('d M Y') : 'N/A' }}</p>
                @if($recentIsPaid)
                    <p class="mt-2 text-sm text-green-600 font-medium">Status: Paid</p>
                @else
                    <p class="mt-2 text-sm text-red-600 font-medium">Status: Unpaid</p>
                @endif
            </div>
            <div>
                @if($recentIsPaid)
                    <button type="button" disabled class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600" aria-disabled="true">Paid</button>
                @else
                    <a href="{{ route('practitioner.invoices.show', ['id' => $recentInvoice['invoice_number'] ?? $recentInvoice['billRefNumber']]) }}" class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600" aria-label="View recent invoice">Click Here to Pay</a>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- If we have applications list, render table of invoices --}}
    @if(!empty($applications) && (is_array($applications) ? count($applications) > 0 : (is_object($applications) && method_exists($applications, 'count') ? $applications->count() > 0 : false)))
        <div class="rounded-lg border p-4 mb-6 bg-white">
            <h3 class="font-semibold mb-3">All Applications / Invoices</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="p-2">#</th>
                            <th class="p-2">Invoice No</th>
                            <th class="p-2">Description</th>
                            <th class="p-2">Invoice Date</th>
                            <th class="p-2">Amount Due</th>
                            <th class="p-2">Amount Paid</th>
                            <th class="p-2">Amount Balance</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="invoices_table_body">
                        @foreach($applications as $idx => $app)
                            @php
                                $inv = $app['invoice_details'] ?? $app;
                                $invoiceId = $app['application_id'] ?? $app['applicationID'] ?? $inv['billRefNumber'] ?? $inv['invoice_number'] ?? $idx;
                                $amountDue = isset($inv['amount_due']) ? (float)$inv['amount_due'] : 0;
                                $amountPaid = isset($inv['amount_paid']) ? (float)$inv['amount_paid'] : 0;
                                $balanceDue = isset($inv['balance_due']) ? (float)$inv['balance_due'] : ($amountDue - $amountPaid);
                                $status = ($balanceDue <= 0) ? 'Paid' : 'Unpaid';
                                $invoiceDate = $inv['invoice_date'] ?? null;
                                $invoiceDateFormatted = $invoiceDate ? \Carbon\Carbon::parse($invoiceDate)->format('d M Y') : 'N/A';
                            @endphp
                            <tr class="invoice-row border-t" data-row-index="{{ $idx }}">
                                <td class="p-2 align-top">{{ $idx + 1 }}</td>
                                <td class="p-2 align-top">{{ $inv['invoice_number'] ?? $inv['billRefNumber'] ?? 'N/A' }}</td>
                                <td class="p-2 align-top">{{ $inv['invoice_desc'] ?? $inv['billDesc'] ?? 'Practice Renewal' }}</td>
                                <td class="p-2 align-top">{{ $invoiceDateFormatted }}</td>
                                <td class="p-2 align-top">{{ number_format($amountDue, 2) }} {{ $inv['currency'] ?? 'KES' }}</td>
                                <td class="p-2 align-top">{{ number_format($amountPaid, 2) }} {{ $inv['currency'] ?? 'KES' }}</td>
                                <td class="p-2 align-top">{{ number_format($balanceDue, 2) }} {{ $inv['currency'] ?? 'KES' }}</td>
                                <td class="p-2 align-top">{{ $status }}</td>
                                <td class="p-2 align-top">
                                    <a href="{{ route('practitioner.invoices.show', ['id' => $invoiceId]) }}" class="mr-2 px-3 py-1 bg-blue-500 text-green-500 rounded" aria-label="View invoice {{ $inv['invoice_number'] ?? $inv['billRefNumber'] ?? '' }}">View</a>
                                    <button type="button" data-idx="{{ $idx }}" class="px-3 py-1 bg-slate-100 text-slate-800 rounded" onclick="printInvoiceFromList(this.dataset.idx)">Print</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- Server-side pagination controls (styled like license-card) --}}
                @if(is_object($applications) && method_exists($applications, 'lastPage') && $applications->lastPage() > 1)
                    <div class="flex flex-col items-center justify-between gap-4 px-5 py-4 border-t border-gray-100 sm:flex-row sm:px-6 mt-3">
                        <div class="text-sm text-gray-500">
                            Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} of {{ $applications->total() }} results
                        </div>

                        <nav id="invoices_pager" class="flex items-center gap-1" role="navigation" aria-label="Invoices pagination">
                            @if($applications->onFirstPage())
                                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default rounded-lg">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Previous
                                </span>
                            @else
                                <a href="{{ $applications->previousPageUrl() }}" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50" aria-label="Previous page">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Previous
                                </a>
                            @endif

                            <div class="hidden sm:flex items-center gap-1">
                                @foreach($applications->getUrlRange(1, $applications->lastPage()) as $page => $url)
                                    @if($page == $applications->currentPage())
                                        <span class="relative inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="relative inline-flex items-center px-3.5 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">{{ $page }}</a>
                                    @endif
                                @endforeach
                            </div>

                            @if($applications->hasMorePages())
                                <a href="{{ $applications->nextPageUrl() }}" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50" aria-label="Next page">
                                    Next
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default rounded-lg">Next <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                            @endif
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Licenses (copied from profile logic) --}}
    @php
        // Pull licenses from session bio_profile (same shape as used on profile page)
        $licenseItems = data_get(session('bio_profile', []), 'license', []);
    @endphp

    @if(!empty($licenseItems))
        <x-profile.license-card :licenseItems="$licenseItems" />
    @else
        <x-profile.empty-state 
            icon="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
            title="No License Information"
            description="License information will appear here when available." />
    @endif

@endsection

@push('scripts')
<script>
    // Auto-submit the form into the iframe when the page loads
    document.addEventListener('DOMContentLoaded', function(){
        const form = document.getElementById('pesaflow_form');
        if (form) {
            // Submit after a short delay so the iframe is ready
            setTimeout(()=>{
                try { form.submit(); } catch(e){ console.error('Auto-submit failed', e); }
            }, 300);
        }
        // Wire iframe loading overlay handling
        const iframe = document.getElementById('pesaflow_iframe');
        const overlay = document.getElementById('pesaflow_loading');
        const refreshBtn = document.getElementById('pesaflow_refresh');
        const openBtn = document.getElementById('pesaflow_open_new');
        if (iframe && overlay) {
            iframe.addEventListener('load', function(){
                // Hide overlay when iframe content loads
                overlay.style.display = 'none';
            });
            // In case the iframe already has a src and loaded
            setTimeout(()=>{ if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') overlay.style.display = 'none'; }, 1000);
        }
        if (refreshBtn) refreshBtn.addEventListener('click', ()=>{ if (iframe) { overlay.style.display = 'flex'; iframe.contentWindow.location.reload(); } });
        if (openBtn) openBtn.addEventListener('click', ()=>{ /* best-effort open in new tab */ });
    });
</script>
<script>
    // Fetch applications from server to avoid embedding large JSON in the Blade template
    window.__applications = [];

    document.addEventListener('DOMContentLoaded', function(){
        // attempt to load applications for client-side actions
        fetch("{{ route('practitioner.applications') }}", { credentials: 'same-origin' })
            .then(r => r.json())
            .then(json => {
                // expected shape: { applications: [...] } or an array directly
                if (Array.isArray(json)) {
                    window.__applications = json;
                } else if (json && Array.isArray(json.applications)) {
                    window.__applications = json.applications;
                } else if (json && Array.isArray(json.data)) {
                    window.__applications = json.data;
                } else {
                    // fallback - try to use json as-is
                    window.__applications = json || [];
                }
            })
            .catch(err => {
                console.error('Failed to load applications', err);
                window.__applications = [];
            });
    });

    function loadInvoiceFromList(idx) {
        idx = parseInt(idx, 10);
        if (!window.__applications || !window.__applications[idx]) return;
        const app = window.__applications[idx];
        const inv = app.invoice_details || app;

        // Populate the form if present, otherwise build one
        let form = document.getElementById('pesaflow_form');
        if (!form) {
            form = document.createElement('form');
            form.id = 'pesaflow_form';
            form.method = 'POST';
            form.target = 'pesaflow_iframe';
            document.body.appendChild(form);
        }
        // Clear existing inputs
        Array.from(form.querySelectorAll('input[type=hidden]')).forEach(i => i.remove());

        const fields = {
            apiClientID: inv.apiClientID || inv.apiClientId,
            serviceID: inv.serviceID || inv.serviceId,
            billRefNumber: inv.billRefNumber || inv.bill_ref_number || inv.invoice_number,
            amount: inv.amountExpected || inv.total_amount || inv.amountExpected,
            clientMSISDN: inv.clientMSISDN || inv.client_msisdn,
            clientEmail: inv.clientEmail || inv.client_email,
            clientName: inv.clientName || inv.client_name,
            clientIDNumber: inv.clientIDNumber || inv.client_id_number,
            secureHash: inv.secureHash || inv.secure_hash,
            currency: inv.currency || 'KES',
            notificationURL: inv.notificationURL || inv.notification_url,
            callBackURLOnSuccess: inv.callBackURLOnSuccess || inv.callBackUrlOnSuccess || inv.callBackURL,
            pictureURL: inv.pictureURL || inv.picture_url,
            billDesc: inv.billDesc || inv.invoice_desc,
            convenience_fee: inv.convenience_fee || inv.convenienceFee
        };

        Object.keys(fields).forEach(k => {
            const v = fields[k];
            if (v === undefined || v === null) return;
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = k; input.value = String(v);
            form.appendChild(input);
        });

        // Set action url
        form.action = inv.pesaflow_url || inv.pesaflow || '';

    // Submit and load into iframe
    try { form.submit(); } catch(e) { console.error('submit failed', e); }

        // Scroll to iframe
        const iframe = document.getElementById('pesaflow_iframe');
        if (iframe) iframe.scrollIntoView({ behavior: 'smooth' });
    }

    function printInvoiceFromList(idx) {
        idx = parseInt(idx, 10);
        if (!window.__applications || !window.__applications[idx]) return;
        const app = window.__applications[idx];
        const inv = app.invoice_details || app;
        const html = `
            <html><head><title>Invoice ${inv.invoice_number || inv.billRefNumber}</title></head>
            <body>
                <h2>Invoice: ${inv.invoice_number || inv.billRefNumber || ''}</h2>
                <p>${inv.invoice_desc || inv.billDesc || ''}</p>
                <p>Amount: ${inv.amountExpected || inv.total_amount || ''} ${inv.currency || 'KES'}</p>
                <p>Client: ${inv.clientName || inv.client_name || ''}</p>
            </body></html>`;
        const w = window.open('', '_blank');
        w.document.open();
        w.document.write(html);
        w.document.close();
        w.focus();
        setTimeout(()=>{ w.print(); }, 500);
    }

    // Accessibility: add keyboard navigation for server-side pager (left/right arrows)
    document.addEventListener('DOMContentLoaded', function(){
        const pager = document.getElementById('invoices_pager');
        if (!pager) return;
        pager.addEventListener('keydown', function(e){
            if (e.key === 'ArrowLeft') {
                const prev = pager.querySelector('a[aria-label="Previous page"]');
                if (prev && !prev.hasAttribute('aria-disabled')) { prev.focus(); }
            } else if (e.key === 'ArrowRight') {
                const next = pager.querySelector('a[aria-label="Next page"]');
                if (next && !next.hasAttribute('aria-disabled')) { next.focus(); }
            }
        });
    });
</script>
@endpush
