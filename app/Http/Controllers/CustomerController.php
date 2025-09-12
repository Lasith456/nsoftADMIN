<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('permission:customer-list|customer-create|customer-edit|customer-delete', ['only' => ['index','show']]);
        $this->middleware('permission:customer-create', ['only' => ['create','store']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:customer-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Customer::query();

        // Handle the search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_id', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_mobile', 'LIKE', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10);
        
        return view('customers.index', compact('customers'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'nic' => 'nullable|string|max:255|unique:customers,nic',
            'primary_address' => 'required|string',
            'customer_mobile' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active');
        
        // Set default value for credit_limit if empty or not present
        $input['credit_limit'] = $request->filled('credit_limit') ? $request->credit_limit : 0.00;


        Customer::create($input);

        return redirect()->route('customers.index')
                         ->with('success','Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): View
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'nic' => 'nullable|string|max:255|unique:customers,nic,' . $customer->id,
            'primary_address' => 'required|string',
            'customer_mobile' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active');

        // Set default value for credit_limit if empty or not present
        $input['credit_limit'] = $request->filled('credit_limit') ? $request->credit_limit : 0.00;

        $customer->update($input);

        return redirect()->route('customers.index')
                         ->with('success','Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();
        return redirect()->route('customers.index')
                         ->with('success','Customer deleted successfully.');
    }
    public function getUnpaidInvoices(Customer $customer)
    {
        $invoices = $customer->invoices()
            ->whereIn('status', ['unpaid', 'partially-paid'])
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json($invoices);
    }
}

