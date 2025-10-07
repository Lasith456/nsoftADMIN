<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Vehicle;
use App\Models\Agent;
use App\Models\Department;
use App\Models\DeliveryNoteItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class DeliveryNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:delivery-note-list|delivery-note-create|delivery-note-show|delivery-note-manage', ['only' => ['index','show', 'manage']]);
        $this->middleware('permission:delivery-note-create', ['only' => ['create','store', 'checkStock']]);
        $this->middleware('permission:delivery-note-manage', ['only' => ['manage', 'updateStatus']]);
        $this->middleware('permission:delivery-note-delete', ['only' => ['destroy']]);
    }
    
    public function index(): View
    {
        $deliveryNotes = DeliveryNote::with('vehicle')->latest()->paginate(10);
        return view('delivery_notes.index', compact('deliveryNotes'));
    }

    public function create(Request $request): View
    {
        // Only customers who have pending POs without DNs
        $customers = Customer::whereHas('purchaseOrders', function ($q) {
            $q->where('status', 'pending')
            ->whereDoesntHave('deliveryNotes');
        })->orderBy('customer_name')->get();

        $query = PurchaseOrder::where('status', 'pending')
            ->whereDoesntHave('deliveryNotes')
            ->with('customer')
            ->whereHas('customer'); 

        if ($request->filled('from_date')) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        // ✅ Only show POs after a customer is selected
        if ($request->has('customer_id') && $request->customer_id !== '') {
            $query->where('customer_id', $request->customer_id);
        } else {
            $query->whereRaw('0=1'); // return empty if no customer chosen
        }

        $purchaseOrders = $query->latest()->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('vehicle_no')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('delivery_notes.create', compact('purchaseOrders', 'vehicles', 'products', 'departments', 'customers'));
    }



public function store(Request $request): RedirectResponse
{
    $request->validate([
        'purchase_order_ids' => 'required|array|min:1',
        'purchase_order_ids.*' => 'exists:purchase_orders,id',
        'vehicle_id' => 'required|exists:vehicles,id',
        'delivery_date' => 'required|date',
        'driver_name' => 'nullable|string|max:255',
        'driver_mobile' => 'nullable|string|max:255',
        'agent_selections' => 'nullable|array',
        'agent_selections.*' => 'nullable|exists:agents,id',
    ]);

    DB::beginTransaction();
    try {
        $po_ids = $request->purchase_order_ids;
        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        // ✅ use override if given, else fallback to vehicle defaults
        $driverName = $request->driver_name ?: $vehicle->driver_name;
        $driverMobile = $request->driver_mobile ?: $vehicle->driver_mobile;

        $deliveryNote = DeliveryNote::create([
            'vehicle_id'    => $vehicle->id,
            'delivery_date' => $request->delivery_date,
            'status'        => 'processing',
            'driver_name'   => $driverName,
            'driver_mobile' => $driverMobile,
        ]);

        $deliveryNote->purchaseOrders()->attach($po_ids);

        // handle stock + items same as before...
        $purchaseOrders = PurchaseOrder::with('items.product')->whereIn('id', $po_ids)->get();
        $requestedItems = [];
        foreach ($purchaseOrders as $po) {
            foreach ($po->items as $item) {
                $productId = $item->product_id;
                if (!isset($requestedItems[$productId])) {
                    $requestedItems[$productId] = ['product_name' => $item->product_name, 'total_quantity' => 0];
                }
                $requestedItems[$productId]['total_quantity'] += $item->quantity;
            }
        }

        foreach ($requestedItems as $productId => $itemData) {
            $product = Product::lockForUpdate()->find($productId);
            $quantityNeeded = $itemData['total_quantity'];
            $fromClearStock = min($product->clear_stock_quantity, $quantityNeeded);
            $shortage = $quantityNeeded - $fromClearStock;
            $agentId = null;
            $fromAgent = 0;

            if ($shortage > 0) {
                if (isset($request->agent_selections[$productId]) && $request->agent_selections[$productId] != '') {
                    $agentId = $request->agent_selections[$productId];
                    $fromAgent = $shortage;
                } else {
                    throw new \Exception("A stock shortage for {$product->name} requires an agent to be assigned.");
                }
            }

            $deliveryNote->items()->create([
                'product_id' => $productId,
                'product_name' => $itemData['product_name'],
                'quantity_requested' => $quantityNeeded,
                'quantity_from_stock' => $fromClearStock,
                'agent_id' => $agentId,
                'quantity_from_agent' => $fromAgent,
            ]);

            if ($fromClearStock > 0) {
                $product->decrement('clear_stock_quantity', $fromClearStock);
            }
        }

        PurchaseOrder::whereIn('id', $po_ids)->update(['status' => 'processing']);

        DB::commit();
        return redirect()->route('delivery-notes.show', $deliveryNote->id)
                         ->with('success', 'Delivery Note created successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors([
            'error' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}

    
public function checkStock(Request $request)
{
    $po_ids = $request->input('po_ids', []);
    if (empty($po_ids)) {
        return response()->json(['items' => []]);
    }

    $items = PurchaseOrderItem::whereIn('purchase_order_id', $po_ids)
        ->with('product')
        ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
        ->groupBy('product_id')
        ->get();
    
    $response = [];
    foreach ($items as $item) {
        $product = $item->product;
        $clearStockShortage = max(0, $item->total_quantity - $product->clear_stock_quantity);
        $agents = collect();

        if ($clearStockShortage > 0 && $product->non_clear_stock_quantity < $clearStockShortage) {
            $agents = Agent::whereHas('products', function ($query) use ($product) {
                    $query->where('products.id', $product->id);
                })
                ->where('is_active', true)
                ->with(['products' => function ($query) use ($product) {
                    $query->where('products.id', $product->id);
                }])
                ->get()
                ->map(function($agent) {
                    if ($agent->products->isNotEmpty()) {
                        $agent->price_per_case = $agent->products->first()->pivot->price_per_case;
                    } else {
                        $agent->price_per_case = null;
                    }
                    return $agent;
                })
                ->sortBy('price_per_case');
        }

        $response[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'requested' => $item->total_quantity,
            'clear_stock' => $product->clear_stock_quantity,
            'non_clear_stock' => $product->non_clear_stock_quantity,
            'clear_stock_shortage' => $clearStockShortage,
            'agents' => $agents->values()->all(),
        ];
    }

    return response()->json(['items' => $response]);
}

    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['vehicle', 'purchaseOrders.customer', 'items.product', 'items.agent']);
        return view('delivery_notes.show', compact('deliveryNote'));
    }
    
    public function manage(): View
    {
        $deliveryNotes = DeliveryNote::where('status', 'processing')->with('vehicle')->get();
        return view('delivery_notes.manage', compact('deliveryNotes'));
    }

    public function updateStatus(Request $request, DeliveryNote $deliveryNote): RedirectResponse
    {
        $request->validate(['status' => 'required|string|in:delivered,cancelled']);

        if ($deliveryNote->status !== 'processing') {
            return redirect()->back()->withErrors(['error' => 'Only processing notes can be updated.']);
        }

        $deliveryNote->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Delivery Note status updated successfully.');
    }

    public function destroy(DeliveryNote $deliveryNote): RedirectResponse
    {
        // ✅ Prevent deletion if connected to receive notes
        if ($deliveryNote->receiveNotes()->exists()) {
            return redirect()->route('delivery-notes.index')
                ->withErrors(['error' => 'This Delivery Note is linked to Receive Notes. Please delete the Receive Notes first.']);
        }

        DB::beginTransaction();
        try {
            // Restore stock for each item
            foreach ($deliveryNote->items as $item) {
                if ($item->quantity_from_stock > 0) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('clear_stock_quantity', $item->quantity_from_stock);
                    }
                }
            }

            // Revert associated POs back to pending
            foreach ($deliveryNote->purchaseOrders as $po) {
                $po->update(['status' => 'pending']);
            }

            $deliveryNote->delete();

            DB::commit();
            return redirect()->route('delivery-notes.index')
                ->with('success', 'Delivery Note deleted and stock has been reverted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete Delivery Note: ' . $e->getMessage()]);
        }
    }

}

