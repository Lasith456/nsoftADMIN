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
        <div class="flex gap-3 mb-4 text-sm">
            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Pending – Can Ignore & Create PO</span>
            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Ignored – Cannot Create PO</span>
            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Processed – Finalized</span>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Return Note ID</th>
                        <th class="px-4 py-2 text-left">Company</th>
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
                            <td class="px-4 py-2 font-semibold">{{ $note->return_note_id }}</td>
                            <td class="px-4 py-2">{{ optional($note->company)->name ?? optional($note->company)->company_name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ optional($note->customer)->name ?? optional($note->customer)->customer_name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ optional($note->agent)->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $note->reason }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($note->return_date)->format('Y-m-d') }}</td>

                            {{-- STATUS BADGE --}}
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

                            {{-- ACTION BUTTONS --}}
                            <td class="px-4 py-2 text-center space-x-2">
                                @if($note->status === 'Pending')
                                    {{-- IGNORE --}}
                                    <form action="{{ route('return-notes.changeStatus', $note->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="Ignored">
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-red-600 text-white rounded text-xs font-semibold hover:bg-red-700"
                                            onclick="return confirm('Are you sure you want to ignore this Return Note?')">
                                            {{-- X-circle icon --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            
                                        </button>
                                    </form>

                                    {{-- CREATE PO --}}
                                    <form action="{{ route('return-notes.create-po', $note->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white rounded text-xs font-semibold hover:bg-blue-700">
                                            {{-- Clipboard-plus icon --}}
                                            PO
                                        </button>
                                    </form>
                                    <a href="{{ route('return-notes.show', $note->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-gray-600 text-white rounded text-xs font-semibold hover:bg-gray-700">
                                        View
                                        </a>
                                @elseif($note->status === 'Ignored')
                                    <button disabled
                                        class="px-3 py-1 bg-gray-400 text-white rounded text-xs font-semibold cursor-not-allowed">
                                        Ignored
                                    </button>
<a href="{{ route('return-notes.show', $note->id) }}"
   class="inline-flex items-center gap-1 px-3 py-1 bg-gray-600 text-white rounded text-xs font-semibold hover:bg-gray-700">
   View
</a>

                                @elseif($note->status === 'Processed')
                                    <button disabled
                                        class="px-3 py-1 bg-green-500 text-white rounded text-xs font-semibold cursor-not-allowed">
                                        Processed
                                    </button>
                                    <a href="{{ route('return-notes.show', $note->id) }}"
   class="inline-flex items-center gap-1 px-3 py-1 bg-gray-600 text-white rounded text-xs font-semibold hover:bg-gray-700">
   View
</a>

                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-500">No Return Notes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $returnNotes->links() }}
        </div>
    </div>
</div>
@endsection
