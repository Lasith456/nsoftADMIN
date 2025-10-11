<?php

namespace App\Http\Controllers;

use App\Models\ReceiveNote;
use App\Models\ReceiveNoteItem;
use App\Models\DeliveryNote;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ReceiveNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:receive-note-list', ['only' => ['index']]);
        $this->middleware('permission:receive-note-create', ['only' => ['create','store', 'getItemsForDeliveryNote']]);
        $this->middleware('permission:receive-note-show', ['only' => ['show']]);
        $this->middleware('permission:receive-note-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:receive-note-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        // Base query with all relationships
        $query = ReceiveNote::with([
            'deliveryNotes.purchaseOrders.customer.company', // ✅ To get customer + company
            'invoices'
        ]);

        // ✅ Filter by company
        if ($request->filled('company_id')) {
            $query->whereHas('deliveryNotes.purchaseOrders.customer', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        // ✅ Search filter (by RN ID or DN ID)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receive_note_id', 'LIKE', "%{$search}%")
                ->orWhereHas('deliveryNotes', function ($q2) use ($search) {
                    $q2->where('delivery_note_id', 'LIKE', "%{$search}%");
                });
            });
        }

        // ✅ Fetch companies for dropdown
        $companies = \App\Models\Company::orderBy('company_name')->get();

        // ✅ Paginate
        $receiveNotes = $query->latest()->paginate(10);

        return view('receive_notes.index', compact('receiveNotes', 'companies'));
    }

public function create(Request $request): View
{
    $companies = \App\Models\Company::orderBy('company_name')->get();
    $deliveryNotes = collect();

    if ($request->filled('customer_id')) {
        $query = DeliveryNote::where('status', 'delivered')
            ->whereDoesntHave('receiveNotes')
            ->with(['purchaseOrders.customer']);

        if ($request->filled('from_date')) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        $query->whereHas('purchaseOrders', function ($q) use ($request) {
            $q->where('customer_id', $request->customer_id);
        });

        $deliveryNotes = $query->latest()->get();
    }

    $allCustomers = Customer::whereHas('purchaseOrders.deliveryNotes', function ($q) {
            $q->where('status', 'delivered')->whereDoesntHave('receiveNotes');
        })
        ->with('company:id,company_name')
        ->orderBy('customer_name')
        ->get();

    // Pass selected values to view
    return view('receive_notes.create', [
        'deliveryNotes' => $deliveryNotes,
        'allCustomers' => $allCustomers,
        'companies' => $companies,
        'selectedCustomerId' => $request->customer_id,
        'selectedCompanyId' => $request->company_id,
        'selectedCustomerName' => $request->customer_name,
    ]);
}



    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'delivery_note_ids' => 'required|array|min:1',
            'delivery_note_ids.*' => 'exists:delivery_notes,id',
            'received_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_expected' => 'required|integer',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.discrepancy_reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $deliveryNoteIds = $request->delivery_note_ids;
            $hasDiscrepancy = false;

            $receiveNote = ReceiveNote::create([
                'received_date' => $request->received_date,
                'notes' => $request->notes,
                'status' => 'completed',
            ]);

            $receiveNote->deliveryNotes()->attach($deliveryNoteIds);

            foreach ($request->items as $itemData) {
                if ($itemData['quantity_received'] != $itemData['quantity_expected']) {
                    $hasDiscrepancy = true;
                }
                ReceiveNoteItem::create([
                    'receive_note_id' => $receiveNote->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_expected' => $itemData['quantity_expected'],
                    'quantity_received' => $itemData['quantity_received'],
                    'discrepancy_reason' => $itemData['discrepancy_reason'] ?? null,
                ]);
            }

            if ($hasDiscrepancy) {
                $receiveNote->update(['status' => 'discrepancy']);
            }
            
            DeliveryNote::whereIn('id', $deliveryNoteIds)->update(['status' => 'received']);

            DB::commit();
            return redirect()->route('receive-notes.show', $receiveNote->id)
                             ->with('success', 'Receive Note created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(ReceiveNote $receiveNote): View
    {
        $receiveNote->load([
            'deliveryNotes.purchaseOrders.customer', // ✅ add this
            'items.product'
        ]);

        return view('receive_notes.show', compact('receiveNote'));
    }

    public function popup($id)
    {
        $receiveNote = ReceiveNote::with(['items.product', 'deliveryNotes.purchaseOrders.customer'])->findOrFail($id);

        // Return only the partial view (no layouts.app)
        return view('receive_notes.partials.details', compact('receiveNote'));
    }
    public function getItemsForDeliveryNote(Request $request)
    {
        $dn_ids = $request->input('dn_ids', []);
        if (empty($dn_ids)) {
            return response()->json(['items' => []]);
        }

        // ✅ Fetch all items from selected Delivery Notes, including agent info
        $deliveryItems = \App\Models\DeliveryNoteItem::whereIn('delivery_note_id', $dn_ids)
            ->with(['product:id,name'])
            ->select(
                'delivery_note_id',
                'product_id',
                'agent_id',
                DB::raw('SUM(quantity_requested) as total_requested')
            )
            ->groupBy('delivery_note_id', 'product_id', 'agent_id')
            ->get();

        // ✅ Build response structure
        $responseItems = $deliveryItems->map(function ($item) {
            return [
                'delivery_note_id'  => $item->delivery_note_id,
                'product_id'        => $item->product_id,
                'product_name'      => $item->product?->name ?? 'Unknown Product',
                'agent_id'          => $item->agent_id, // 🟢 Needed for agent/mixed stock detection
                'quantity_expected' => $item->total_requested,
                'quantity_received' => $item->total_requested,
            ];
        });

        // ✅ Merge duplicate products across multiple DNs (if same product appears twice)
        $merged = $responseItems->groupBy('product_id')->map(function ($group) {
            $totalExpected = $group->sum('quantity_expected');
            $agentIds = $group->pluck('agent_id')->unique()->filter();
            $isMixed = $agentIds->count() > 1 || $agentIds->isEmpty() === false && $agentIds->count() !== $group->count();

            return [
                'product_id'        => $group->first()['product_id'],
                'product_name'      => $group->first()['product_name'],
                'quantity_expected' => $totalExpected,
                'quantity_received' => $totalExpected,
                'agent_id'          => $agentIds->count() === 1 ? $agentIds->first() : null,
                'is_mixed_stock'    => $isMixed, // 🟢 flag if same product came from both agent + own stock
                'delivery_note_id'  => $group->pluck('delivery_note_id')->join(','),
            ];
        })->values();

        return response()->json(['items' => $merged]);
    }


    public function destroy(ReceiveNote $receiveNote): RedirectResponse
    {
        try {
            if ($receiveNote->invoices()->exists()) {
                return redirect()->route('receive-notes.index')
                    ->withErrors(['error' => 'Cannot delete: This Receive Note is already linked to an Invoice.']);
            }

            DB::beginTransaction();
            $deliveryNotes = $receiveNote->deliveryNotes;
            foreach ($deliveryNotes as $dn) {
                $dn->update(['status' => 'Delivered']);
            }
            $receiveNote->items()->delete();
            $receiveNote->deliveryNotes()->detach();
            $receiveNote->delete();

            DB::commit();

            return redirect()->route('receive-notes.index')
                ->with('success', 'Receive Note deleted successfully. Linked Delivery Notes have been marked as Delivered.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete Receive Note: ' . $e->getMessage()]);
        }
    }
}

