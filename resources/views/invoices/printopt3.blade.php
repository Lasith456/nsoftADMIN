@extends('layouts.app')

@section('content')
<div class="text-right mb-4 no-print">
    <button onclick="window.print()" 
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Print Invoice
    </button>
</div>

<div class="invoice-container bg-white p-6" style="width: 8.5in; height: 14in; margin: auto; font-family: 'Noto Serif Sinhala', 'Noto Serif Tamil', serif; font-size: 12px;">

    <!-- Header -->
    <div class="text-center mb-3">
        <h2 class="font-bold text-lg">
            කොන්ත්‍රාත්කරුගේ බිල් පත / ஒப்பந்தக்காரரின் பத்திரம் / Contractor’s Invoice
        </h2>
    </div>

    <!-- Top Info -->
    <table class="w-full border-collapse border border-black mb-3 text-xs">
        <tr>
            <td class="border border-black p-2 w-1/3">
                <b>ගනුදෙනුකරුගේ නම / வாடிக்கையாளர் பெயர் / Customer Name:</b><br>
                {{ $invoice->customer_name }}
            </td>
            <td class="border border-black p-2 w-1/3">
                <b>ස්ථානය / இடம் / Location:</b><br>
                {{ $invoice->location }}
            </td>
            <td class="border border-black p-2 w-1/3">
                <b>දිනය / தேதி / Date:</b><br>
                {{ $invoice->invoice_date->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td class="border border-black p-2">
                <b>සමාගමේ නම / நிறுவனம் / Company:</b><br>
                {{ $invoice->company_name }}
            </td>
            <td class="border border-black p-2">
                <b>ලිපිනය / முகவரி / Address:</b><br>
                {{ $invoice->company_address }}
            </td>
            <td class="border border-black p-2">
                <b>බිල් අංකය / பில் எண் / Invoice No:</b><br>
                {{ $invoice->invoice_no }}
            </td>
        </tr>
        <tr>
            <td class="border border-black p-2">
                <b>ගෙවීමේ ක්‍රමය / கட்டண முறை / Payment Method:</b><br>
                {{ $invoice->payment_method }}
            </td>
            <td class="border border-black p-2">
                <b>ඩෙලවරි ලිපිනය / விநியோக முகவரி / Delivery Address:</b><br>
                {{ $invoice->delivery_address }}
            </td>
            <td class="border border-black p-2">
                <b>යොමු අංකය / குறிப்பு எண் / Ref No:</b><br>
                {{ $invoice->ref_no }}
            </td>
        </tr>
    </table>

    <!-- Notes -->
    <div class="mb-3 text-xs leading-5">
        <p>1. මෙම බිල්පත ගෙවිය යුතු මුදල පමණක් අය කිරීම සඳහා භාවිතා කළ යුතුය.</p>
        <p>2. පාරිභෝගිකයා විසින් ගෙවිය යුතු මුදල ගෙවීමෙන් පසු පිළිගැනීමේ ලියකියවිලි ලබා දිය යුතුය.</p>
        <p>1. இவ்விலைப்பட்டியல் பணம் வசூலிக்க மட்டுமே பயன்பட வேண்டும்.</p>
        <p>2. வாடிக்கையாளர் பணம் செலுத்திய பின் ரசீது வழங்கப்பட வேண்டும்.</p>
    </div>

    <!-- Items Table -->
    <table class="w-full border-collapse border border-black text-xs">
        <thead>
            <tr>
                <th class="border border-black p-1">කාල සීමාව / கால வரம்பு / Period</th>
                <th class="border border-black p-1">විස්තරය / விவரம் / Description</th>
                <th class="border border-black p-1">ප්‍රමාණය / அளவு / Qty</th>
                <th class="border border-black p-1">ඒකක මිල / அலகு விலை / Unit Price</th>
                <th class="border border-black p-1">මුදල / தொகை / Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td class="border border-black p-1 text-center">
                        From {{ $item->from_date }} <br> To {{ $item->to_date }}
                    </td>
                    <td class="border border-black p-1">{{ $item->description }}</td>
                    <td class="border border-black p-1 text-right">{{ $item->quantity }}</td>
                    <td class="border border-black p-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="border border-black p-1 text-right">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" class="border border-black p-1 text-right"><b>VAT {{ $invoice->vat_rate }}%</b></td>
                <td class="border border-black p-1 text-right">{{ number_format($invoice->vat_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="border border-black p-1 text-right"><b>Total</b></td>
                <td class="border border-black p-1 text-right font-bold">{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Amount in Words -->
    <p class="mt-3 text-sm">
        <b>අක්ෂරයෙන් මුදල / எழுத்துக்களில் தொகை / Amount in Words:</b> {{ $invoice->amount_in_words }}
    </p>

    <!-- Bottom Notes -->
    <div class="mt-3 text-xs leading-5">
        <p>මෙම බිල්පත පිළිබඳ විමසීම් ඇති නම් එමගින් සම්බන්ධ වන ලිපි මගින් දැනුම් දිය යුතුය.</p>
        <p>இவ்விலைப்பட்டியல் தொடர்பாக கேள்விகள் இருப்பின் அவற்றை எழுத்து மூலம் அறிவிக்க வேண்டும்.</p>
    </div>

    <!-- Footer -->
    <div class="mt-6 flex justify-between text-sm">
        <div>
            <p>පිරිසදු කළ මුදල / செலுத்தப்பட்ட தொகை / Paid Amount: <b>{{ number_format($invoice->total_amount, 2) }}</b></p>
            <p>දිනය / தேதி / Date: {{ $invoice->invoice_date->format('d/m/Y') }}</p>
        </div>
        <div class="border border-black p-2 text-center w-40">
            <p>රු. 1000 ට අඩු<br>රු. 1.00 තැපැල් මුද්දරය<br>100 ක් සඳහා මුද්දර<br>(Stamp Box)</p>
        </div>
    </div>

    <div class="mt-8 flex justify-between text-sm">
        <p>කාර්යය නිලධාරියා / அலுவலர் / Officer: __________________</p>
        <p>අත්සන / கையொப்பம் / Signature: __________________</p>
    </div>


</div>

<style>
    @media print {
        .no-print { display: none; }
        body { margin: 0; }
        @page {
            size: legal portrait;
            margin: 10mm;
        }
    }
</style>
@endsection
