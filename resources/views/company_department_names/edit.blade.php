@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
    <h2 class="text-xl font-bold mb-4">Edit Companywise Department Name</h2>

    <form action="{{ route('company_department_names.update', $companyDepartmentName->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700">Company</label>
            <select name="company_id" class="w-full border p-2 rounded" required>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" 
                        {{ $companyDepartmentName->company_id == $company->id ? 'selected' : '' }}>
                        {{ $company->company_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Department</label>
            <select name="department_id" class="w-full border p-2 rounded" required>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" 
                        {{ $companyDepartmentName->department_id == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Appear Name</label>
            <input type="text" name="appear_name" class="w-full border p-2 rounded" 
                   value="{{ $companyDepartmentName->appear_name }}" required>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('company_department_names.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
        </div>
    </form>
</div>
@endsection
