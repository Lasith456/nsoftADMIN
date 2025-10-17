<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:product-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }

    /* ============================================================
       PRODUCT LIST
    ============================================================ */
    public function index(Request $request): View
    {
        $query = Product::with(['department']);

        // 🔍 Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('product_id', 'LIKE', "%{$search}%")
                    ->orWhere('appear_name', 'LIKE', "%{$search}%");
            });
        }

        // 🏷 Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $products = $query->latest()->paginate(10);
        $departments = Department::orderBy('name')->get();

        return view('products.index', compact('products', 'departments'));
    }

    /* ============================================================
       PRODUCT CREATE VIEW
    ============================================================ */
    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        $companies = Company::orderBy('company_name')->get();
        return view('products.create', compact('departments', 'companies'));
    }

    /* ============================================================
       STORE NEW PRODUCT
    ============================================================ */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'appear_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'product_type' => 'required|in:pack,case',
            'units_per_case' => 'required_if:product_type,case|nullable|integer|min:1',
            'unit_of_measure' => 'required|string|max:255',
            'reorder_qty' => 'required|integer|min:0',
            'company_prices' => 'nullable|array',
            'company_prices.*.selling_price' => 'nullable|numeric|min:0',
            'customer_prices' => 'nullable|array',
            'customer_prices.*.selling_price' => 'nullable|numeric|min:0',
        ]);

        // Default unit logic
        $unitsPerCase = $request->product_type === 'pack'
            ? 1
            : max(1, (int) $request->input('units_per_case', 1));

        DB::transaction(function () use ($request, $unitsPerCase) {
            // Create product
            $product = Product::create([
                'name' => $request->name,
                'appear_name' => $request->appear_name,
                'department_id' => $request->department_id,
                'units_per_case' => $unitsPerCase,
                'unit_of_measure' => $request->unit_of_measure,
                'reorder_qty' => $request->reorder_qty,
                'is_active' => $request->boolean('is_active'),
                'is_vat' => $request->boolean('is_vat'),
                'is_clear' => $request->boolean('is_clear'),
                'separate_department_invoice' => $request->boolean('separate_department_invoice'),
                'discount' => (float) $request->input('discount', 0.00),
            ]);

            // Save company-level selling prices
            foreach ((array) $request->company_prices as $companyId => $price) {
                $product->companyPrices()->create([
                    'company_id' => $companyId,
                    'selling_price' => $price['selling_price'] ?? 0,
                    'cost_price' => $price['cost_price'] ?? null, // safe if still in schema
                ]);
            }

            // Save optional customer-level overrides
            foreach ((array) $request->customer_prices as $customerId => $price) {
                $product->customerPrices()->create([
                    'customer_id' => $customerId,
                    'selling_price' => $price['selling_price'] ?? 0,
                ]);
            }

            // Save company-wise department links
            foreach ((array) $request->input('company_departments', []) as $companyId => $dept) {
                if (!empty($dept['department_id'])) {
                    $product->productDepartmentWise()->create([
                        'company_id' => $companyId,
                        'department_id' => $dept['department_id'],
                    ]);
                }
            }

        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /* ============================================================
       SHOW SINGLE PRODUCT
    ============================================================ */
    public function show(Product $product): View
    {
        // Load all required relations at once (Eager Loading)
        $product->load([
            'department',
            'companyPrices.company',
            'customerPrices.customer',
            'productDepartmentWise.company',
            'productDepartmentWise.department',
        ]);
        return view('products.show', compact('product'));
    }


    /* ============================================================
       EDIT PRODUCT VIEW
    ============================================================ */
    public function edit(Product $product): View
    {
        $departments = Department::orderBy('name')->get();
        $companies = Company::orderBy('company_name')->get();
        $companyDepartments = $product->productDepartmentWise()->get();
        return view('products.edit', compact('product', 'departments', 'companies', 'companyDepartments'));
    }

    /* ============================================================
       UPDATE EXISTING PRODUCT
    ============================================================ */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'appear_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'product_type' => 'required|in:pack,case',
            'units_per_case' => 'nullable|integer|min:1',
            'unit_of_measure' => 'required|string|max:255',
            'reorder_qty' => 'required|integer|min:0',
            'company_prices' => 'nullable|array',
            'company_prices.*.selling_price' => 'nullable|numeric|min:0',
            'customer_prices' => 'nullable|array',
            'customer_prices.*.selling_price' => 'nullable|numeric|min:0',
            'company_departments' => 'nullable|array',
            'company_departments.*.department_id' => 'nullable|exists:departments,id',
        ]);

        // ✅ Safe handling for units per case
        $unitsPerCase = $request->product_type === 'pack'
            ? 1
            : max(1, (int) $request->input('units_per_case', 1));

        DB::transaction(function () use ($request, $product, $unitsPerCase) {

            /* =========================
            🔹 1. Update main product
            ==========================*/
            $product->update([
                'name' => $request->name,
                'appear_name' => $request->appear_name,
                'department_id' => $request->department_id,
                'units_per_case' => $unitsPerCase,
                'unit_of_measure' => $request->unit_of_measure,
                'reorder_qty' => $request->reorder_qty,
                'is_active' => $request->boolean('is_active'),
                'is_vat' => $request->boolean('is_vat'),
                'is_clear' => $request->boolean('is_clear'),
                'separate_department_invoice' => $request->boolean('separate_department_invoice'),
                'discount' => $request->input('discount', 0.00),
            ]);


            /* ======================================
            🔹 2. Update / Create Company Prices
            =======================================*/
            foreach ((array) $request->input('company_prices', []) as $companyId => $prices) {
                if (is_array($prices)) {
                    $product->companyPrices()->updateOrCreate(
                        ['company_id' => $companyId],
                        [
                            'selling_price' => $prices['selling_price'] ?? 0,
                            'cost_price' => $prices['cost_price'] ?? null,
                        ]
                    );
                }
            }


            /* =============================================
            🔹 3. Update / Create Customer Price Overrides
            ==============================================*/
            foreach ((array) $request->input('customer_prices', []) as $customerId => $prices) {
                if (is_array($prices)) {
                    $product->customerPrices()->updateOrCreate(
                        ['customer_id' => $customerId],
                        ['selling_price' => $prices['selling_price'] ?? 0]
                    );
                }
            }


            /* ====================================================
            🔹 4. Update / Create Company-wise Department Mapping
            =====================================================*/
            foreach ((array) $request->input('company_departments', []) as $companyId => $dept) {
                $departmentId = $dept['department_id'] ?? null;

                // Skip if department ID is missing or invalid
                if (!$departmentId) continue;

                $product->productDepartmentWise()->updateOrCreate(
                    ['company_id' => $companyId],
                    ['department_id' => $departmentId]
                );
            }
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }


    /* ============================================================
       DELETE PRODUCT
    ============================================================ */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
