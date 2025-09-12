<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Department;
use App\Models\SubDepartment;
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

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('product_id', 'LIKE', "%{$search}%")
                  ->orWhere('appear_name', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(10);
        
        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();
        return view('products.create', compact('departments', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'appear_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'required|exists:sub_departments,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'units_per_case' => 'integer|min:1',
            'unit_of_measure' => 'required|string|max:255',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_qty' => 'required|integer|min:0',
        ]);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active');
        $input['is_vat'] = $request->has('is_vat');
        $input['discount'] = $request->filled('discount') ? $request->discount : 0.00;

        Product::create($input);

        return redirect()->route('products.index')
                         ->with('success','Product created successfully.');
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
        return view('products.edit', compact('product', 'departments', 'subDepartments', 'suppliers'));
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
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_qty' => 'required|integer|min:0',
        ]);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active');
        $input['is_vat'] = $request->has('is_vat');
        $input['discount'] = $request->filled('discount') ? $request->discount : 0.00;

        $product->update($input);

        return redirect()->route('products.index')
                         ->with('success','Product updated successfully.');
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

