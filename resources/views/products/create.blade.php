@extends('layouts.app')

@section('content')
<div class="container mx-auto" 
     x-data="productForm({ departments: {{ json_encode($departments) }}, suppliers: {{ json_encode($suppliers) }} })">
     
    <!-- Add New Modal -->
    <div x-show="isModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" 
         @click.away="isModalOpen = false" 
         x-cloak>
         
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4" x-text="modalTitle"></h2>
            <div x-show="modalMessage" 
                 class="p-4 mb-4 text-sm rounded-lg" 
                 :class="modalSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                 x-text="modalMessage"></div>

            <!-- Department Form -->
            <form x-show="modalType === 'department'" @submit.prevent="storeDepartment" class="space-y-4">
                <div>
                    <label for="new_department_name" class="block text-sm font-medium">Department Name*</label>
                    <input type="text" id="new_department_name" x-model="newDepartment.name" 
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 
                           rounded-md shadow-sm py-2 px-3" required>
                </div>
                <div class="text-right space-x-2">
                    <button type="button" @click="isModalOpen = false" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Main Product Form -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="product-form" action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Add New Product</h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('products.index') }}" 
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md 
                              hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold">Back</a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                        Save Product
                    </button>
                </div>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-medium">Name*</label>
                    <input type="text" name="name" id="name" x-model="mainForm.name" 
                           @input="mainForm.appear_name = $event.target.value" 
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 
                           rounded-md py-2 px-3" required>
                </div>

                <!-- Appear Name -->
                <div>
                    <label for="appear_name" class="block text-sm font-medium">Appear Name*</label>
                    <input type="text" name="appear_name" id="appear_name" x-model="mainForm.appear_name" 
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 
                           rounded-md py-2 px-3" required>
                </div>
                
                <!-- Department -->
                <div>
                    <label for="department_name" class="block text-sm font-medium">Department*</label>
                    <div class="flex items-center space-x-2">
                        <input list="departments-list" id="department_name" x-model="mainForm.department_name" 
                               @change="updateDepartmentId" 
                               class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 
                               rounded-md py-2 px-3">
                        <button type="button" @click="openModal('department')" 
                                class="mt-1 px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded-md">+</button>
                    </div>
                    <datalist id="departments-list">
                        <template x-for="dept in departments" :key="dept.id">
                            <option :value="dept.name" :data-id="dept.id"></option>
                        </template>
                    </datalist>
                    <input type="hidden" name="department_id" id="department_id" x-model="mainForm.department_id">
                </div>

                <!-- Supplier -->
                <div>
                    <label for="supplier_name" class="block text-sm font-medium">Supplier</label>
                    <input list="suppliers-list" id="supplier_name" x-model="mainForm.supplier_name" 
                           @change="updateSupplierId" 
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 
                           rounded-md py-2 px-3">
                    <datalist id="suppliers-list">
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->supplier_name }}" data-id="{{ $supplier->id }}"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="supplier_id" id="supplier_id" x-model="mainForm.supplier_id">
                </div>

                <!-- Product Type -->
                <div>
                    <label class="block text-sm font-medium">Product Type*</label>
                    <div class="mt-2 flex items-center space-x-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="product_type" value="pack" class="text-indigo-600" x-model="mainForm.product_type">
                            <span class="ml-2 text-sm">Pack</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="product_type" value="case" class="text-indigo-600" x-model="mainForm.product_type">
                            <span class="ml-2 text-sm">Case</span>
                        </label>
                    </div>
                </div>

                <!-- Units Per Case -->
                <div>
                    <label for="units_per_case" class="block text-sm font-medium">Units Per Case*</label>
                    <input type="number" name="units_per_case" id="units_per_case" x-model.number="mainForm.units_per_case" 
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 
                           rounded-md py-2 px-3" :disabled="mainForm.product_type === 'pack'" required>
                </div>

                <!-- Unit of Measure -->
                <div>
                    <label for="unit_of_measure" class="block text-sm font-medium">Unit of Measure*</label>
                    <input list="units" name="unit_of_measure" id="unit_of_measure" value="{{ old('unit_of_measure') }}" 
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 
                           rounded-md py-2 px-3" required>
                    <datalist id="units">
                        <option value="Pieces"></option>
                        <option value="KG"></option>
                        <option value="Litre"></option>
                        <option value="Pack"></option>
                        <option value="Box"></option>
                        <option value="Bottle"></option>
                    </datalist>
                </div>

                <!-- Reorder Qty -->
                <div>
                    <label for="reorder_qty" class="block text-sm font-medium">Reorder Level*</label>
                    <input type="number" name="reorder_qty" id="reorder_qty" value="{{ old('reorder_qty', 0) }}" 
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 
                           rounded-md py-2 px-3" required>
                </div>
            </div>

            <!-- Company Wise Prices -->
            @isset($companies)
            <div class="lg:col-span-3 mt-6">
                <h3 class="text-lg font-semibold mb-2">Company Wise Prices</h3>
                <table class="w-full border border-gray-300 rounded-md">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Company</th>
                            <th class="p-2 text-left">Cost Price</th>
                            <th class="p-2 text-left">Selling Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companies as $company)
                            <tr>
                                <td class="p-2">{{ $company->company_name }}</td>
                                <td class="p-2">
                                    <input type="number" step="0.01"
                                           name="company_prices[{{ $company->id }}][cost_price]"
                                           class="w-full border rounded-md px-2 py-1"
                                           value="{{ old('company_prices.' . $company->id . '.cost_price') }}">
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01"
                                           name="company_prices[{{ $company->id }}][selling_price]"
                                           class="w-full border rounded-md px-2 py-1"
                                           value="{{ old('company_prices.' . $company->id . '.selling_price') }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endisset

            <!-- Toggles -->
            <div class="lg:col-span-3 flex items-center space-x-6 pt-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" class="h-4 w-4 text-indigo-600 rounded" checked>
                    <span class="ml-2 text-sm">Is Active</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_vat" class="h-4 w-4 text-indigo-600 rounded">
                    <span class="ml-2 text-sm">Is VAT</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_clear" class="h-4 w-4 text-indigo-600 rounded">
                    <span class="ml-2 text-sm">Always Clear Stock</span>
                </label>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productForm', (initialData) => ({
            isModalOpen: false,
            modalType: '',
            modalTitle: '',
            modalMessage: '',
            modalSuccess: false,
            departments: initialData.departments,
            suppliers: initialData.suppliers,
            mainForm: {
                name: '{{ old('name') }}',
                appear_name: '{{ old('appear_name') }}',
                department_id: '{{ old('department_id') }}',
                department_name: '',
                supplier_id: '{{ old('supplier_id') }}',
                supplier_name: '',
                product_type: '{{ old('product_type', 'pack') }}',
                units_per_case: '{{ old('units_per_case', 1) }}',
            },
            newDepartment: { name: '' },
            
            init() {
                this.$watch('mainForm.product_type', (value) => {
                    if (value === 'pack') this.mainForm.units_per_case = 1;
                });

                if (this.mainForm.department_id) {
                    const dept = this.departments.find(d => d.id == this.mainForm.department_id);
                    if (dept) this.mainForm.department_name = dept.name;
                }
                if (this.mainForm.supplier_id) {
                    const supp = this.suppliers.find(s => s.id == this.mainForm.supplier_id);
                    if (supp) this.mainForm.supplier_name = supp.supplier_name;
                }
            },

            openModal(type) {
                this.isModalOpen = true;
                this.modalType = type;
                this.modalMessage = '';
                this.modalSuccess = false;
                if (type === 'department') {
                    this.modalTitle = 'Add New Department';
                }
            },

            updateDepartmentId() {
                const options = document.getElementById('departments-list').options;
                const selected = Array.from(options).find(opt => opt.value === this.mainForm.department_name);
                this.mainForm.department_id = selected ? selected.dataset.id : '';
            },
            
            updateSupplierId() {
                const options = document.getElementById('suppliers-list').options;
                const selected = Array.from(options).find(opt => opt.value === this.mainForm.supplier_name);
                this.mainForm.supplier_id = selected ? selected.dataset.id : '';
            },

            storeDepartment() {
                fetch('{{ route("departments.api.store") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.newDepartment)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.id) {
                        this.departments.push(data);
                        this.mainForm.department_name = data.name;
                        this.mainForm.department_id = data.id;
                        this.isModalOpen = false;
                        this.newDepartment.name = '';
                    } else {
                        this.modalMessage = 'Failed to create department. It might already exist.';
                        this.modalSuccess = false;
                    }
                });
            }
        }));
    });
</script>
@endsection
