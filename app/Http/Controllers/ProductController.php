<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Department;
use App\Models\SubDepartment;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;



class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index','show']]);
        $this->middleware('permission:product-create', ['only' => ['create','store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $query = Product::with(['department', 'subDepartment', 'supplier']);

        // 🔍 Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('product_id', 'LIKE', "%{$search}%")
                ->orWhere('appear_name', 'LIKE', "%{$search}%");
            }); 
        }

        // 🏷 Department filter
        if ($request->has('department_id') && $request->department_id != '') {
            $query->where('department_id', $request->department_id);
        }

        $products = $query->latest()->paginate(10);

        // Send department list for dropdown
        $departments = Department::orderBy('name')->get();

        return view('products.index', compact('products', 'departments'));
    }


    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $companies = Company::orderBy('company_name')->get();
        return view('products.create', compact('departments', 'suppliers', 'companies'));
    }

public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'appear_name' => 'required|string|max:255',
        'department_id' => 'required|exists:departments,id',
        'sub_department_id' => 'required|exists:sub_departments,id',
        'supplier_id' => 'nullable|exists:suppliers,id',

        // IMPORTANT: validate type & units together
        'product_type' => 'required|in:pack,case',
        'units_per_case' => 'required_if:product_type,case|nullable|integer|min:1',

        'unit_of_measure' => 'required|string|max:255',
        'reorder_qty' => 'required|integer|min:0',

        // Make prices optional; still supported if sent
        'company_prices' => 'nullable|array',
        'company_prices.*.cost_price' => 'nullable|numeric|min:0',
        'company_prices.*.selling_price' => 'nullable|numeric|min:0',
    ]);

    // Normalize units_per_case so it can never be null in DB
    $unitsPerCase = $request->product_type === 'pack'
        ? 1
        : (int) $request->input('units_per_case', 1);

    if ($unitsPerCase < 1) {
        $unitsPerCase = 1;
    }

    DB::transaction(function () use ($request, $unitsPerCase) {
        $product = Product::create([
            'name' => $request->name,
            'appear_name' => $request->appear_name,
            'department_id' => $request->department_id,
            'sub_department_id' => $request->sub_department_id,
            'supplier_id' => $request->supplier_id,
            'units_per_case' => $unitsPerCase,
            'unit_of_measure' => $request->unit_of_measure,
            'reorder_qty' => $request->reorder_qty,
            'is_active' => $request->boolean('is_active'),
            'is_vat' => $request->boolean('is_vat'),
            'is_clear' => $request->boolean('is_clear'),
            'separate_department_invoice' => $request->boolean('separate_department_invoice'),
            'discount' => (float) $request->input('discount', 0.00),
        ]);

        // Save company-wise prices if provided
        foreach ((array) $request->company_prices as $companyId => $prices) {
            $cost = $prices['cost_price'] ?? null;
            $sell = $prices['selling_price'] ?? null;

            if ($cost !== null || $sell !== null) {
                $product->companyPrices()->create([
                    'company_id' => $companyId,
                    'cost_price' => $cost ?? 0,
                    'selling_price' => $sell ?? 0,
                ]);
            }
        }
    });

    return redirect()
        ->route('products.index')
        ->with('success', 'Product created successfully.');
}



    public function show(Product $product): View
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $departments = Department::orderBy('name')->get();
        $subDepartments = SubDepartment::where('department_id', $product->department_id)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();
         $companies = Company::orderBy('company_name')->get(); 
        return view('products.edit', compact('product', 'departments', 'subDepartments', 'suppliers', 'companies'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'appear_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'required|exists:sub_departments,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'units_per_case' => 'integer|min:1',
            'unit_of_measure' => 'required|string|max:255',
            'reorder_qty' => 'required|integer|min:0',
            'company_prices' => 'required|array',
            'company_prices.*.cost_price' => 'nullable|numeric|min:0',
            'company_prices.*.selling_price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $product) {
            // Update product details
            $product->update([
                'name' => $request->name,
                'appear_name' => $request->appear_name,
                'department_id' => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'supplier_id' => $request->supplier_id,
                'units_per_case' => $request->units_per_case,
                'unit_of_measure' => $request->unit_of_measure,
                'reorder_qty' => $request->reorder_qty,
                'is_active' => $request->has('is_active'),
                'is_vat' => $request->has('is_vat'),
                'is_clear' => $request->has('is_clear'),
                'separate_department_invoice' => $request->has('separate_department_invoice'),
                'discount' => $request->filled('discount') ? $request->discount : 0.00,
            ]);

            // Update or create company-wise prices
            foreach ($request->company_prices as $companyId => $prices) {
                $product->companyPrices()->updateOrCreate(
                    ['company_id' => $companyId],
                    [
                        'cost_price' => $prices['cost_price'] ?? 0,
                        'selling_price' => $prices['selling_price'] ?? 0,
                    ]
                );
            }
        });

        return redirect()->route('products.index')
                        ->with('success', 'Product updated successfully.');
    }


    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products.index')
                         ->with('success','Product deleted successfully.');
    }
    
    public function getSubDepartments(Request $request)
    {
        $subDepartments = SubDepartment::where('department_id', $request->department_id)->orderBy('name')->get();
        return response()->json($subDepartments);
    }
}