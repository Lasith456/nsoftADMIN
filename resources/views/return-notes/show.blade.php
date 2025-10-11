@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="return-note-details" class="bg-white shadow-lg rounded-lg max-w-5xl mx-auto p-10 border border-gray-400">

        {{-- HEADER & ACTIONS --}}
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h2 class="text-3xl font-bold text-gray-800">Return Note Details</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </a>
                <a href="{{ route('return-notes.index') }}"
                   class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-xs uppercase font-semibold">
                    Back to List
                </a>
                <button onclick="window.print()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-blue-700">
                    Print
                </button>
            </div>
        </div>

        {{-- COMPANY LETTERHEAD --}}
        <div class="text-center border-b border-gray-700 pb-4 mb-6">
            <h2 class="text-2xl font-extrabold uppercase">H.G.P.M. (PVT) LTD.</h2>
            <p class="text-sm">No: 412/B, Galle Road, Pamburana, Matara.</p>
            <p class="text-sm">Tel: 041 2229231 / 041 2224121 | Fax: 041 2224122 | Email: hgpm.ltd@sltnet.lk</p>
        </div>

        {{-- RETURN NOTE META DETAILS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 pb-4 border-b border-gray-300">
            <div>
                <p><strong>Return Note No:</strong> {{ $returnNote->return_note_id }}</p>
                <p><strong>Return Date:</strong> {{ \Carbon\Carbon::parse($returnNote->return_date)->format('d/m/Y') }}</p>
            </div>
            <div>
                <p><strong>Company:</strong> {{ $returnNote->company->company_name ?? $returnNote->company->name ?? 'N/A' }}</p>
                <p><strong>Customer:</strong> {{ $returnNote->customer->customer_name ?? $returnNote->customer->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p><strong>Agent:</strong> {{ $returnNote->agent->name ?? 'N/A' }}</p>
                <p>
                    <strong>Status:</strong>
                    <span class="px-2 inline-flex text-xs font-semibold rounded-full
                        @if($returnNote->status === 'Processed') bg-green-100 text-green-800
                        @elseif($returnNote->status === 'Ignored') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ ucfirst($returnNote->status ?? 'Pending') }}
                    </span>
                </p>
            </div>
        </div>

        {{-- RETURN REASON --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Reason for Return</h3>
            <p class="text-sm text-gray-700 leading-relaxed border border-gray-300 rounded-md p-3 bg-gray-50">
                {{ $returnNote->reason ?? 'No reason provided.' }}
            </p>
        </div>

        {{-- RETURNED ITEMS TABLE --}}
        <h3 class="text-lg font-bold text-gray-800 mb-4">Returned Product</h3>
        <table class="w-full border border-gray-700 text-sm mb-8">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-2 py-1 text-left">Product Description</th>
                    <th class="border px-2 py-1 text-right">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border px-2 py-1">
                        @if($returnNote->product)
                            <a href="{{ route('products.show', $returnNote->product->id) }}"
                               class="text-blue-600 hover:underline font-medium">
                               {{ $returnNote->product->name }}
                            </a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="border px-2 py-1 text-right">{{ $returnNote->quantity ?? '0' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- CERTIFICATION --}}
        <div class="mt-8 text-sm">
            <p>
                Certified that the above item(s) have been verified, inspected and recorded according to company
                policy. The return was acknowledged on the stated date and processed accordingly.
            </p>
        </div>

        {{-- SIGNATURE AREA --}}
        <div class="grid grid-cols-2 gap-10 mt-16 text-sm">
            <div>
                <p>Received & Verified By:</p>
                <div class="mt-12 border-t border-gray-600 w-64"></div>
                <p class="mt-2">Name:</p>
                <p>Designation:</p>
            </div>
            <div class="text-right">
                <p>For and on behalf of</p>
                <p class="font-bold">H.G.P.M. (PVT) LTD.</p>
                <div class="mt-12 border-t border-gray-600 w-64 ml-auto"></div>
                <p class="mt-2">Date: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

    </div>
</div>

{{-- PRINT STYLING --}}
<style>
    @media print {
        @page {
            margin: 20mm;
            size: A4 portrait;
            @top-left, @top-center, @top-right,
            @bottom-left, @bottom-center, @bottom-right { content: none; }
        }
        body * { visibility: hidden !important; }
        #return-note-details, #return-note-details * { visibility: visible !important; }
        #return-note-details {
            position: absolute;
            left: 0; top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
        }
        .print\:hidden { display: none !important; }
    }
</style>
@endsection
