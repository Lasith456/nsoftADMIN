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

    public function index(): View
    {
        $receiveNotes = ReceiveNote::with(['deliveryNotes', 'invoices'])->latest()->paginate(10);
        return view('receive_notes.index', compact('receiveNotes'));
    }


    public function create(Request $request): View
    {
        $deliveryNotes = collect(); // empty by default

        if ($request->filled('customer_id')) {
            $query = DeliveryNote::where('status', 'delivered')
                ->whereDoesntHave('receiveNotes')
                ->with(['purchaseOrders.customer']);

            // Date filters
            if ($request->filled('from_date')) {
                $query->whereDate('delivery_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('delivery_date', '<=', $request->to_date);
            }

            // Customer filter
            $query->whereHas('purchaseOrders', function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });

            $deliveryNotes = $query->latest()->get();
        }

        // Always load customers who have eligible delivery notes
        $customers = Customer::whereHas('purchaseOrders.deliveryNotes', function ($q) {
            $q->where('status', 'delivered')
            ->whereDoesntHave('receiveNotes');
        })->orderBy('customer_name')->get();

        return view('receive_notes.create', compact('deliveryNotes', 'customers'));
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


    public function getItemsForDeliveryNote(Request $request)
    {
        $dn_ids = $request->input('dn_ids', []);
        if (empty($dn_ids)) {
            return response()->json(['items' => []]);
        }

        $deliveryItems = \App\Models\DeliveryNoteItem::whereIn('delivery_note_id', $dn_ids)
            ->with('product')
            ->select('product_id', DB::raw('SUM(quantity_requested) as total_requested'))
            ->groupBy('product_id')
            ->get();
        
        $responseItems = $deliveryItems->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity_expected' => $item->total_requested,
                'quantity_received' => $item->total_requested,
            ];
        });

        return response()->json(['items' => $responseItems]);
    }
    public function destroy(ReceiveNote $receiveNote): RedirectResponse
    {
        try {
            // Block if invoices exist
            if ($receiveNote->invoices()->exists()) {
                return redirect()->route('receive-notes.index')
                    ->withErrors(['error' => 'Cannot delete: This Receive Note is already linked to an Invoice.']);
            }

            DB::beginTransaction();

            // Delete related items first
            $receiveNote->items()->delete();

            // Detach delivery notes
            $receiveNote->deliveryNotes()->detach();

            // Finally delete receive note
            $receiveNote->delete();

            DB::commit();
            return redirect()->route('receive-notes.index')
                            ->with('success', 'Receive Note deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete Receive Note: ' . $e->getMessage()]);
        }
    }

}

