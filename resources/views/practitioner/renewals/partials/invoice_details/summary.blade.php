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
