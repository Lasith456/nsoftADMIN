<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class GrnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:grn-list|grn-create|grn-delete|grn-manage', ['only' => ['index','show', 'manage']]);
        $this->middleware('permission:grn-create', ['only' => ['create','store','selectType','createFromPo']]);
        $this->middleware('permission:grn-delete', ['only' => ['destroy']]);
        $this->middleware('permission:grn-manage', ['only' => ['manage', 'complete', 'cancel', 'generateInvoice']]);
    }

    public function index(Request $request): View
    {
        $query = Grn::with(['supplier', 'invoice']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('grn_id', 'LIKE', "%{$search}%")
                  ->orWhere('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($subq) use ($search) {
                      $subq->where('supplier_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->has('delivery_date') && $request->delivery_date != '') {
            $query->whereDate('delivery_date', $request->delivery_date);
        }

        $grns = $query->latest()->paginate(10);
        return view('grns.index', compact('grns'));
    }

        public function create(Request $request): View
    {
        // Fetch base data
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('supplier_name')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $departments = Department::orderBy('name')->get();

        // ✅ Capture query parameters (passed from Delivery Note Create page)
        $selectedSupplierId = $request->query('supplier_id');
        $selectedProductId = $request->query('product_id');
        $selectedDepartmentId = $request->query('department_id');
        $shortage = $request->query('shortage');
        $productName = $request->query('product_name');

        // ✅ Pass all variables to view
        return view('grns.create', compact(
            'suppliers',
            'products',
            'departments',
            'selectedSupplierId',
            'selectedProductId',
            'selectedDepartmentId',
            'shortage',
            'productName'
        ));
    }


    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'delivery_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_type' => 'required|string|in:Unit,Case',
            'items.*.stock_type' => 'required|string|in:clear,non-clear',
            // 👇 allow 0 (previously min:1)
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.is_free_issue' => 'nullable|boolean',
            'items.*.free_issue_qty' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $totalDiscount = 0;

            foreach ($request->items as $itemData) {
                // Hided this GRN cost/selling price update to product
                // $product = Product::find($itemData['product_id']);
                // $product->update([
                //     'cost_price' => $itemData['cost_price'],
                //     'selling_price' => $itemData['selling_price'],
                // ]);

                $isFree = !empty($itemData['is_free_issue']) && (int)$itemData['is_free_issue'] === 1;
                $qty = (int)($itemData['quantity'] ?? 0);
                $cost = (float)($itemData['cost_price'] ?? 0);
                $discount = (float)($itemData['discount'] ?? 0);

                // Exclude free issues entirely from totals.
                // Also avoid adding discount when qty is 0 to keep totals consistent.
                if (!$isFree && $qty > 0) {
                    $totalAmount += $cost * $qty;
                    $totalDiscount += $discount;
                }
            }

            $grn = Grn::create([
                'delivery_date' => $request->delivery_date,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'total_discount' => $totalDiscount,
                'net_amount' => $totalAmount - $totalDiscount,
            ]);

            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                GrnItem::create([
                    'grn_id' => $grn->id,
                    'product_id' => $itemData['product_id'],
                    'unit_type' => $itemData['unit_type'],
                    'stock_type' => $itemData['stock_type'],
                    'quantity_received' => $itemData['quantity'],
                    'free_issue_qty' => $itemData['free_issue_qty'] ?? 0,
                    'is_free_issue' => $itemData['is_free_issue'] ?? false,
                    'units_per_case' => $product->units_per_case,
                    'cost_price' => $itemData['cost_price'],
                    'selling_price' => $itemData['selling_price'],
                    'discount' => $itemData['discount'] ?? 0,
                ]);
            }
            if ($request->filled('grnpo_id')) {
                        $grnPo = \App\Models\GrnPo::find($request->grnpo_id);
                        if ($grnPo) {
                            $grnPo->update(['status' => 'completed']); // or 'confirmed'
                        }
                    }
            DB::commit();
            return redirect()->route('grns.index')->with('success', 'GRN created successfully with pending status.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Grn $grn): View
    {
        $grn->load(['supplier', 'items.product']);
        return view('grns.show', compact('grn'));
    }

    public function manage(Request $request): View
    {
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $grns = collect();

        if ($request->filled('grn_id') || $request->filled('supplier_id')) {
            $query = Grn::query()->with('supplier')->where('status', 'pending');
            if ($request->filled('grn_id')) {
                $query->where('grn_id', 'like', '%' . $request->grn_id . '%');
            }
            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }
            $grns = $query->get();
        }

        return view('grns.manage', compact('suppliers', 'grns'));
    }

    public function generateInvoice(Grn $grn): RedirectResponse
    {
        if ($grn->status !== 'confirmed' || $grn->invoice_id) {
            return back()->withErrors(['error' => 'An invoice cannot be generated for this GRN. It might not be confirmed or may already be invoiced.']);
        }

        DB::beginTransaction();
        try {
            $supplier = $grn->supplier;
            $totalAmount = $grn->net_amount;
            $invoiceItemsData = [];

            foreach ($grn->items as $item) {
                $invoiceItemsData[] = [
                    'description' => $item->product->name . " (from GRN: {$grn->grn_id})",
                    'quantity' => $item->quantity_received,
                    'unit_price' => $item->cost_price,
                    'total' => ($item->cost_price * $item->quantity_received) - $item->discount,
                ];
            }

            $invoice = $supplier->invoices()->create([
                'invoice_id' => 'INV-SUPP-' . strtoupper(Str::random(6)),
                'due_date' => now()->addDays(30),
                'sub_total' => $totalAmount,
                'vat_percentage' => 0,
                'vat_amount' => 0,
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
                'is_vat_invoice' => false,
            ]);

            $invoice->items()->createMany($invoiceItemsData);

            $grn->update([
                'status' => 'invoiced',
                'invoice_id' => $invoice->id
            ]);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Supplier invoice generated successfully from GRN ' . $grn->grn_id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'An error occurred while generating the invoice: ' . $e->getMessage()]);
        }
    }

    public function complete(Grn $grn): RedirectResponse
    {
        if ($grn->status !== 'pending') {
            return redirect()->back()->withErrors(['error' => 'This GRN has already been processed.']);
        }

        DB::beginTransaction();
        try {
            foreach ($grn->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $unitsPerCase = ($item->unit_type === 'Case') ? $item->units_per_case : 1;

                    // Add both normal qty and free issue qty (both may be 0, never negative)
                    $totalUnitsReceived = max(0, (int)$item->quantity_received) * $unitsPerCase;
                    $totalUnitsReceived += ($item->is_free_issue ? max(0, (int)$item->free_issue_qty) * $unitsPerCase : 0);

                    if ($item->stock_type === 'clear') {
                        $product->clear_stock_quantity = max(0, $product->clear_stock_quantity + $totalUnitsReceived);
                    } else {
                        $product->non_clear_stock_quantity = max(0, $product->non_clear_stock_quantity + $totalUnitsReceived);
                    }
                    $product->save();
                }
            }

                 $grn->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
            DB::commit();
            return redirect()->route('grns.index')->with('success', 'GRN has been confirmed and stock updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Grn $grn): RedirectResponse
    {
        if ($grn->status !== 'pending') {
            return redirect()->back()->withErrors(['error' => 'Only pending GRNs can be cancelled.']);
        }
        $grn->update(['status' => 'cancelled']);
        return redirect()->route('grns.index')->with('success', 'GRN has been successfully cancelled.');
    }

    public function destroy(Grn $grn): RedirectResponse
    {
        DB::beginTransaction();
        try {
            if ($grn->status === 'confirmed') {
                foreach ($grn->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $unitsPerCase = ($item->unit_type === 'Case') ? $item->units_per_case : 1;

                        $totalUnitsToDecrement = max(0, (int)$item->quantity_received) * $unitsPerCase;
                        $totalUnitsToDecrement += ($item->is_free_issue ? max(0, (int)$item->free_issue_qty) * $unitsPerCase : 0);

                        if ($item->stock_type === 'clear') {
                            $product->clear_stock_quantity = max(0, $product->clear_stock_quantity - $totalUnitsToDecrement);
                        } else {
                            $product->non_clear_stock_quantity = max(0, $product->non_clear_stock_quantity - $totalUnitsToDecrement);
                        }
                        $product->save();
                    }
                }
            }
            $grn->delete();
            DB::commit();
            return redirect()->route('grns.index')->with('success', 'GRN deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete GRN: ' . $e->getMessage()]);
        }
    }

public function selectType()
{
    $suppliers = \App\Models\Supplier::where('is_active', true)->orderBy('supplier_name')->get();
    return view('grns.select_type', compact('suppliers'));
}
public function createFromPo($grnpoId)
{
    $grnpo = \App\Models\GrnPo::with(['supplier', 'items.product', 'items.department'])->findOrFail($grnpoId);

    $supplier = $grnpo->supplier;
    $departments = \App\Models\Department::orderBy('name')->get();

    // Prefill items from GRN PO
    $poItems = $grnpo->items->map(function ($item) {
        return [
            'department_id' => $item->department_id,
            'department_name' => $item->department->name ?? '',
            'product_id' => $item->product_id,
            'product_name' => $item->product->name ?? '',
            'expected_qty' => $item->quantity,
            'quantity_received' => $item->quantity, // editable field
            'unit_type' => 'Unit',
            'stock_type' => 'clear',
            'cost_price' => $item->product->cost_price ?? 0,
            'selling_price' => $item->product->selling_price ?? 0,
            'discount' => 0,
            'is_free_issue' => 0,
            'free_issue_qty' => 0,
            'units_per_case' => $item->product->units_per_case ?? 1,
        ];
    });

    return view('grns.create_from_po', compact('grnpo', 'supplier', 'departments', 'poItems'));
}

}
