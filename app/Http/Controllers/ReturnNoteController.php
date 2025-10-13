<?php

namespace App\Http\Controllers;

use App\Models\ReturnNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnNoteController extends Controller
{
    public function __construct()
    {
        // ✅ Require login for all routes
        $this->middleware('auth');

        // ✅ Apply permission-based route protection
        $this->middleware('permission:view return notes')->only(['index', 'show']);
        $this->middleware('permission:create return notes')->only(['storeAjax']);
        $this->middleware('permission:update return notes')->only(['changeStatus']);
        $this->middleware('permission:convert return note to po')->only(['createPO']);
    }

    /**
     * AJAX create (for popup in Receive Note page)
     */
    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'customer_id' => 'nullable|exists:customers,id',
            'return_date' => 'nullable|date',
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
        ]);

        $returnNote = ReturnNote::create([
            'company_id'   => $validated['company_id'] ?? null,
            'customer_id'  => $validated['customer_id'] ?? null,
            'reason'       => $validated['reason'],
            'return_date'  => $validated['return_date'] ?? now(),
            'created_by'   => auth()->id(),
            'agent_id'     => $request->agent_id,
            'product_id'   => $validated['product_id'],
            'quantity'     => $validated['quantity'],
            'session_token'=> $request->session_token,
        ]);

        return response()->json([
            'success'        => true,
            'return_note_id' => $returnNote->return_note_id,
            'id'             => $returnNote->id,
        ]);
    }

    /**
     * List all Return Notes with filters.
     */
    public function index(Request $request)
    {
        $query = ReturnNote::with([
            'receiveNote.deliveryNotes',
            'receiveNote.customer',
            'receiveNote.agent',
            'company'
        ]);

        // 🔍 Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // 🔍 Date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('return_date', [$request->from_date, $request->to_date]);
        }

        // 🔍 Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_note_id', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('receiveNote.customer', function ($c) use ($search) {
                      $c->where('customer_name', 'like', "%{$search}%");
                  });
            });
        }

        $returnNotes = $query->latest()->paginate(10);
        $companies = \App\Models\Company::orderBy('company_name')->get();

        return view('return-notes.index', compact('returnNotes', 'companies'));
    }

    /**
     * Change Return Note status (e.g., Pending → Processed → Ignored)
     */
    public function changeStatus(Request $request, ReturnNote $returnNote)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Processed,Ignored',
        ]);

        DB::beginTransaction();
        try {
            $returnNote->update(['status' => $validated['status']]);

            // ✅ If linked to a Receive Note → mark it completed
            if ($returnNote->receive_note_id) {
                \App\Models\ReceiveNote::where('id', $returnNote->receive_note_id)
                    ->update(['status' => 'completed']);
            }

            DB::commit();
            return redirect()->route('return-notes.index')
                ->with('success', "Return Note status changed to {$validated['status']}. Linked Receive Note marked as completed.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to change status: ' . $e->getMessage()]);
        }
    }

    /**
     * Auto-create a Purchase Order from a Return Note.
     */
    public function createPO(ReturnNote $returnNote)
    {
        DB::beginTransaction();
        try {
            if (empty($returnNote->product_id) || empty($returnNote->quantity)) {
                return back()->withErrors(['error' => 'No product or quantity found in this Return Note.']);
            }

            $last = \App\Models\PurchaseOrder::orderByRaw("CAST(SUBSTRING(po_id, 4) AS UNSIGNED) DESC")->first();
            $next = $last ? intval(substr($last->po_id, 3)) + 1 : 1;
            $poCode = 'PO-' . str_pad($next, 4, '0', STR_PAD_LEFT);

            $po = \App\Models\PurchaseOrder::create([
                'po_id'         => $poCode,
                'customer_id'   => $returnNote->customer_id,
                'status'        => 'pending',
                'delivery_date' => now()->addDays(7),
                'notes'         => "Auto-created from Return Note {$returnNote->return_note_id}",
            ]);

            $product = \App\Models\Product::find($returnNote->product_id);
            if (!$product) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Product not found for this Return Note.']);
            }

            \App\Models\PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id'        => $product->id,
                'product_name'      => $product->name,
                'quantity'          => $returnNote->quantity,
                'unit_price'        => (float) $product->selling_price,
            ]);

            // ✅ Update statuses
            $returnNote->update(['status' => 'Processed']);
            if ($returnNote->receive_note_id) {
                \App\Models\ReceiveNote::where('id', $returnNote->receive_note_id)
                    ->update(['status' => 'completed']);
            }

            DB::commit();
            return redirect()->route('return-notes.index')
                ->with('success', "Purchase Order {$po->po_id} created successfully from Return Note {$returnNote->return_note_id}. Linked Receive Note marked as completed.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create PO: ' . $e->getMessage()]);
        }
    }

    /**
     * Show a single Return Note details.
     */
    public function show(ReturnNote $returnNote)
    {
        $returnNote->load(['company', 'customer', 'agent', 'product']);
        return view('return-notes.show', compact('returnNote'));
    }
}
