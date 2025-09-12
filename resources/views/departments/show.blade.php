@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-2xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Department Details</h2>
              <a href="{{ route('departments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back to List
                </a>
                            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back
            </a>
        </div>

        <div class="space-y-4">
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200">Name:</strong>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $department->name }}</p>
            </div>
            
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200">Sub-Departments:</strong>
                @if($department->subDepartments->isNotEmpty())
                    <ul class="list-disc pl-5 mt-2 text-gray-600 dark:text-gray-400">
                        @foreach ($department->subDepartments as $subDepartment)
                            <li>{{ $subDepartment->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600 dark:text-gray-400 mt-1">No sub-departments found for this department.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

