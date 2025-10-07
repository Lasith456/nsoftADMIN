<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    /**
     * Set up the permissions for the controller.
     */
    public function __construct()
    {
        $this->middleware('permission:purchase-order-list|purchase-order-create|purchase-order-edit|purchase-order-delete', ['only' => ['index','show']]);
        $this->middleware('permission:purchase-order-create', ['only' => ['create','store']]);
        $this->middleware('permission:purchase-order-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:purchase-order-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        // Base query with customer and company relationships
        $query = PurchaseOrder::with(['customer.company']);

        // ✅ Filter by company
        if ($request->filled('company_id')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        // ✅ Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_id', 'LIKE', "%{$search}%")
                ->orWhereHas('customer', function ($q2) use ($search) {
                    $q2->where('customer_name', 'LIKE', "%{$search}%");
                });
            });
        }

        // Fetch all companies for dropdown
        $companies = \App\Models\Company::orderBy('company_name')->get();

        $purchaseOrders = $query->latest()->paginate(10);

        return view('purchase_orders.index', compact('purchaseOrders', 'companies'));
    }

public function create(Request $request): View
{
    $companies   = \App\Models\Company::orderBy('company_name')->get();
    $customers   = Customer::where('is_active', true)->orderBy('customer_name')->get();
    $products    = Product::where('is_active', true)->orderBy('name')->get();
    $departments = \App\Models\Department::orderBy('name')->get();

    $prefillCustomerId = $request->query('customer_id');
    $shortages = $request->query('shortages', []);

    return view('purchase_orders.create', compact(
        'companies', 'customers', 'products', 'departments', 'prefillCustomerId', 'shortages'
    ));
}



    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Create the main Purchase Order record
            $po = PurchaseOrder::create([
                'customer_id' => $request->customer_id,
                'delivery_date' => $request->delivery_date,
                'status' => 'pending', // Default status

            ]);

            // Create the PO items
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                // Add a check to ensure the product was found
                if (!$product) {
                    // If not found, throw a specific exception that can be caught
                    throw new \Exception("Product with ID {$itemData['product_id']} could not be found. The order was not created.");
                }

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $itemData['quantity'],
                    'is_vat' => $product->is_vat,
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating purchase order: ' . $e->getMessage());
            // Now display the more specific error message from our new check
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['customer', 'items.product']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('items');
        $customers = Customer::where('is_active', true)->orderBy('customer_name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('purchase_orders.edit', compact('purchaseOrder', 'customers', 'products'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        // Validation now includes the status field
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'required|date',
            'status' => 'required|string|in:pending,processing,delivered,cancelled',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Update call now includes the status
            $purchaseOrder->update($request->only(['customer_id', 'delivery_date', 'status']));

            // The rest of the logic for items remains the same if they are not editable on this form.
            // If you were to make items editable, that logic would go here.

            DB::commit();
            return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'An error occurred while updating the PO.']);
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        // ✅ Check if this Purchase Order has delivery notes
        if ($purchaseOrder->deliveryNotes()->exists()) {
            return redirect()->route('purchase-orders.index')
                ->withErrors(['error' => 'This Purchase Order is already assigned to a Delivery Note. Please delete the Delivery Note first.']);
        }

        try {
            $purchaseOrder->delete(); // cascade deletes items
            return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('purchase-orders.index')->withErrors(['error' => 'Failed to delete Purchase Order: ' . $e->getMessage()]);
        }
    }

    public function autoCreateFromDiscrepancy(Request $request): RedirectResponse
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'shortages'   => 'required|array|min:1',
        'shortages.*.product_id' => 'required|exists:products,id',
        'shortages.*.qty'        => 'required|integer',
    ]);

    DB::beginTransaction();
    try {
        $po = PurchaseOrder::create([
            'customer_id'   => $request->customer_id,
            'delivery_date' => now()->addDays(7), // default 7 days later
            'status'        => 'pending',
        ]);

        foreach ($request->shortages as $item) {
            $product = Product::findOrFail($item['product_id']);
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id'        => $product->id,
                'product_name'      => $product->name,
                'quantity'          => abs($item['qty']), // shortage is negative → use positive qty
                'is_vat'            => $product->is_vat,
            ]);
        }

        DB::commit();
        return redirect()->route('purchase-orders.show', $po->id)
            ->with('success', 'Purchase Order created automatically from discrepancy.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}

}

