@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold text-black mb-4">Select Company</h2>
    <p class="text-gray-600 mb-6">Choose a company to view its report.</p>

    <form action="{{ route('reports.company') }}" method="GET" class="space-y-4">
        <div>
            <label for="company_id" class="block text-sm font-medium text-gray-700">Company</label>
            <select name="company_id" id="company_id" required
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                <option value="">-- Select Company --</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex space-x-2">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">
                Continue
            </button>
            <a href="{{ route('home') }}"
                class="px-4 py-2 bg-gray-300 text-black rounded-md text-xs uppercase font-semibold">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
