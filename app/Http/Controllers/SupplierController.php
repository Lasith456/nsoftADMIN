<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplier-list|supplier-create|supplier-edit|supplier-delete', ['only' => ['index','show']]);
        $this->middleware('permission:supplier-create', ['only' => ['create','store']]);
        $this->middleware('permission:supplier-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:supplier-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $query = Supplier::query();

        // Handle the search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('supplier_name', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_id', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_mobile', 'LIKE', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(10);
        
        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'nic' => 'nullable|string|max:255|unique:suppliers,nic',
            'primary_address' => 'required|string',
            'supplier_mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_period' => 'nullable|integer|min:0',
        ]);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active');
        $input['credit_limit'] = $request->filled('credit_limit') ? $request->credit_limit : 0.00;

        Supplier::create($input);

        return redirect()->route('suppliers.index')
                         ->with('success','Supplier created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'nic' => 'nullable|string|max:255|unique:suppliers,nic,' . $supplier->id,
            'primary_address' => 'required|string',
            'supplier_mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_period' => 'nullable|integer|min:0',
        ]);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active');
        $input['credit_limit'] = $request->filled('credit_limit') ? $request->credit_limit : 0.00;

        $supplier->update($input);

        return redirect()->route('suppliers.index')
                         ->with('success','Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')
                         ->with('success','Supplier deleted successfully.');
    }
}