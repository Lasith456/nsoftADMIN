@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Edit Role</h2>
        <a class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300" href="{{ route('roles.index') }}">
            Back
        </a>
    </div>

    @if (count($errors) > 0)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('roles.update', $role->id) }}">
        @csrf
        @method('PUT')
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name:</label>
                <input type="text" name="name" placeholder="Name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="{{ $role->name }}">
            </div>

            <div>
                <strong class="block text-sm font-medium text-gray-700">Permissions:</strong>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($permission as $value)
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" name="permission[]" value="{{ $value->id }}" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" {{ in_array($value->id, $rolePermissions) ? 'checked' : '' }}>
                            <span class="text-gray-700">{{ $value->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="text-center pt-4">
                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-300">
                    Submit
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
