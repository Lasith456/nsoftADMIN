@extends('layouts.app')
@section('content')
<div class="max-w-lg mx-auto bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-4">Create Debit Note</h2>
    <form method="POST" action="{{ route('debit-notes.store') }}">
        @csrf
        <label class="block mb-2">Customer</label>
        <select name="customer_id" class="w-full border rounded p-2 mb-4" required>
            <option value="">Select Customer</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
            @endforeach
        </select>

        <label class="block mb-2">Amount (LKR)</label>
        <input type="number" step="0.01" name="amount" class="w-full border rounded p-2 mb-4" required>

        <label class="block mb-2">Issued Date</label>
        <input type="date" name="issued_date" value="{{ date('Y-m-d') }}" class="w-full border rounded p-2 mb-4" required>

        <label class="block mb-2">Reason</label>
        <textarea name="reason" class="w-full border rounded p-2 mb-4"></textarea>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
@endsection
