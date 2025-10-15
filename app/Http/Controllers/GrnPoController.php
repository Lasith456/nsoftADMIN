<?php

namespace App\Http\Controllers;

use App\Models\GrnPo;
use App\Models\GrnPoItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrnPoController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // ✅ Apply permission-based access control (same pattern as CustomerController)
        $this->middleware('permission:grnpo-list|grnpo-create|grnpo-edit|grnpo-delete', ['only' => ['index', 'show', 'pendingBySupplier']]);
        $this->middleware('permission:grnpo-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:grnpo-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:grnpo-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the GRN POs.
     */
    public function index(Request $request): View
    {
        $query = GrnPo::with('supplier');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('grnpo_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('supplier_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('delivery_date')) {
            $query->whereDate('delivery_date', $request->delivery_date);
        }

        $grnpos = $query->latest()->paginate(10);

        return view('grnpos.index', compact('grnpos'));
    }

    /**
     * Show the form for creating a new GRN PO.
     */
    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('supplier_name')->get();
        $departments = Department::orderBy('name')->get();
        $products = Product::where('is_active', true)->get();

        return view('grnpos.create', compact('suppliers', 'departments', 'products'));
    }

    /**
     * Store a newly created GRN PO.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.department_id' => 'required|exists:departments,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Generate unique GRNPO ID
            $nextId = (GrnPo::max('id') ?? 0) + 1;
            $grnpoId = 'GRNPO-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $grnpo = GrnPo::create([
                'grnpo_id' => $grnpoId,
                'supplier_id' => $request->supplier_id,
                'delivery_date' => $request->delivery_date,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                GrnPoItem::create([
                    'grnpo_id' => $grnpo->id,
                    'department_id' => $item['department_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            Log::info('GRN PO created successfully', ['grnpo_id' => $grnpo->grnpo_id]);

            return redirect()->route('grnpos.index')
                             ->with('success', 'GRN PO created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GRN PO creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Failed to save GRN PO.']);
        }
    }

    /**
     * Display the specified GRN PO.
     */
    public function show(GrnPo $grnpo): View
    {
        $grnpo->load(['supplier', 'items.product', 'items.department']);
        return view('grnpos.show', compact('grnpo'));
    }

    /**
     * Show the form for editing the specified GRN PO.
     */
    public function edit(GrnPo $grnpo): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('supplier_name')->get();
        $departments = Department::orderBy('name')->get();
        $products = Product::where('is_active', true)->get();
        $grnpo->load('items');

        return view('grnpos.edit', compact('grnpo', 'suppliers', 'departments', 'products'));
    }

    /**
     * Update the specified GRN PO.
     */
    public function update(Request $request, GrnPo $grnpo): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $grnpo->update([
                'supplier_id' => $request->supplier_id,
                'delivery_date' => $request->delivery_date,
            ]);

            DB::commit();

            Log::info('GRN PO updated', ['grnpo_id' => $grnpo->grnpo_id]);

            return redirect()->route('grnpos.index')
                             ->with('success', 'GRN PO updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update GRN PO', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Failed to update GRN PO.']);
        }
    }

    /**
     * Return pending GRN POs by supplier (AJAX use).
     */
    public function pendingBySupplier($supplierId)
    {
        $pendingPos = GrnPo::where('supplier_id', $supplierId)
            ->where('status', 'pending')
            ->with('supplier:id,supplier_name')
            ->get(['id', 'grnpo_id', 'supplier_id', 'delivery_date']);

        $formatted = $pendingPos->map(function ($po) {
            return [
                'id' => $po->id,
                'grnpo_id' => $po->grnpo_id,
                'supplier_name' => $po->supplier->supplier_name ?? 'Unknown',
                'delivery_date' => $po->delivery_date ? $po->delivery_date->format('Y-m-d') : null,
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Remove the specified GRN PO from storage.
     */
    public function destroy(GrnPo $grnpo): RedirectResponse
    {
        try {
            if (in_array($grnpo->status, ['completed', 'confirmed'])) {
                return back()->withErrors(['error' => 'Cannot delete a completed or confirmed GRN PO.']);
            }

            DB::beginTransaction();

            $grnpo->items()->delete();
            $grnpo->delete();

            DB::commit();

            Log::warning('GRN PO deleted', ['grnpo_id' => $grnpo->grnpo_id]);

            return redirect()->route('grnpos.index')
                             ->with('success', 'GRN PO deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GRN PO delete failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to delete GRN PO.']);
        }
    }
}
