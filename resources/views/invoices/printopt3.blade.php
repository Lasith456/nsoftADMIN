@extends('layouts.app')

@section('content')
<div class="text-right mb-3 no-print">
    <button onclick="window.print()" 
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Print Invoice
    </button>
</div>

<div class="invoice-container bg-white p-4 mx-auto" 
     style="width: 8.5in; height: 13.6in; font-family: 'Noto Serif Sinhala', 'Noto Serif Tamil', serif; font-size: 11px; line-height: 1.2;">

    <!-- ================= HEADER RIGHT SECTION ================= -->
    <div class="flex justify-end text-[11px]">
        <div class="text-right space-y-0.5">

            <!-- Compact curly brace row 1 -->
            <div class="flex items-center justify-end space-x-2">
                <div class="flex flex-col items-end leading-tight text-[11px]">
                    <span>සම්මත අංකය</span>
                    <span>தராதர இலக்கம் </span>
                </div>
                <div class="text-2xl font-bold leading-none">}</div>
                <div class="flex space-x-1 text-[11px] font-semibold">
                    <span>258</span>
                </div>
            </div>

            <!-- Compact curly brace row 2 -->
            <div class="flex items-center justify-end space-x-2">
                <div class="flex flex-col items-end leading-tight text-[11px]">
                    <span>ශ්‍රී  ලං. ගු. හ.</span>
                    <span>இ. அ. வா. ப.  </span>
                </div>
                <div class="text-2xl font-bold leading-none">}</div>
                <div class="flex space-x-1 text-[11px] font-semibold">
                    <span>666</span>
                </div>
            </div>

            <div>(F2* සිං/දෙ) 04/86</div>
        </div>
    </div>

    <!-- ================= MAIN HEADING ================= -->
    <div class="text-center my-1">
        <h2 class="font-bold text-[18px]">
            කොන්ත්‍රාත්කරුගේ බිල් පත / ஒப்பந்தக்காரரின் பட்டியல்
        </h2>
    </div>

    <!-- ================= HEADER INFORMATION TABLE ================= -->
    <div class="text-[10.5px] leading-tight mb-2 w-full ">
        <div class="grid grid-cols-3 text-left">
            <!-- Left Column with Table -->
            <div class="p-1">
                <table class="w-full border border-black border-collapse text-[10px] leading-tight">
                    <thead>
                        <tr>
                            <th colspan="2" class="border border-black border-r border-black p-1 text-center font-semibold">
                                ශ්‍රී ලං. ගු. හ <span> ප්‍රයෝජනය සදහා පමණයි</span> <br>
                                இ. அ. வா. ப. <span>பயன்பாட்டிற்கு மட்டும்</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border-r border-black p-1">
                                <div class="flex items-center space-x-3">
                                    <!-- Sinhala + English -->
                                    <div class="text-left">
                                        <span class="block">ඒකක බිල් අනුක්‍රමික අංක</span>
                                        <span class="block">அலகு பட்டியல் தொடர் எண் </span>
                                    </div>

                                    <!-- Curly brace -->
                                    <div class="text-3xl font-bold leading-none">}</div>

                                    <!-- Number -->
                                    <div class="font-semibold text-[11px] dotted-fill"></div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="border-r border-black p-1">
                                <div class="flex items-center">
                                    <!-- Text block: allow it to expand, never shrink -->
                                    <div class="flex-grow flex-shrink-0 text-left">
                                        <span class="block">රහස්‍ය ලැයිස්තු අංක</span>
                                        <span class="block">ரகசிய பட்டியல் எண்</span>
                                    </div>

                                    <!-- Curly brace -->
                                    <div class="text-3xl font-bold leading-none mx-3">}</div>

                                    <!-- Number block: keep small, fixed width -->
                                    <div class="text-center font-semibold text-[11px] dotted-fill"></div>
                                </div>
                            </td>

                        </tr>

                        <tr>
                            <td class="border-r border-black p-1">
                                <div class="flex items-center">
                                    <!-- Text block: allow it to expand, never shrink -->
                                    <div class="flex-grow flex-shrink-0 text-left">
                                        <span class="block">ගිණුම් වර්ෂය</span>
                                        <span class="block">கணக்கு ஆண்டு</span>
                                    </div>

                                    <!-- Curly brace -->
                                    <div class="text-3xl font-bold leading-none mx-3">}</div>

                                    <!-- Number block: keep small, fixed width -->
                                    <div class="text-center font-semibold text-[11px] dotted-fill"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>




            <!-- Middle Column (no top or bottom border) -->
            <div class="p-1">
                <div class="flex justify-between mt-2 py-1">
                    <div class="flex items-center">
                    <!-- Text block: allow it to expand, never shrink -->
                        <div class="flex-grow flex-shrink-0 text-left">
                            <span class="block">කදවුර</span>
                            <span class="block">பாசறை </span>
                        </div>
                        <!-- Curly brace -->
                        <div class="text-3xl font-bold leading-none mx-3">}</div>
                        <!-- Number block: keep small, fixed width -->
                        <div class="text-center flex-shrink-0 font-semibold text-[11px] dotted-fill">Sri Lanka Air-Force</div>
                    </div>
                </div>

                <div class="flex justify-between py-1">
                    <div class="flex items-center">
                    <!-- Text block: allow it to expand, never shrink -->
                        <div class="flex-grow flex-shrink-0 text-left">
                            <span class="block">ස්ථානය</span>
                            <span class="block">இடம்</span>
                        </div>
                        <!-- Curly brace -->
                        <div class="text-3xl font-bold leading-none mx-3">}</div>
                        <!-- Number block: keep small, fixed width -->
                        <div class="text-center flex-shrink-0 font-semibold text-[11px] dotted-fill">Koggala</div>
                    </div>
                </div>

                <div class="flex justify-between py-1">
                    <div class="flex items-center">
                    <!-- Text block: allow it to expand, never shrink -->
                        <div class="flex-grow flex-shrink-0 text-left">
                            <span class="block">ඇනවුම් කොන්ත්‍රාත් අංකය</span>
                            <span class="block">கட்டளை  ஒப்பந்த எண்</span>
                        </div>
                        <!-- Curly brace -->
                        <div class="text-3xl font-bold leading-none mx-3">}</div>
                        <!-- Number block: keep small, fixed width -->
                        <div class="text-center font-semibold text-[11px] dotted-fill"></div>
                    </div>
                </div>

                <div class="flex justify-between py-1">
                    <div class="flex items-center">
                    <!-- Text block: allow it to expand, never shrink -->
                        <div class="flex-grow flex-shrink-0 text-left">
                            <span class="block">දිනය</span>
                            <span class="block">திகதி</span>
                        </div>
                        <!-- Curly brace -->
                        <div class="text-3xl font-bold leading-none mx-3">}</div>
                        <!-- Number block: keep small, fixed width -->
                        <div class="text-center flex-shrink-0 font-semibold text-[11px] dotted-fill">16/09/2025</div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="p-1">
                <table class="w-full border border-black border-collapse text-[10px] leading-tight">
                    <thead>
                        <tr>
                            <th colspan="2" class="border border-black border-r border-black p-1 text-center font-semibold">
                                දෙපාර්තමේන්තුවේ ප්‍රයෝජනය සදහා පමණයි <br>
                                திணைக்கள  உபயோகத்திற்கு மட்டும் 
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border-r border-black p-1">
                                <div class="flex items-center">
                                <!-- Text block: allow it to expand, never shrink -->
                                    <div class="flex-grow flex-shrink-0 text-left">
                                        <span class="block">බිල්පත්‍ර අංකය</span>
                                        <span class="block">பட்டியல் எண்</span>
                                    </div>
                                    <!-- Curly brace -->
                                    <div class="text-3xl font-bold leading-none mx-3">}</div>
                                    <!-- Number block: keep small, fixed width -->
                                    <div class="text-center font-semibold text-[11px] dotted-fill"></div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="border-r border-black p-1">
                                <div class="flex items-center">
                                <!-- Text block: allow it to expand, never shrink -->
                                    <div class="flex-grow flex-shrink-0 text-left">
                                        <span class="block">වැය ලැජරයේ ඇතුළත් කරන ලදි</span>
                                        <span class="block">செலவினப்  பதிவேட்டில் சேர்க்கப்பட்டது</span>
                                    </div>
                                    <!-- Curly brace -->
                                    <div class="text-3xl font-bold leading-none mx-3">}</div>
                                    <!-- Number block: keep small, fixed width -->
                                    <div class="text-center font-semibold text-[11px] dotted-fill"></div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="border-r border-black p-1">
                                <div class="flex items-center">
                                <!-- Text block: allow it to expand, never shrink -->
                                    <div class="flex-grow flex-shrink-0 text-left">
                                        <span class="block">ශීර්ෂය</span>
                                        <span class="block">தலைப்பு</span>
                                    </div>
                                    <!-- Curly brace -->
                                    <div class="text-3xl font-bold leading-none mx-3">}</div>
                                    <!-- Number block: keep small, fixed width -->
                                    <div class="text-center font-semibold text-[11px] dotted-fill"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="border-r border-black p-1">
                                <div class="flex items-center">
                                <!-- Text block: allow it to expand, never shrink -->
                                    <div class="flex-grow flex-shrink-0 text-left">
                                        <span class="block">මුදල</span>
                                        <span class="block">தொகை</span>
                                    </div>
                                    <!-- Curly brace -->
                                    <div class="text-3xl font-bold leading-none mx-3">}</div>
                                    <!-- Number block: keep small, fixed width -->
                                    <div class="text-center font-semibold text-[11px] dotted-fill"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="border-r border-black p-1">
                                <div class="flex items-center">
                                <!-- Text block: allow it to expand, never shrink -->
                                    <div class="flex-grow flex-shrink-0 text-left">
                                        <span class="block">අත්සන හා දිනය</span>
                                        <span class="block">ஒப்பமும் மற்றும் திகதி</span>
                                    </div>
                                    <!-- Curly brace -->
                                    <div class="text-3xl font-bold leading-none mx-3">}</div>
                                    <!-- Number block: keep small, fixed width -->
                                    <div class="text-center font-semibold text-[11px] dotted-fill"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex items-center space-x-3 -mt-10">
            <div class="text-left">
                <span class="block">කොන්ත්‍රාත්කරුගේ නම</span>
                <span class="block">ஒப்பந்தக்காரரின் பெயர்</span>
            </div>
            <div class="text-3xl font-bold leading-none">}</div>
            <div class="font-semibold text-[11px] dotted-fill">H.G.P.M. (Pvt) Ltd.</div>
        </div>
        <div class="flex items-center space-x-3">
            <div class="text-left">
                <span class="block">සම්පූර්ණ ලිපිනය</span>
                <span class="block">முழு முகவரி</span>
            </div>
            <div class="text-3xl font-bold leading-none">}</div>
            <div class="font-semibold text-[11px] dotted-fill">No:09, Old Market Road, Kotuwegoda,Mathara</div>
        </div>
        <div class="flex items-center space-x-3">
            <div class="text-left">
                <span class="block">චෙක්පත ගෙවිය යුත්තේ</span>
                <span class="block">காசோலை யாரிடம் கொடுப்பது </span>
            </div>
            <div class="text-3xl font-bold leading-none">}</div>
            <div class="font-semibold text-[11px] dotted-fill">H.G.P.M. (Pvt) Ltd.</div>
        </div>
        <div class="flex items-center space-x-3">
            <div class="text-left">
                <span class="block">ලිපිනය</span>
                <span class="block">முகவரி</span>
            </div>
            <div class="text-3xl font-bold leading-none">}</div>
            <div class="font-semibold text-[11px] dotted-fill">No:09, Old Market Road, Kotuwegoda,Mathara</div>
        </div>
            <table class="w-full border-collapse text-[10px] leading-tight">
                <tbody>
                    <tr>
                        <!-- First column (60%) -->
                        <td class="w-[60%] p-1 align-top">
                            <div class="flex items-center space-x-3">
                                <div class="text-left">
                                    <span class="block">ගිණුම් චෙක්පතක්ද යන වග</span>
                                    <span class="block">காசோலை அனுப்பும் விபரம் </span>
                                </div>
                                <div class="text-3xl font-bold leading-none">}</div>
                                <div class="font-semibold text-[11px] dotted-fill">By Cheque</div>
                            </div>
                        </td>

                        <!-- Second column (40%) -->
                        <td class="w-[40%] p-1 align-top">
                            <div class="flex items-center space-x-3">
                                <div class="text-left">
                                    <span class="block">මෙම බිල්පත්‍රය හදුනාගැනීමේ කොන්ත්‍රාත්කරුගේ යොමුව</span>
                                    <span class="block">இந்த பட்டியலை ஏற்றுக்கொள்ளும் ஒப்பந்தக்காரரின் தொடர் எண் </span>
                                </div>
                                <div class="text-3xl font-bold leading-none">}</div>
                                <div class="font-semibold text-[11px] dotted-fill">5216 AF</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
    </div>

    <!-- ================= NOTES ================= -->
    <div class="border-t border-black mb-2 text-[10px] leading-4">
        <!-- Header line with centered heading -->
        <div class="text-center font-semibold py-0.5">
            දැනගැනීම සදහායි / அறிவிக்கப்பட வேண்டியது 
        </div>

        <!-- Notes content -->
        <div class="p-2">
            <p>1. මෙම බිල්පත්‍රය කොන්ත්‍රාත්තුවට අනුකූලව පිලියෙළ කොට කොන්ත්‍රාත්කරු විසින් නියමිත ස්ථානයේ අත්සන් කොට ඉදිරිපත් කළ යුතුයි.</p>
            <p>2. මුදල් ගෙවීම කරනු ලබන්නේ මෙම බිල් පත්‍රය නිසි ලෙස පුරවා ඉදිරිපත් කළ පසු බව කරුණාවෙන් සළකන්න.</p>
            <p>1. இந்த பட்டியலை ஒப்பந்தத்தின்படி தயாரிக்கப்பட்டு, ஒப்பந்ததாரரால் நியமிக்கப்பட்ட இடத்தில் கையொப்பமிடப்பட்டு சமர்ப்பிக்கப்பட வேண்டும்..</p>
            <p>2. இந்த பட்டியலை  முறையாக நிரப்பப்பட்டு சமர்ப்பிக்கப்பட்ட பிறகு பணம் செலுத்தப்படும் என்பதை நினைவில் கொள்ளவும்.</p>
        </div>
    </div>

    <!-- ================= ITEM TABLE ================= -->
    <table class="w-full border-collapse border border-black text-[10px]">
        <thead>
            <tr>
                <th class="border border-black p-1">සැපයූ දිනය <br>வழங்கப்பட்ட தேதி</th>
                <th class="border border-black p-1">සැපයීම් හා පරීක්ෂා කිරීම් පත් අංකය <br>வழங்கிடும் பரிசோதைப் பத்திரத்தின் இலக்கம் </th>
                <th class="border border-black p-1">ඉන්වොයිස් පත්‍ර අංකය <br>வஅனுப்பும் பொருள் எண்</th>
                <th class="border border-black p-1">කොන්ත්‍රාත්‍තු භාණ්ඩ අංකය <br>ஒப்பந்த பொருள்களின் இலக்கம் </th>
                <th class="border border-black p-1">භාණ්ඩ විස්තරය හෝ සේවාවේ විස්තරය <br>பொருள்களின் விளக்கம் அல்லது சேவை விளக்கம்</th>
                <th class="border border-black p-1">ප්‍රමාණය <br>அளவு</th>
                <th class="border border-black p-1">එකක මිළ <br>ஒன்றின் விலை</th>
                <th class="border border-black p-1">මුදල <br>தொகை</th>
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

    <!-- ================= AMOUNT IN WORDS ================= -->
    <p class="mt-2 text-[10.5px]">
        මෙයින් ඉල්ලා සිටින මුදල සපයන ලද භාණ්ඩ සදහා හෝ ඉටුකරන ලද සේවාවන් සදහා මීට ඉහතදී ඉල්ලා නැති බවට සහතික කරමි.<br>
        கோரப்பட்ட தொகை, வழங்கப்பட்ட பொருட்களுக்கோ அல்லது வழங்கப்பட்ட சேவைகளுக்கோ இதற்கு முன்பு கோரப்படவில்லை என்று நான் உறுதிப்படுத்துகிறேன் .<br>
        ඉහත සදහන් සේවයන් සදහා රුපියල් {{ $invoice->amount_in_words }} මුදලක් ලැබුණු බවට සහතිකක් කරමි.
    </p>

   <!-- ================= FOOTER NOTES ================= -->
    <div class="mt-2 text-[10px] leading-4 grid grid-cols-2 gap-4">
        <!-- Left column (Sinhala) -->
        <div>
            රු/ரூ. <span>{{ number_format($invoice->total_amount, 2) }}</span> ශත <span></span> <br>
            දිනය <span>{{ now()->format('Y-m-d') }}</span>
        </div>

        <!-- Right column (Tamil) -->
        <div class="mt-3">
            <!-- Signature line with curly brace and stamp box -->
            <div class="flex items-center justify-between space-x-3">
                <!-- Left text (two-line label) -->
                <div class="text-left leading-tight">
                    <span class="block">කොන්ත්‍රාත්කරුගේ අත්සන</span>
                    <span class="block">ஒப்பந்தக்காரரின் கையொப்பம்</span>
                </div>

                <!-- Curly brace -->
                <div class="text-3xl font-bold leading-none">}</div>

                <!-- Stamp box -->
                <div class="border border-black p-2 text-center w-40 text-[9.5px] leading-tight">
                    <p>රු.100 ට වැඩි මුදලට <br> රු.1.00 ක මුද්දරයක් <br>ඇලවිය යුතුය.</p> <br>
                    <p>ரூ. 100க்கு மேல் மதிப்புள்ள எந்தவொரு தொகைக்கும் ரூ. 1.00 முத்திரை ஒட்டப்பட வேண்டும்.</p>
                </div>
            </div>

            <!-- Small note aligned to the right -->
            <div class="flex justify-end mt-1 text-[10px]">
                [අ.පි.බ.]
            </div>
        </div>

    </div>

</div>

<style>
    .dotted-fill {
  border-bottom: 1px dotted black;
  display: inline-block;
  width: 100%;
  text-align: start;
  padding-bottom: 2px;
}
    @media print {
        .no-print { display: none; }
        body { margin: 0; }
        @page {
            size: legal portrait;
            margin: 8mm;
        }
        .invoice-container {
            height: auto !important;
        }
    }
</style>
@endsection
