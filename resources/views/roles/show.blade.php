@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 sm:mb-0">Role Details</h2>
        <a class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors duration-200" href="{{ url()->previous() }}">
            Back to List
        </a>
    </div>

    <div class="space-y-4">
        <div>
            <strong class="block text-sm font-medium text-gray-500">Name:</strong>
            <p class="mt-1 text-lg text-gray-900">{{ $role->name }}</p>
        </div>
        <hr>
        <div>
            <strong class="block text-sm font-medium text-gray-500">Permissions:</strong>
            <div class="mt-2 flex flex-wrap gap-2">
                @if(!empty($rolePermissions))
                    @foreach($rolePermissions as $v)
                        <span class="px-3 py-1 text-sm font-semibold leading-5 rounded-full bg-blue-100 text-blue-800">
                            {{ $v->name }}
                        </span>
                    @endforeach
                @else
                     <p class="text-gray-600">No Permissions Assigned</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
