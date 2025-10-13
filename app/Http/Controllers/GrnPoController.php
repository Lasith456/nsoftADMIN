<?php

namespace App\Http\Controllers;

use App\Models\GrnPo;
use App\Models\GrnPoItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrnPoController extends Controller
{
    public function __construct()
    {
        // ✅ Protect all routes by authentication
        $this->middleware('auth');

        // ✅ Apply permission-based middleware (Spatie Laravel-Permission or Policy)
        // You can adjust these to match your defined permission names
        $this->middleware('permission:view grnpos')->only(['index', 'show']);
        $this->middleware('permission:create grnpos')->only(['create', 'store']);
        $this->middleware('permission:delete grnpos')->only(['destroy']);
    }

    // ======================
    // INDEX PAGE (List all)
    // ======================
    public function index(Request $request)
    {
        $query = GrnPo::with('supplier');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('grnpo_id', 'like', "%$search%")
                ->orWhereHas('supplier', fn($q) => $q->where('supplier_name', 'like', "%$search%"));
        }

        if ($request->filled('delivery_date')) {
            $query->whereDate('delivery_date', $request->delivery_date);
        }

        $grnpos = $query->latest()->paginate(10);

        return view('grnpos.index', compact('grnpos'));
    }

    // ======================
    // CREATE PAGE
    // ======================
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('supplier_name')->get();
        $departments = Department::orderBy('name')->get();
        $products = Product::where('is_active', true)->get();

        return view('grnpos.create', compact('suppliers', 'departments', 'products'));
    }

    // ======================
    // STORE (Save new record)
    // ======================
    public function store(Request $request)
    {
        $this->authorize('create', GrnPo::class); // ✅ policy check (optional but good practice)

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
            $grnpo = GrnPo::create([
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
            Log::info('GRN PO created', ['id' => $grnpo->id, 'supplier_id' => $grnpo->supplier_id]);

            return redirect()->route('grnpos.index')
                ->with('success', 'GRN PO created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create GRN PO', ['error' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['error' => 'Failed to save GRN PO: ' . $e->getMessage()]);
        }
    }

    // ======================
    // SHOW SINGLE GRN PO
    // ======================
    public function show($id)
    {
        $this->authorize('view', GrnPo::class);

        $grnpo = GrnPo::with(['supplier', 'items.product', 'items.department'])
            ->findOrFail($id);

        return view('grnpos.show', compact('grnpo'));
    }

    // ======================
    // AJAX: Pending POs per supplier
    // ======================
    public function pendingBySupplier($supplierId)
    {
        $this->authorize('view', GrnPo::class);

        $pendingPos = GrnPo::where('supplier_id', $supplierId)
            ->where('status', 'pending')
            ->with('supplier:id,supplier_name')
            ->get(['id', 'grnpo_id', 'supplier_id', 'delivery_date']);

        $formatted = $pendingPos->map(function ($po) {
            return [
                'id' => $po->id,
                'grnpo_id' => $po->grnpo_id,
                'supplier_name' => $po->supplier->supplier_name ?? 'Unknown',
                'delivery_date' => $po->delivery_date->format('Y-m-d'),
            ];
        });

        return response()->json($formatted);
    }

    // ======================
    // DESTROY (Delete)
    // ======================
    public function destroy(GrnPo $grnpo)
    {
        $this->authorize('delete', $grnpo);

        try {
            if ($grnpo->status === 'completed' || $grnpo->status === 'confirmed') {
                return back()->withErrors(['error' => 'Cannot delete a completed or confirmed GRN PO.']);
            }

            DB::beginTransaction();

            $grnpo->items()->delete();
            $grnpo->delete();

            DB::commit();

            Log::warning('GRN PO deleted', ['grnpo_id' => $grnpo->id]);

            return redirect()->route('grnpos.index')->with('success', 'GRN PO deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete GRN PO', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to delete GRN PO.']);
        }
    }
}
