@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    {{-- Screen header (hidden when printing) --}}
    <div class="flex justify-between items-center mb-4 print:hidden">
        <h2 class="text-xl font-semibold">Miscellaneous Payment Voucher — A5</h2>
        <div class="space-x-2">
            <a href="{{ route('invoices.show', $invoice->id) }}"
               class="px-3 py-2 bg-gray-200 rounded text-sm">Back</a>
            <button onclick="window.print()" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">
                Print Both Pages
            </button>
        </div>
    </div>

    {{-- ======================== FRONT SIDE ======================== --}}
    <div id="voucher-a5"
         class="bg-white border mx-auto shadow-sm"
         style="width:148mm; height:210mm; padding:10mm; font-family:'Noto Serif Sinhala','Iskoola Pota','Times New Roman',serif; line-height:1.35; box-sizing:border-box;">

        {{-- ==== Top bilingual heading ==== --}}
        <div class="text-center mb-4">
            <div class="flex justify-end text-[11px] mt-1">
                <div class="text-right">
                    <div>සම්මත අංකය 92</div>
                    <div>ශ්‍රී ලං.ගු.හ. 892</div>
                    <div>(F4* S.& E.) 1/74</div>
                </div>
            </div>
            <div class="fs-12 font-bold">ශ්‍රී ලංකා ගුවන් හමුදාව</div>
            <div class="fs-12 font-bold uppercase">SRI LANKA AIR FORCE</div>
            <div class="fs-11 font-bold mt-1">විවිධ ගෙවීම් වවුචරය</div>
            <div class="fs-11 font-bold uppercase">MISCELLANEOUS PAYMENT VOUCHER</div>
        </div>

        @php
            $partyLabel = $invoice->invoiceable_type === 'App\\Models\\Customer' ? 'Unit'
                        : ($invoice->invoiceable_type === 'App\\Models\\Supplier' ? 'Supplier' : 'Agent');
            $partyName  = $invoice->invoiceable->customer_name
                        ?? $invoice->invoiceable->supplier_name
                        ?? $invoice->invoiceable->name
                        ?? '';
            $monthText = strtoupper(\Carbon\Carbon::parse($invoice->created_at ?? now())->format('F'));
            $yearText  = \Carbon\Carbon::parse($invoice->created_at ?? now())->format('Y');
            $rupees = floor($invoice->total_amount);
            $cents  = (int) round(($invoice->total_amount - $rupees) * 100);
            $cents  = $cents === 100 ? 0 : $cents;
            $cents2 = str_pad($cents, 2, '0', STR_PAD_LEFT);
            $words = \App\Helpers\NumberToWords::convert($invoice->total_amount);
        @endphp

        {{-- ==== Voucher meta ==== --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:3mm;">
            <tr>
                <td class="fs-9" style="width:45%;">
                    <div class="flex items-center space-x-3">
                        <div class="text-left">
                            <span class="block">කදවුර</span>
                            <span class="block">Unit</span>
                        </div>
                        <div class="text-3xl font-bold leading-none">}</div>
                        <div class="font-semibold text-[11px] dotted-fill">{{ $partyName }}<br>Matara</div>
                    </div>
                </td>
                <td class="fs-9" style="width:55%;">
                    <div class="flex items-center space-x-3">
                        <div class="text-left">
                            <span class="block">බැර වවුචර අංකය</span>
                            <span class="block">Credit Voucher No.</span>
                        </div>
                        <div class="text-3xl font-bold leading-none">}</div>
                        <div class="font-semibold text-[11px] dotted-fill">{{ $invoice->invoice_id }}</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="fs-9"></td>
                <td class="fs-8">
                    <span>{{ $monthText }} {{ $yearText }}</span> මාසයේ මුදල් ගිණුම<br>
                    Cash A/c. for the month: <span>{{ $monthText }} {{ $yearText }}</span><br>
                    දිනය / Date : <span class="dotted-fill">{{ \Carbon\Carbon::parse($invoice->created_at ?? now())->format('d/m/Y') }}</span>
                </td>
            </tr>
        </table>

        {{-- ==== Amount in words ==== --}}
        <div class="fs-9 mb-4">
            <div class="flex items-center flex-wrap gap-3">
                <div style="width:28%;">
                    <span class="block">පහත සදහන් සේවය සදහා රුපියල්</span>
                    <span class="block">The sum of Rs.</span>
                </div>
                <div class="font-semibold text-[11px] dotted-fill text-center" style="width:17%; min-width:100px;">
                    {{ number_format($rupees, 0) }}
                </div>
                <div style="width:4%;">
                    <span class="block">ශත</span><span class="block">c.</span>
                </div>
                <div class="font-semibold text-[11px] dotted-fill text-center" style="width:10%; min-width:80px;">
                    {{ $cents2 }}
                </div>
                <div style="width:20%;">
                    <span class="block">මුදලක්</span><span class="block">is due to</span>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-1">
                <div class="font-semibold text-[11px] dotted-fill text-center" style="width:40%; min-width:250px;">
                    {{ strtolower($words) }}
                </div>
                <div class="text-left" style="width:55%;">
                    <span class="block">ලැබිය යුතුය</span>
                    <span class="block">in respect of the services stated below.</span>
                </div>
            </div>
        </div>

        {{-- ==== Description + Amount ==== --}}
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr class="fs-9">
                    <th style="text-align:left; border-bottom:1px solid #000;"></th>
                    <th style="text-align:right; border-bottom:1px solid #000;">රු. / Rs.</th>
                    <th style="text-align:right; border-bottom:1px solid #000;">සෙ. / c.</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grouped = $invoice->items->groupBy(fn($i) => optional(optional($i->product)->department)->name ?? 'GENERAL');
                @endphp

                @foreach($grouped as $dept => $rows)
                    @php
                        $deptTotal = $rows->sum('total');
                        $rs = floor($deptTotal);
                        $cs = (int) round(($deptTotal - $rs) * 100);
                        $cs = $cs === 100 ? 0 : $cs;
                    @endphp
                    <tr>
                        <td class="fs-9 font-bold uppercase">SUPPLY OF {{ strtoupper($dept) }}</td>
                        <td style="text-align:right;">{{ number_format($rs, 0) }}</td>
                        <td style="text-align:right;">{{ str_pad($cs, 2, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                @endforeach

                <tr><td colspan="3" style="border-top:1px solid #000;"></td></tr>
                <tr class="fs-9 font-bold">
                    <td style="text-align:right;">මුලු ගණන / Total</td>
                    <td style="text-align:right;">{{ number_format($rupees, 0) }}</td>
                    <td style="text-align:right;">{{ $cents2 }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ==== Signatures ==== --}}
        <div class="fs-9 mt-10 border-t pt-2">
            <div class="flex justify-between mb-10">
                <div style="width:35%; text-align:center; border-right:1px solid #000; margin-right:10mm;">
                    <div class="font-semibold">ගෙවීම් සඳහා බලය</div>
                    <div>Authority for payment</div>
                </div>
                <div style="width:55%;">
                    <div class="font-semibold">නිවැරදි බවට සහතික කරනු ලබන්නේ,</div>
                    <div>Certified correct</div>
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="font-semibold text-[11px]">..............................................</div>
                        <div class="text-3xl font-bold leading-none">{</div>
                        <div class="text-left">
                            <span class="block">ගණකාධිකාරි</span>
                            <span class="block">Accountant Officer</span>
                        </div>
                    </div>

                    <div class="font-semibold">ගෙවීම අනුමත කරනු ලබන්නේ,</div>
                    <div>Approved for payment</div>
                    <div class="flex items-center space-x-3">
                        <div class="font-semibold text-[11px]">...........................................</div>
                        <div class="text-3xl font-bold leading-none">{</div>
                        <div class="text-left">
                            <span class="block">අණදෙන නිලධාරී</span>
                            <span class="block">Commanding Officer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==== Footer ==== --}}
        <div class="fs-8 mt-6">
            <em>N.B. – Where the amount is on account of a suspended receipt on a List of Differences, the relative Account should be stated.</em>
        </div>
        <div class="mt-4 text-[11px]">
            H 053409 – 1500 (2019/08) ශ්‍රී ලංකා රජයේ මුද්‍රණ දෙපාර්තමේන්තුව
        </div>
    </div>

    {{-- ======================== BACK SIDE ======================== --}}
    <div id="voucher-a5-back"
         class="bg-white border mx-auto page-break-before shadow-sm"
         style="width:148mm; height:210mm; padding:15mm; font-family:'Noto Serif Sinhala','Iskoola Pota','Times New Roman',serif; line-height:1.5; box-sizing:border-box;">

        <div class="flex justify-end text-[11pt] mb-8">
            <div>
                <span>දිනය / Date :</span>
                <span class="inline-block border-b border-black ml-2" style="min-width:80mm;">{{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="text-[11pt] leading-7 mb-8">
            <div>
                <span>රුපියල්</span>
                <span class="inline-block border-b border-black mx-2" style="min-width:60mm;">{{ number_format($rupees, 0) }}</span>
                <span>ශත</span>
                <span class="inline-block border-b border-black mx-2" style="min-width:25mm;">{{ $cents2 }}</span>
                <span>මෙම වවුචරයේ පසුපස සඳහන් සේවා සඳහා ලැබු බව මෙයින් තහවුරු කරමි.</span>
            </div>

            <div class="mt-4">
                <span>RECEIVED the sum of</span>
                <span class="inline-block border-b border-black mx-2" style="min-width:60mm;">{{ number_format($rupees, 0) }}</span>
                <span>Rupees</span>
                <span class="inline-block border-b border-black mx-2" style="min-width:25mm;">{{ $cents2 }}</span>
                <span>cents in respect of the services detailed overleaf.</span>
            </div>
        </div>

        <div class="flex justify-between mt-8 mb-6 text-[11pt]">
            <div>
                <div>රු. / Rs.</div>
                <div>ශත / cts.</div>
            </div>
            <div class="border border-black" style="width:45mm; height:70mm;"></div>
        </div>
    </div>
</div>

{{-- ======================== STYLES ======================== --}}
<style>
.fs-6 { font-size:6pt; }
.fs-7 { font-size:7pt; }
.fs-8 { font-size:8pt; }
.fs-9 { font-size:9pt; }
.fs-10 { font-size:10pt; }
.fs-11 { font-size:11pt; }
.fs-12 { font-size:12pt; }

.dotted-fill {
    display:inline-block;
    border-bottom:1px dotted #000;
    padding:0 2mm;
    min-width:20mm;
}

/* ----------- PRINT FIX FOR A5 SIZE ----------- */
@page {
    size: A5;
    margin: 0;
}

.page-break-before {
    page-break-before: always;
}

@media print {
    html, body {
        width: 148mm;
        height: 210mm;
        margin: 0;
        padding: 0;
    }
    body * { visibility: hidden; }
    #voucher-a5, #voucher-a5 * { visibility: visible; }
    #voucher-a5-back, #voucher-a5-back * { visibility: visible; }
    #voucher-a5 {
        position: relative;
        page-break-after: always;
        width: 148mm; height: 210mm;
        margin: 0 auto; padding: 10mm;
    }
    #voucher-a5-back {
        position: relative;
        width: 148mm; height: 210mm;
        margin: 0 auto; padding: 15mm;
    }
}
</style>
@endsection
