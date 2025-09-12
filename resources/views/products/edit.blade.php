@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Product</h2>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                Back
            </a>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Whoops! Something went wrong.</p>
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('name', $product->name) }}" required>
                </div>
                <!-- Appear Name -->
                <div>
                    <label for="appear_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Appear Name <span class="text-red-500">*</span></label>
                    <input type="text" name="appear_name" id="appear_name" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('appear_name', $product->appear_name) }}" required>
                </div>
                 <!-- Department -->
                <div>
                    <label for="department_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department <span class="text-red-500">*</span></label>
                    <input list="departments-list" id="department_name" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('department_name', $product->department->name ?? '') }}" required>
                    <datalist id="departments-list">
                        @foreach($departments as $department)
                            <option value="{{ $department->name }}" data-id="{{ $department->id }}"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="department_id" id="department_id" value="{{ old('department_id', $product->department_id) }}">
                </div>
                <!-- Sub-Department -->
                <div>
                    <label for="sub_department_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sub-Department <span class="text-red-500">*</span></label>
                    <input list="subdepartments-list" id="sub_department_name" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('sub_department_name', $product->subDepartment->name ?? '') }}" required>
                    <datalist id="subdepartments-list">
                        {{-- Populated by JavaScript --}}
                    </datalist>
                    <input type="hidden" name="sub_department_id" id="sub_department_id" value="{{ old('sub_department_id', $product->sub_department_id) }}">
                </div>
                <!-- Supplier -->
                <div>
                    <label for="supplier_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                    <input list="suppliers-list" id="supplier_name" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('supplier_name', $product->supplier->supplier_name ?? '') }}">
                    <datalist id="suppliers-list">
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->supplier_name }}" data-id="{{ $supplier->id }}"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="supplier_id" id="supplier_id" value="{{ old('supplier_id', $product->supplier_id) }}">
                </div>
                <!-- Units Per Case -->
                <div>
                    <label for="units_per_case" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Units Per Case <span class="text-red-500">*</span></label>
                    <input type="number" name="units_per_case" id="units_per_case" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('units_per_case', $product->units_per_case) }}" required>
                </div>
                <!-- Unit of Measure -->
                <div>
                    <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of Measure <span class="text-red-500">*</span></label>
                    <input list="units" name="unit_of_measure" id="unit_of_measure" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('unit_of_measure', $product->unit_of_measure) }}" required>
                    <datalist id="units">
                        <option value="KG"></option>
                        <option value="Litre"></option>
                        <option value="Pieces"></option>
                        <option value="Pack"></option>
                        <option value="Box"></option>
                        <option value="Bottle"></option>
                    </datalist>
                </div>
                <!-- Cost Price -->
                <div>
                    <label for="cost_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Price <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="cost_price" id="cost_price" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('cost_price', $product->cost_price) }}" required>
                </div>
                <!-- Selling Price -->
                <div>
                    <label for="selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selling Price <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="selling_price" id="selling_price" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('selling_price', $product->selling_price) }}" required>
                </div>
                 <!-- Reorder Level -->
                <div>
                    <label for="reorder_qty" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reorder Level <span class="text-red-500">*</span></label>
                    <input type="number" name="reorder_qty" id="reorder_qty" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('reorder_qty', $product->reorder_qty) }}" required>
                </div>

                <!-- Toggles -->
                <div class="lg:col-span-3 flex items-center space-x-6 pt-4">
                     <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-indigo-600 rounded" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Is Active</label>
                    </div>
                     <div class="flex items-center">
                        <input type="checkbox" name="is_vat" id="is_vat" class="h-4 w-4 text-indigo-600 rounded" {{ old('is_vat', $product->is_vat) ? 'checked' : '' }}>
                        <label for="is_vat" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Is VAT</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_clear" id="is_clear" class="h-4 w-4 text-indigo-600 rounded" {{ old('is_clear', $product->is_clear) ? 'checked' : '' }}>
                        <label for="is_clear" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Is Clear</label>
                    </div>
                </div>
            </div>

            <div class="text-right pt-8">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const departmentInput = document.getElementById('department_name');
        const departmentIdInput = document.getElementById('department_id');
        const subDepartmentInput = document.getElementById('sub_department_name');
        const subDepartmentIdInput = document.getElementById('sub_department_id');
        const subDepartmentDatalist = document.getElementById('subdepartments-list');
        const supplierInput = document.getElementById('supplier_name');
        const supplierIdInput = document.getElementById('supplier_id');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- Department Logic ---
        departmentInput.addEventListener('change', function() {
            const departmentName = this.value;
            let departmentId = '';
            const options = document.getElementById('departments-list').options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === departmentName) {
                    departmentId = options[i].dataset.id;
                    break;
                }
            }
            departmentIdInput.value = departmentId;
            fetchSubDepartments(departmentId);
        });

        // --- Sub-Department Logic ---
        subDepartmentInput.addEventListener('change', function() {
            const subDepartmentName = this.value;
            let subDepartmentId = '';
            const options = subDepartmentDatalist.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === subDepartmentName) {
                    subDepartmentId = options[i].dataset.id;
                    break;
                }
            }
            subDepartmentIdInput.value = subDepartmentId;
        });

        // --- Supplier Logic ---
        supplierInput.addEventListener('change', function() {
            const supplierName = this.value;
            let supplierId = '';
            const options = document.getElementById('suppliers-list').options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === supplierName) {
                    supplierId = options[i].dataset.id;
                    break;
                }
            }
            supplierIdInput.value = supplierId;
        });
        
        function fetchSubDepartments(departmentId) {
            subDepartmentInput.value = '';
            subDepartmentIdInput.value = '';
            subDepartmentDatalist.innerHTML = '';
            if (!departmentId) return;

            fetch(`{{ route("products.getSubDepartments") }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ department_id: departmentId })
            })
            .then(response => response.json())
            .then(data => {
                const currentSubDepartmentId = '{{ $product->sub_department_id }}';
                data.forEach(function(subDepartment) {
                    const option = document.createElement('option');
                    option.value = subDepartment.name;
                    option.dataset.id = subDepartment.id;
                    subDepartmentDatalist.appendChild(option);
                    
                    // **THE FIX IS HERE**: This part now sets the hidden ID field correctly on load
                    if (subDepartment.id == currentSubDepartmentId) {
                        subDepartmentInput.value = subDepartment.name;
                        subDepartmentIdInput.value = subDepartment.id;
                    }
                });
            })
            .catch(error => console.error('Error fetching sub-departments:', error));
        }
        
        // --- Initial Load Logic ---
        const initialDepartmentId = departmentIdInput.value;
        if (initialDepartmentId) {
            fetchSubDepartments(initialDepartmentId);
        }
    });
</script>
@endsection

