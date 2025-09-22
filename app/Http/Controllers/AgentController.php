<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Product;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:agent-list|agent-create|agent-edit|agent-delete', ['only' => ['index','show']]);
        $this->middleware('permission:agent-create', ['only' => ['create','store']]);
        $this->middleware('permission:agent-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:agent-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $query = Agent::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('agent_id', 'LIKE', "%{$search}%")
                  ->orWhere('contact_no', 'LIKE', "%{$search}%");
        }

        $agents = $query->latest()->paginate(10);
        return view('agents.index', compact('agents'));
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('agents.create', compact('products', 'departments'));
    }

  public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_no' => 'required|string|max:20',
            'address' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.price_per_case' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $input = $request->except('products');
            $input['agent_id'] = 'AGENT-' . strtoupper(Str::random(6));
            $input['is_active'] = $request->has('is_active');
            // **THE FIX IS HERE**: Set default values if fields are empty
            $input['credit_limit'] = $request->filled('credit_limit') ? $request->credit_limit : 0.00;
            $input['credit_period'] = $request->filled('credit_period') ? $request->credit_period : 0;
            
            $agent = Agent::create($input);

            $productsToSync = [];
            foreach ($request->products as $productData) {
                $productsToSync[$productData['product_id']] = ['price_per_case' => $productData['price_per_case']];
            }
            $agent->products()->sync($productsToSync);

            DB::commit();
            return redirect()->route('agents.index')->with('success', 'Agent created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Agent $agent): View
    {
        // Eager load the new products relationship
        $agent->load('products');
        return view('agents.show', compact('agent'));
    }

    public function edit(Agent $agent): View
    {
        $agent->load('products');
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('agents.edit', compact('agent', 'products'));
    }

  public function update(Request $request, Agent $agent): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_no' => 'required|string|max:20',
            'address' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.price_per_case' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $input = $request->except('products');
            $input['is_active'] = $request->has('is_active');
            // **THE FIX IS HERE**: Set default values if fields are empty
            $input['credit_limit'] = $request->filled('credit_limit') ? $request->credit_limit : 0.00;
            $input['credit_period'] = $request->filled('credit_period') ? $request->credit_period : 0;

            $agent->update($input);

            $productsToSync = [];
            foreach ($request->products as $productData) {
                $productsToSync[$productData['product_id']] = ['price_per_case' => $productData['price_per_case']];
            }
            $agent->products()->sync($productsToSync);

            DB::commit();
            return redirect()->route('agents.index')->with('success', 'Agent updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function destroy(Agent $agent): RedirectResponse
    {
        $agent->delete();
        return redirect()->route('agents.index')
                         ->with('success','Agent deleted successfully.');
    }
}

