@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-2xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Add New Company</h2>
            <a href="{{ route('companies.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                Back
            </a>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
            <p class="font-bold">Whoops!</p>
            <p>There were some problems with your input.</p>
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('companies.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Company Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="company_name" id="company_name"
                           value="{{ old('company_name') }}"
                           class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200"
                           placeholder="Enter company name" required>
                </div>

                <div class="text-right">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                        Save Company
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
