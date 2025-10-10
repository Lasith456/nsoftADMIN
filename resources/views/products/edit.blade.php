@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2"
     x-data="productForm({
         product: {{ json_encode($product) }},
         departments: {{ json_encode($departments) }},
         companies: {{ json_encode($companies) }}
     })">

    <!-- ================= MODAL FOR NEW DEPARTMENT ================= -->
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

            <form x-show="modalType === 'department'" @submit.prevent="storeDepartment" class="space-y-4">
                <div>
                    <label for="new_department_name" class="block text-sm font-medium">Department Name*</label>
                    <input type="text" id="new_department_name" x-model="newDepartment.name"
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600
                           rounded-md shadow-sm py-2 px-3" required>
                </div>
                <div class="text-right space-x-2">
                    <button type="button" @click="isModalOpen = false"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MAIN PRODUCT EDIT FORM ================= -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="product-form" action="{{ route('products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Header -->
            <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Edit Product</h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('products.index') }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md
                              hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold">Back</a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                        Update Product
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

            <!-- Basic Product Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium">Name*</label>
                    <input type="text" name="name" id="name" x-model="mainForm.name"
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600
                           rounded-md py-2 px-3" required>
                </div>

                <div>
                    <label for="appear_name" class="block text-sm font-medium">Appear Name*</label>
                    <input type="text" name="appear_name" id="appear_name" x-model="mainForm.appear_name"
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600
                           rounded-md py-2 px-3" required>
                </div>

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
                    <input type="hidden" name="department_id" x-model="mainForm.department_id">
                </div>

                <div>
                    <label class="block text-sm font-medium">Product Type*</label>
                    <div class="mt-2 flex items-center space-x-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="product_type" value="pack" class="text-indigo-600"
                                   x-model="mainForm.product_type">
                            <span class="ml-2 text-sm">Pack</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="product_type" value="case" class="text-indigo-600"
                                   x-model="mainForm.product_type">
                            <span class="ml-2 text-sm">Case</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="units_per_case" class="block text-sm font-medium">Units Per Case*</label>
                    <input type="number" name="units_per_case" id="units_per_case"
                           x-model.number="mainForm.units_per_case"
                           :disabled="mainForm.product_type === 'pack'"
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600
                           rounded-md py-2 px-3" required>
                </div>

                <div>
                    <label for="unit_of_measure" class="block text-sm font-medium">Unit of Measure*</label>
                    <input list="units" name="unit_of_measure" id="unit_of_measure" x-model="mainForm.unit_of_measure"
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

                <div>
                    <label for="reorder_qty" class="block text-sm font-medium">Reorder Level*</label>
                    <input type="number" name="reorder_qty" id="reorder_qty"
                           x-model.number="mainForm.reorder_qty"
                           class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600
                           rounded-md py-2 px-3" required>
                </div>
            </div>

            <!-- ================= COMPANY AND CUSTOMER PRICES ================= -->
            <div class="mt-6"
                 x-data="companyCustomerPriceHandler({
                    companies: {{ json_encode($companies) }},
                    existingPrices: {{ json_encode($product->companyPrices()->get()) }},
                    customerPrices: {{ json_encode($product->customerPrices()->get()) }}
                 })" x-init="init()">

                <h3 class="text-lg font-semibold mb-2">Company and Customer Prices</h3>

                <template x-for="company in companyPrices" :key="company.id">
                    <div class="border border-gray-300 rounded-md mb-4 bg-gray-50 p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-semibold text-gray-700" x-text="company.company_name"></h4>
                            <label class="inline-flex items-center">
                                <input type="checkbox" x-model="company.showCustomers" @change="loadCustomers(company)"
                                       class="h-4 w-4 text-indigo-600 rounded">
                                <span class="ml-2 text-sm">Show customer-specific prices</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Default Selling Price</label>
                            <input type="number" step="0.01"
                                   :name="`company_prices[${company.id}][selling_price]`"
                                   x-model="company.selling_price"
                                   class="mt-1 block w-full border rounded-md px-2 py-1">
                        </div>

                        <template x-if="company.showCustomers">
                            <div class="mt-3">
                                <template x-if="company.customers.length === 0">
                                    <p class="text-sm text-gray-500 italic">Loading customers...</p>
                                </template>

                                <template x-if="company.customers.length > 0">
                                    <table class="w-full border border-gray-200 rounded-md mt-2">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="p-2 text-left text-sm">Customer</th>
                                                <th class="p-2 text-left text-sm">Selling Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="cust in company.customers" :key="cust.id">
                                                <tr>
                                                    <td class="p-2" x-text="cust.customer_name"></td>
                                                    <td class="p-2">
                                                        <input type="number" step="0.01"
                                                               :name="`customer_prices[${cust.id}][selling_price]`"
                                                               x-model="cust.selling_price"
                                                               placeholder="Same as company price"
                                                               class="border rounded-md px-2 py-1 w-full">
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- ================= TOGGLES ================= -->
            <div class="flex items-center space-x-6 pt-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" class="h-4 w-4 text-indigo-600 rounded"
                           x-model="mainForm.is_active">
                    <span class="ml-2 text-sm">Is Active</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_vat" class="h-4 w-4 text-indigo-600 rounded"
                           x-model="mainForm.is_vat">
                    <span class="ml-2 text-sm">Is VAT</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_clear" class="h-4 w-4 text-indigo-600 rounded"
                           x-model="mainForm.is_clear">
                    <span class="ml-2 text-sm">Always Clear Stock</span>
                </label>
            </div>
        </form>
    </div>
</div>

<!-- ================= ALPINE.JS SCRIPTS ================= -->
<script>
document.addEventListener('alpine:init', () => {

    /* ---------- PRODUCT FORM ---------- */
    Alpine.data('productForm', (initialData) => ({
        isModalOpen: false,
        modalType: '',
        modalTitle: '',
        modalMessage: '',
        modalSuccess: false,
        departments: initialData.departments,
        mainForm: {
            name: initialData.product.name,
            appear_name: initialData.product.appear_name,
            department_id: initialData.product.department_id,
            department_name: '',
            product_type: initialData.product.units_per_case > 1 ? 'case' : 'pack',
            units_per_case: initialData.product.units_per_case,
            unit_of_measure: initialData.product.unit_of_measure,
            reorder_qty: initialData.product.reorder_qty,
            is_active: initialData.product.is_active,
            is_vat: initialData.product.is_vat,
            is_clear: initialData.product.is_clear
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
        },

        openModal(type) {
            this.isModalOpen = true;
            this.modalType = type;
            this.modalMessage = '';
            this.modalSuccess = false;
            if (type === 'department') this.modalTitle = 'Add New Department';
        },

        updateDepartmentId() {
            const options = document.getElementById('departments-list').options;
            const selected = Array.from(options).find(opt => opt.value === this.mainForm.department_name);
            this.mainForm.department_id = selected ? selected.dataset.id : '';
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
                    this.modalMessage = 'Failed to create department.';
                    this.modalSuccess = false;
                }
            });
        }
    }));

    /* ---------- COMPANY + CUSTOMER PRICE HANDLER ---------- */
    Alpine.data('companyCustomerPriceHandler', (initialData) => ({
        companyPrices: [],

        init() {
            const existingPrices = initialData.existingPrices.reduce((acc, p) => {
                acc[p.company_id] = p.selling_price;
                return acc;
            }, {});

            this.companyPrices = initialData.companies.map(c => ({
                id: c.id,
                company_name: c.company_name,
                selling_price: existingPrices[c.id] ?? '',
                showCustomers: false,
                customers: []
            }));
        },

        loadCustomers(company) {
            if (!company.showCustomers) return;

            fetch(`/companies/${company.id}/customers`)
                .then(res => res.json())
                .then(data => {
                    company.customers = data.map(c => {
                        const existing = initialData.customerPrices.find(cp => cp.customer_id === c.id);
                        return {
                            id: c.id,
                            customer_name: c.customer_name,
                            selling_price: existing ? existing.selling_price : company.selling_price
                        };
                    });
                })
                .catch(() => {
                    company.customers = [];
                });
        },
    }));
});
</script>
@endsection
