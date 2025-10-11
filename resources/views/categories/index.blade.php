@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Categories</h2>
            <a href="{{ route('categories.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Add Category</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full border divide-y divide-gray-300 dark:divide-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="p-2 text-left text-sm font-semibold">#</th>
                        <th class="p-2 text-left text-sm font-semibold">Name</th>
                        <th class="p-2 text-right text-sm font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($categories as $category)
                        <tr>
                            <td class="p-2 text-sm">{{ $loop->iteration }}</td>
                            <td class="p-2 text-sm">{{ $category->name }}</td>
                            <td class="p-2 text-right text-sm space-x-2">
                                <a href="{{ route('categories.edit', $category) }}"
                                   class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">Edit</a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $categories->links() }}</div>
    </div>
</div>
@endsection
