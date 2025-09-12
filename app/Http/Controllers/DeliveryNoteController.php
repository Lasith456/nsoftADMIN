<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Vehicle;
use App\Models\Agent;
use App\Models\DeliveryNoteItem;
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
        $query = PurchaseOrder::where('status', 'pending')
                                ->whereDoesntHave('deliveryNotes')
                                ->with('customer');
        
        // **THE FIX IS HERE**: Now filters by the PO's delivery_date
        if ($request->filled('from_date')) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        $purchaseOrders = $query->latest()->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('vehicle_no')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('delivery_notes.create', compact('purchaseOrders', 'vehicles', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'purchase_order_ids' => 'required|array|min:1',
            'purchase_order_ids.*' => 'exists:purchase_orders,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'delivery_date' => 'required|date',
            'agent_selections' => 'nullable|array',
            'agent_selections.*' => 'nullable|exists:agents,id',
        ]);

        DB::beginTransaction();
        try {
            $po_ids = $request->purchase_order_ids;
            $purchaseOrders = PurchaseOrder::with('items.product')->whereIn('id', $po_ids)->get();
            
            $requestedItems = [];
            foreach ($purchaseOrders as $po) {
                foreach ($po->items as $item) {
                    $productId = $item->product_id;
                    if (!isset($requestedItems[$productId])) {
                        $requestedItems[$productId] = ['product' => $item->product, 'name' => $item->product_name, 'total_quantity' => 0];
                    }
                    $requestedItems[$productId]['total_quantity'] += $item->quantity;
                }
            }
            
            $deliveryNote = DeliveryNote::create([
                'vehicle_id' => $request->vehicle_id,
                'delivery_date' => $request->delivery_date,
                'status' => 'processing', // Default status
            ]);

            $deliveryNote->purchaseOrders()->attach($po_ids);

            foreach ($requestedItems as $productId => $itemData) {
                $product = $itemData['product'];
                $quantityNeeded = $itemData['total_quantity'];
                
                $fromStock = min($product->total_stock, $quantityNeeded);
                $shortage = $quantityNeeded - $fromStock;
                $agentId = null;
                $fromAgent = 0;

                if ($shortage > 0) {
                    if (isset($request->agent_selections[$productId]) && $request->agent_selections[$productId] != '') {
                        $agentId = $request->agent_selections[$productId];
                        $fromAgent = $shortage;
                    } else {
                        throw new \Exception("Stock shortage for {$product->name}, but no agent was assigned.");
                    }
                }
                
                $fromClearStock = min($product->clear_stock_quantity, $fromStock);
                $fromNonClearStock = $fromStock - $fromClearStock;

                $deliveryNote->items()->create([
                    'product_id' => $productId,
                    'product_name' => $itemData['name'],
                    'quantity_requested' => $quantityNeeded,
                    'quantity_from_stock' => $fromStock,
                    'agent_id' => $agentId,
                    'quantity_from_agent' => $fromAgent,
                ]);

                if ($fromClearStock > 0) {
                    $product->decrement('clear_stock_quantity', $fromClearStock);
                }
                if($fromNonClearStock > 0){
                     $product->decrement('non_clear_stock_quantity', $fromNonClearStock);
                }
            }

            PurchaseOrder::whereIn('id', $po_ids)->update(['status' => 'processing']);

            DB::commit();
            return redirect()->route('delivery-notes.show', $deliveryNote->id)->with('success', 'Delivery Note created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
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
            $agents = [];
            
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
                    $agent->price_per_case = $agent->products->first()->pivot->price_per_case;
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

        return redirect()->route('delivery-notes.manage')->with('success', 'Delivery Note status updated successfully.');
    }
    public function destroy(DeliveryNote $deliveryNote)
    {
        DB::beginTransaction();
        try {
            foreach ($deliveryNote->items as $item) {
                if ($item->quantity_from_stock > 0) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        // For simplicity, this example returns stock to the 'clear' stock.
                        // A more complex system might track which stock type it came from.
                        $product->increment('clear_stock_quantity', $item->quantity_from_stock);
                    }
                }
            }

            foreach($deliveryNote->purchaseOrders as $po) {
                $po->update(['status' => 'pending']);
            }

            $deliveryNote->delete();

            DB::commit();
            return redirect()->route('delivery-notes.index')->with('success', 'Delivery Note deleted and stock has been reverted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete Delivery Note: ' . $e->getMessage()]);
        }
    }

}