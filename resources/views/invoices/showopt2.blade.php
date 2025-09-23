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
         style="width:148mm; height:210mm; padding:12mm; font-family:'Noto Serif Sinhala','Iskoola Pota','Times New Roman',serif; font-size:8pt; line-height:1.28;">

        {{-- Top bilingual heading --}}
        <div class="text-center" style="margin-bottom:6mm;">
            <div style="font-weight:700;">ශ්‍රී ලංකා ගුවන් හමුදාව</div>
            <div style="font-weight:700; text-transform:uppercase;">SRI LANKA AIR FORCE</div>
            <div style="font-weight:700; margin-top:2mm;">විවිධ ගෙවීම් වවුචරය</div>
            <div style="font-weight:700; text-transform:uppercase;">MISCELLANEOUS PAYMENT VOUCHER</div>
        </div>

        @php
            $partyLabel = $invoice->invoiceable_type === 'App\\Models\\Customer' ? 'Unit'
                        : ($invoice->invoiceable_type === 'App\\Models\\Supplier' ? 'Supplier' : 'Agent');

            $partyName  = $invoice->invoiceable->customer_name
                        ?? $invoice->invoiceable->supplier_name
                        ?? $invoice->invoiceable->name
                        ?? '';

            $partyAddress = $invoice->invoiceable->primary_address
                          ?? $invoice->invoiceable->address
                          ?? '';

            $monthText = strtoupper(\Carbon\Carbon::parse($invoice->created_at ?? now())->format('F'));
            $yearText  = \Carbon\Carbon::parse($invoice->created_at ?? now())->format('Y');

            $rupees = floor($invoice->total_amount);
            $cents  = (int) round(($invoice->total_amount - $rupees) * 100);
            $cents  = $cents === 100 ? 0 : $cents; // guard rounding edge
            $cents2 = str_pad($cents, 2, '0', STR_PAD_LEFT);

            // amount in words (expects your helper to exist)
            $words = \App\Helpers\NumberToWords::convert($invoice->total_amount);
        @endphp

        {{-- Voucher meta rows (fixed alignment using table) --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:3mm; table-layout:fixed;">
            <tr>
                <td style="width:65%; padding:0 0 1.5mm 0;">
                    <span>කළමනාකරු / {{ $partyLabel }} :</span>
                    <span class="dotted-fill">{{ $partyName }}</span>
                </td>
                <td style="width:35%; padding:0 0 1.5mm 0;">
                    <span>භාණ්ඩ ගෙවීම් අංකය / Credit Voucher No. :</span>
                    <span class="dotted-fill">{{ $invoice->invoice_id }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 1.5mm 0;">
                    <span style="opacity:.85;">{{ $partyAddress }}</span>
                </td>
                <td style="padding:0 0 1.5mm 0;">
                    <span>20</span><span class="dotted-fill" style="min-width:12mm;">{{ $yearText }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 1.5mm 0;">
                    Cash A/c. for the month <span class="dotted-fill" style="min-width:28mm;">{{ $monthText }}</span> {{ $yearText }}
                </td>
                <td style="padding:0 0 1.5mm 0;">
                    දිනය / Date : <span class="dotted-fill" style="min-width:24mm;">{{ \Carbon\Carbon::parse($invoice->created_at ?? now())->format('d/m/Y') }}</span>
                </td>
            </tr>
        </table>

        {{-- Amount in words & due to --}}
        <div style="margin-bottom:3mm;">
            <div>
                <span>පන</span> <span> The sum of Rs.</span>
                <span class="dotted-fill" style="min-width:80mm; text-transform:lowercase;">
                    {{ strtolower($words) }}
                </span>
                <span> is due to </span>
                <span class="dotted-fill" style="min-width:40mm;">{{ $partyName }}</span>
            </div>
            <div style="margin-top:2mm;">
                <span>සේවාවන් සම්බන්ධයෙන් පහත පරිදි</span>
                <span> in respect of the services stated below :-</span>
            </div>
        </div>

        {{-- Services / description area + total column --}}
        <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
            <colgroup>
                <col style="width:75%;">
                <col style="width:15%;">
                <col style="width:10%;">
            </colgroup>
            <thead>
                <tr>
                    <th style="text-align:left; border-bottom:1px solid #000; padding-bottom:1mm;">
                        {{-- left intentionally blank as per form --}}
                    </th>
                    <th style="text-align:right; border-bottom:1px solid #000; padding-bottom:1mm;">රු. / Rs.</th>
                    <th style="text-align:right; border-bottom:1px solid #000; padding-bottom:1mm;">සෙ. / c.</th>
                </tr>
            </thead>
            <tbody>
                {{-- Group lines by department with SUPPLY OF … headings, then list ALL item names --}}
                @php $grouped = $invoice->items->groupBy(fn($i) => optional(optional($i->product)->department)->name ?? 'GENERAL'); @endphp
                @foreach($grouped as $dept => $rows)
                    <tr>
                        <td style="padding:1.5mm 0 0.5mm 0; font-weight:700; text-transform:uppercase;">
                            SUPPLY OF {{ str_replace('Department: ', '', $invoice->notes) }}
                        </td>
                        <td></td><td></td>
                    </tr>

                    @foreach($rows as $row)
                        @php
                            $desc = $row->description ?? optional($row->product)->name ?? '—';
                            $rs   = floor($row->total);
                            $cs   = (int) round(($row->total - $rs) * 100);
                            $cs   = $cs === 100 ? 0 : $cs;
                        @endphp
                        <tr>
                            <td class="desc-line">
                                {{ $desc }}
                            </td>
                            <td style="text-align:right;">{{ number_format($rs, 0) }}</td>
                            <td style="text-align:right;">{{ str_pad($cs, 2, '0', STR_PAD_LEFT) }}</td>
                        </tr>
                    @endforeach

                    {{-- little spacer line between departments --}}
                    <tr><td style="height:2mm;"></td><td></td><td></td></tr>
                @endforeach

                {{-- Bottom divider --}}
                <tr>
                    <td style="border-top:1px solid #000; height:0.5mm;"></td>
                    <td style="border-top:1px solid #000;"></td>
                    <td style="border-top:1px solid #000;"></td>
                </tr>

                {{-- TOTAL row (right aligned, boxed feel) --}}
                <tr>
                    <td style="text-align:right; padding-top:1mm; font-weight:700;">මුලු එකතුව / Total</td>
                    <td style="text-align:right; padding-top:1mm; font-weight:700;">{{ number_format($rupees, 0) }}</td>
                    <td style="text-align:right; padding-top:1mm; font-weight:700;">{{ $cents2 }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Supporting docs + total label line under it (matches form) --}}
        <div style="display:flex; justify-content:space-between; margin-top:5mm;">
            <div>සහතික ලිපි ඇමුණා ඇත / Supporting documents are attached where necessary.</div>
            <div style="white-space:nowrap;">මුලු එකතුව / Total }</div>
        </div>

        {{-- Signatures grid --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; column-gap:10mm; margin-top:10mm; font-size:10.5pt;">
            <div style="border:1px solid #000; padding:4mm; height:38mm;">
                <div style="font-weight:600;">ගෙවීම් සඳහා බලය</div>
                <div>Authority for payment</div>
            </div>
            <div style="border:1px solid #000; padding:4mm; height:38mm;">
                <div style="font-weight:600;">නිවැරදි බවට සහතික කරමි</div>
                <div>Certified correct. <span style="float:right;">{ Accountant Officer }</span></div>
                <div style="margin-top:10mm; font-weight:600;">ගෙවීම් සඳහා අනුමත කරමි</div>
                <div>Approved for payment. <span style="float:right;">{ Commanding Officer }</span></div>
            </div>
        </div>

        {{-- Footnote --}}
        <div style="margin-top:8mm; font-size:10pt;">
            <em>N.B. – Where the amount is on account of a suspended receipt on a List of Differences, the relative Account should be stated</em>
        </div>
    </div>
</div>

<style>
/* dot leader effect for inline fields */
.dotted-fill{
    display:inline-block;
    border-bottom:1px dotted #000;
    padding:0 2mm;
    min-width:22mm;
}

/* description lines with dotted bottom to mimic printed fill area */
.desc-line{
    border-bottom:1px dotted #999;
    padding:0.8mm 0;
}

/* print styling */
@media print{
    body *{ visibility:hidden; }
    #voucher-a5, #voucher-a5 *{ visibility:visible; }
    #voucher-a5{
        position:absolute; left:0; top:0;
        width:148mm; height:210mm;
        margin:0; padding:12mm;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }
}
</style>
@endsection
