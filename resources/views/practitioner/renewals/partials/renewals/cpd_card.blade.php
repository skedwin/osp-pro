<div class="rounded-lg border p-4 bg-white">
    <div class="flex items-center justify-between mb-2">
        <h4 class="text-lg font-semibold">CPD Summary</h4>
        <div class="text-sm text-slate-500">Year: {{ 
            \Carbon\Carbon::now()->year
        }}</div>
    </div>

    <div class="text-sm text-slate-700">Points this year: <strong>{{ number_format($cpdTotal ?? 0, 1) }}</strong></div>
    <div class="text-sm text-slate-700">Required: <strong>{{ number_format($requiredCpd ?? 20, 1) }}</strong></div>
    @if(isset($hasCpd) && $hasCpd)
        <div class="text-sm text-green-700 mt-2">You meet the CPD requirement.</div>
    @else
        <div class="text-sm text-rose-700 mt-2">You do not meet the CPD requirement yet.</div>
    @endif
</div>
