@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
    {{ __('Show User') }}
</h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold">User Details</h3>
                    <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                </div>

                <div class="space-y-4">
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <strong class="text-gray-500 dark:text-gray-400">Name:</strong>
                        <p class="mt-1">{{ $user->name }}</p>
                    </div>

                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <strong class="text-gray-500 dark:text-gray-400">Email:</strong>
                        <p class="mt-1">{{ $user->email }}</p>
                    </div>

                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <strong class="text-gray-500 dark:text-gray-400">Roles:</strong>
                        <div class="mt-2">
                            @if($user->getRoleNames()->isNotEmpty())
                                @foreach ($user->getRoleNames() as $role)
                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                        {{ $role }}
                                    </span>
                                @endforeach
                            @else
                                <p>No Roles Assigned</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

