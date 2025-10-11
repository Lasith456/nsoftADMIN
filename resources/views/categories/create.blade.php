@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Add Category</h2>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Category Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full border rounded-md py-2 px-3 dark:bg-gray-900 dark:text-white" required>
            </div>

            <div class="flex justify-end space-x-2">
                <a href="{{ route('categories.index') }}"
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md text-sm">Cancel</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
