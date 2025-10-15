@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <h2 class="text-2xl font-bold mb-4 flex justify-between items-center">
        <span>Wastage Logs</span>

        <!-- 🔍 Filter Form -->
        <form method="GET" action="{{ route('stock.wastageLogs') }}" class="flex items-center space-x-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product..."
                   class="border rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-300">

            <select name="status" class="border rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-300">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
            </select>

            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('stock.wastageLogs') }}" class="text-gray-500 text-sm hover:text-red-600">Clear</a>
            @endif
        </form>
    </h2>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-3">
            {{ session('success') }}
        </div>
    @endif

    <!-- 🧾 Wastage Table -->
    <table class="min-w-full bg-white border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">#</th>
                <th class="p-2 border">Product</th>
                <th class="p-2 border">Quantity</th>
                <th class="p-2 border">Reason</th>
                <th class="p-2 border">Status</th>
                <th class="p-2 border text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $index => $log)
            <tr class="border-t hover:bg-gray-50">
                <td class="p-2">{{ $logs->firstItem() + $index }}</td>
                <td class="p-2">{{ $log->product->name ?? '-' }}</td>
                <td class="p-2">{{ $log->quantity }}</td>
                <td class="p-2">{{ $log->reason ?? '-' }}</td>
                <td class="p-2">
                    <span class="px-2 py-1 rounded text-sm 
                        {{ $log->status === 'returned' ? 'bg-green-200 text-green-700' : 'bg-yellow-200 text-yellow-700' }}">
                        {{ ucfirst($log->status) }}
                    </span>
                </td>
                <td class="p-2 text-center">
                    @if ($log->status !== 'returned')
                        <form action="{{ route('stock.markReturned', $log->id) }}" method="POST" class="inline">
                            @csrf
                            <!-- ✅ Return Icon -->
                            <button type="submit" title="Mark as Returned"
                                class="text-blue-500 hover:text-blue-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="2" stroke="currentColor" class="w-6 h-6 inline">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 110 12h-3" />
                                </svg>
                            </button>
                        </form>
                    @else
                        <span class="text-gray-500 italic">Returned</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-3 text-center text-gray-500">No wastage logs available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>
@endsection