@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Agent Outstanding Payments</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                            <path d="M285.476 272.971L91.132 467.314c-9.373 
                                     9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 
                                     256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 
                                     24.569 9.373 33.941 0L285.475 239.03c9.373 
                                     9.372 9.373 24.568.001 33.941z"/>
                        </svg>
                    </li>
                    <li class="text-black">Agent Payments</li>
                </ol>
            </nav>
        </div>
        @can('payment-create')
            <a class="mt-3 md:mt-0 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md 
                      font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
               href="{{ route('payments.createBulkAgent') }}">
                Record Bulk Payment
            </a>
        @endcan
    </div>

    @if ($message = Session::get('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Agent ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Agent Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Agent Mobile</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase">Total Outstanding</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($agents as $agent)
                <tr>
                    <td class="px-4 py-2 text-sm font-medium text-black">{{ $agent->agent_id }}</td>
                    <td class="px-4 py-2 text-sm text-black">{{ $agent->name }}</td>
                    <td class="px-4 py-2 text-sm text-black">{{ $agent->contact_no }}</td>
                    <td class="px-4 py-2 text-sm text-right font-bold text-red-600">
                        {{ number_format($agent->outstanding_balance, 2) }}
                    </td>
                    <td class="px-4 py-2 text-right text-sm font-medium">
<a href="{{ route('payments.createBulkAgent', ['agent' => $agent->id]) }}"
   class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-green-700">
    Pay
</a>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-2 text-sm text-center text-gray-500">
                        No agents with outstanding payments found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
