<?php

namespace App\Http\Controllers;

use App\Models\ReceiveNote;
use App\Models\ReceiveNoteItem;
use App\Models\DeliveryNote;
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
        $receiveNotes = ReceiveNote::with('deliveryNotes')->latest()->paginate(10);
        return view('receive_notes.index', compact('receiveNotes'));
    }

 public function create(Request $request): View
    {
        $query = DeliveryNote::where('status', 'delivered')
                                ->whereDoesntHave('receiveNotes');
        
        // **THE FIX IS HERE**: Add date filtering to the query
        if ($request->filled('from_date')) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        $deliveryNotes = $query->latest()->get();
        return view('receive_notes.create', compact('deliveryNotes'));
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
        $receiveNote->load(['deliveryNotes', 'items.product']);
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
}

