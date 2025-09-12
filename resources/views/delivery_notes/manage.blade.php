@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Manage Delivery Note Status</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Update the status of "Processing" delivery notes.</p>
        
        @if ($message = Session::get('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p>{{ $message }}</p></div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">DN ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($deliveryNotes as $dn)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-200">{{ $dn->delivery_note_id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $dn->vehicle->vehicle_no ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Processing</span></td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <form action="{{ route('delivery-notes.updateStatus', $dn->id) }}" method="POST" class="inline-flex">
                                @csrf
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="text-green-600 hover:text-green-900 mr-4">Mark as Delivered</button>
                            </form>
                            <form action="{{ route('delivery-notes.updateStatus', $dn->id) }}" method="POST" class="inline-flex">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="text-red-600 hover:text-red-900">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4">No delivery notes are currently processing.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
