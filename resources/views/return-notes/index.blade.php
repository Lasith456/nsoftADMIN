@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Return Notes</h2>
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                Back
            </a>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- STATUS LEGEND --}}
        <div class="flex flex-wrap gap-3 mb-4 text-sm">
            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Pending – Can Ignore & Create PO</span>
            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Ignored – Cannot Create PO</span>
            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Processed – Finalized</span>
        </div>

        {{-- FILTERS --}}
        <form action="{{ route('return-notes.index') }}" method="GET"
            class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 space-y-3 md:space-y-0 md:space-x-3">

            {{-- Company Filter --}}
            <div class="flex items-center space-x-2">
                <label for="company_id" class="text-sm font-medium text-gray-700 dark:text-gray-300">Company:</label>
                <select name="company_id" id="company_id"
                        class="border border-gray-300 dark:border-gray-600 rounded-md p-2 text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100"
                        onchange="this.form.submit()">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date Range --}}
            <div class="flex items-center space-x-2">
                <label for="from_date" class="text-sm font-medium text-gray-700 dark:text-gray-300">From:</label>
                <input type="date" name="from_date" id="from_date"
                    value="{{ request('from_date') }}"
                    class="border border-gray-300 dark:border-gray-600 rounded-md p-2 text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100">

                <label for="to_date" class="text-sm font-medium text-gray-700 dark:text-gray-300">To:</label>
                <input type="date" name="to_date" id="to_date"
                    value="{{ request('to_date') }}"
                    class="border border-gray-300 dark:border-gray-600 rounded-md p-2 text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100">

                {{-- Filter & Clear Buttons --}}
                <div class="flex items-center space-x-2">
                    <button type="submit"
                            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md">
                        Filter
                    </button>

                    <a href="{{ route('return-notes.index') }}"
                    class="px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-xs font-semibold rounded-md">
                    Clear
                    </a>
                </div>
            </div>


        </form>


        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Return Note ID</th>
                        <th class="px-4 py-2 text-left">Receive Note ID</th>
                        <th class="px-4 py-2 text-left">Customer</th>
                        <th class="px-4 py-2 text-left">Agent</th>
                        <th class="px-4 py-2 text-left">Reason</th>
                        <th class="px-4 py-2 text-left">Return Date</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($returnNotes as $note)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            {{-- Return Note ID --}}
                            <td class="px-4 py-2 font-semibold">{{ $note->return_note_id }}</td>

                            {{-- Receive Note ID --}}
                            <td class="px-4 py-2">
                                {{ $note->receiveNote?->receive_note_id ?? '—' }}
                            </td>

                            {{-- Customer --}}
                            <td class="px-4 py-2">{{ optional($note->customer)->name ?? optional($note->customer)->customer_name ?? '-' }}</td>

                            {{-- Agent --}}

                            <td class="px-4 py-2">{{ optional($note->agent)->name ?? '-' }}</td>


                            {{-- Reason --}}
                            <td class="px-4 py-2">{{ $note->reason ?? '-' }}</td>

                            {{-- Return Date --}}
                            <td class="px-4 py-2">{{ $note->return_date ? \Carbon\Carbon::parse($note->return_date)->format('Y-m-d') : '-' }}</td>

                            {{-- Status --}}
                            <td class="px-4 py-2 text-center">
                                @php
                                    $statusColor = match($note->status) {
                                        'Pending' => 'bg-yellow-100 text-yellow-800',
                                        'Processed' => 'bg-green-100 text-green-800',
                                        'Ignored' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded text-sm font-medium {{ $statusColor }}">
                                    {{ $note->status ?? 'Pending' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-2 text-center space-x-2">
                                @if($note->status === 'Pending')
                                    <form action="{{ route('return-notes.changeStatus', $note->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="Ignored">
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700"
                                                onclick="return confirm('Are you sure you want to ignore this Return Note?')">
                                            Ignore
                                        </button>
                                    </form>

                                    <form action="{{ route('return-notes.create-po', $note->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
                                            Create PO
                                        </button>
                                    </form>

                                    <a href="{{ route('return-notes.show', $note->id) }}" class="px-3 py-1 bg-gray-600 text-white rounded text-xs hover:bg-gray-700">
                                        View
                                    </a>
                                @elseif($note->status === 'Ignored')
                                    <button disabled class="px-3 py-1 bg-gray-400 text-white rounded text-xs">Ignored</button>
                                    <a href="{{ route('return-notes.show', $note->id) }}" class="px-3 py-1 bg-gray-600 text-white rounded text-xs hover:bg-gray-700">
                                        View
                                    </a>
                                @elseif($note->status === 'Processed')
                                    <button disabled class="px-3 py-1 bg-green-500 text-white rounded text-xs">Processed</button>
                                    <a href="{{ route('return-notes.show', $note->id) }}" class="px-3 py-1 bg-gray-600 text-white rounded text-xs hover:bg-gray-700">
                                        View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-gray-500">No Return Notes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $returnNotes->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
