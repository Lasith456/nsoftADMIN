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
                Print
            </button>
        </div>
    </div>

    <div id="voucher-a5"
         class="bg-white border mx-auto"
         style="width:148mm; height:210mm; padding:10mm; font-family:'Noto Serif Sinhala','Iskoola Pota','Times New Roman',serif; line-height:1.35;">

        {{-- Top bilingual heading --}}
        <div class="text-center" style="margin-bottom:4mm;">
                <div class="flex justify-end text-[11px] mt-1">
      <div class="text-right">
        <div>සම්මත අංකය 92</div>
        <div>ශ්‍රී ලං.ගු.හ. 892</div>
        <div>(F4* S.& E.) 1/74</div>
      </div>
    </div>
            <div class="fs-12" style="font-weight:700;">ශ්‍රී ලංකා ගුවන් හමුදාව</div>
            <div class="fs-12" style="font-weight:700; text-transform:uppercase;">SRI LANKA AIR FORCE</div>
            <div class="fs-11" style="font-weight:700; margin-top:2mm;">විවිධ ගෙවීම් වවුචරය</div>
            <div class="fs-11" style="font-weight:700; text-transform:uppercase;">MISCELLANEOUS PAYMENT VOUCHER</div>
            
        </div>
    <!-- Top Right Numbers -->


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

        {{-- Voucher meta --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:3mm; table-layout:fixed;">
            <tr>
                <td class="fs-9" style="width:55%; padding-bottom:2mm;">
                    <span>කදවුර / {{ $partyLabel }} :</span>
                    <span class="dotted-fill">{{ $partyName }}</span>
                    <br><span class="pl-16"> MATARA</span>
                </td>
                <td class="fs-9" style="width:45%; padding-bottom:2mm;">
                    <span>බැර වවුචර අංකය /</span>
                    <span class="dotted-fill">{{ $invoice->invoice_id }}</span><br>
                    <span>Credit Voucher No. :</span>
                </td>
            </tr>
            <tr>
                <td class="fs-9" style="padding-bottom:2mm;"></td>
                <td class="fs-8" style="padding-bottom:2mm;">
                    <span class="">{{ $monthText }}{{ $yearText }}</span><span> මාසයේ මුදල් ගිණුම</span>
                    <br>Cash A/c. for the month :
                    <span class="">{{ $monthText }}{{ $yearText }}</span>
                    <br>දිනය / Date : <span class="dotted-fill">{{ \Carbon\Carbon::parse($invoice->created_at ?? now())->format('d/m/Y') }}</span>
                </td>
            </tr>

        </table>

        {{-- Amount in words --}}
        <div style="margin-bottom:4mm;" class="fs-9">
            <span>පහත සදහන් සේවය සදහා රුපියල් /........................................................ ශත .................. මුදලක්<br> 
            The sum of Rs. ....................................................................................... c. .................. is due to</span>
            <br>
            <span style=" text-transform:lowercase;">
                {{ strtolower($words) }}
            </span>
            ලැබිය යුතුය
            <br>.................................................................in respect of the services started below.
            <!-- <span>and {{ $cents2 }}/100</span>
            <span>is due to</span> -->
            <!-- <span class="dotted-fill" style="min-width:35mm;">{{ $partyName }}</span> -->
        </div>

        {{-- Description + Amount --}}
        <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
            <colgroup>
                <col style="width:75%;">
                <col style="width:15%;">
                <col style="width:10%;">
            </colgroup>
            <thead>
                <tr class="fs-9">
                    <th style="text-align:left; border-bottom:1px solid #000; padding-bottom:1mm;"></th>
                    <th style="text-align:right; border-bottom:1px solid #000; padding-bottom:1mm;">රු. / Rs.</th>
                    <th style="text-align:right; border-bottom:1px solid #000; padding-bottom:1mm;">සෙ. / c.</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // group invoice items by department name
                    $grouped = $invoice->items->groupBy(function($item) {
                        return optional(optional($item->product)->department)->name ?? 'GENERAL';
                    });
                @endphp

                @foreach($grouped as $dept => $rows)
                    @php
                        $deptTotal = $rows->sum('total');
                        $rs   = floor($deptTotal);
                        $cs   = (int) round(($deptTotal - $rs) * 100);
                        $cs   = $cs === 100 ? 0 : $cs;
                    @endphp

                    <tr>
                        <td class="fs-9" style="padding:1.5mm 0; font-weight:700; text-transform:uppercase;">
                            SUPPLY OF {{ strtoupper($dept) }}
                        </td>
                        <td style="text-align:right;">{{ number_format($rs, 0) }}</td>
                        <td style="text-align:right;">{{ str_pad($cs, 2, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                @endforeach

                {{-- Divider --}}
                <tr>
                    <td style="border-top:1px solid #000; height:0.5mm;"></td>
                    <td style="border-top:1px solid #000;"></td>
                    <td style="border-top:1px solid #000;"></td>
                </tr>

                {{-- TOTAL --}}
                <tr class="fs-9">
                    <td style="text-align:right; padding-top:1mm; font-weight:700;">මුලු ගණන / Total</td>
                    <td style="text-align:right; padding-top:1mm; font-weight:700;">{{ number_format($rupees, 0) }}</td>
                    <td style="text-align:right; padding-top:1mm; font-weight:700;">{{ $cents2 }}</td>
                </tr>
            </tbody>

        </table>

        {{-- Supporting docs line --}}
        <div style="display:flex; justify-content:space-between; margin-top:5mm;" class="fs-8">
            <div>සබැඳි ලියකියවිලි උවමනා අවස්ථාවන්හීදී ඇමිණිය යුතුයි.
                <br>Supporting documents are attached where necessary.</div>
            <div style="white-space:nowrap;"></div>
        </div>

        {{-- Signatures --}}
        <div style="margin-top:12mm; border-top:1px solid #000; margin-top:10mm;" class="fs-9">
            <div style="display:flex; justify-content:space-between; margin-bottom:10mm;">
                <div style="width:35%; text-align:center; border-right:1px solid #000; margin-right:10mm;">
                    <div style="font-weight:600;">ගෙවීම් සඳහා බලය</div>
                    <div>Authority for payment</div>
                </div>
                <div style="width:55%;">
                    <div style="font-weight:600;">නිවැරදි බවට සහතික කරනු ලබන්නේ,</div>
                    <div>Certified correct. { Accountant Officer }</div>
                    <span style="margin-top:10mm;">........................................................ ගණකාධිකාරි</span>
                    <div style=" font-weight:600;">ගෙවීම අනුමත කරනු ලබන්නේ,</div>
                    <div>Approved for payment. { Commanding Officer }</div>
                    <span style="margin-top:10mm;">........................................................ අණදෙන නිලධාරී</span>
                </div>
            </div>
        </div>

        {{-- Footnote --}}
        <div style="margin-top:10mm;" class="fs-8">
            <em>N.B. – Where the amount is on account of a suspended receipt on a List of Differences, the relative Account should be stated</em>
        </div>
        <div class="mt-4 text-[11px]">
      H 053409 – 1500 (2019/08) ශ්‍රී ලංකා රජයේ මුද්‍රණ දෙපාර්තමේන්තුව
    </div>
    </div>
</div>

<style>
/* font size helpers */
.fs-6 { font-size:6pt; }
.fs-7 { font-size:7pt; }
.fs-8 { font-size:8pt; }
.fs-9 { font-size:9pt; }
.fs-10 { font-size:10pt; }
.fs-11 { font-size:11pt; }
.fs-12 { font-size:12pt; }

/* dotted fills */
.dotted-fill {
    display:inline-block;
    border-bottom:1px dotted #000;
    padding:0 2mm;
    min-width:20mm;
}
.dotted-center {
    display:inline-block;
    border-bottom:1px dotted #000;
    text-align:center;
    min-width:35mm;
    padding:0 2mm;
}
.desc-line {
    border-bottom:1px dotted #999;
    padding:0.8mm 0;
}

/* print mode */
@media print {
    body * { visibility:hidden; }
    #voucher-a5, #voucher-a5 * { visibility:visible; }
    #voucher-a5 {
        position:absolute; left:0; top:0;
        width:148mm; height:210mm;
        margin:0; padding:10mm;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }
}
</style>
@endsection
