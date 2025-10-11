<?php

namespace App\Http\Controllers;

use App\Models\ReturnNote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class ReturnNoteController extends Controller
{
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
            'company_id' => $validated['company_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'reason' => $validated['reason'],
            'return_date' => $validated['return_date'] ?? now(),
            'created_by' => auth()->id(),
            'agent_id'   => $request->agent_id,
            'product_id' => $validated['product_id'],
            'quantity'   => $validated['quantity'],
                'session_token' => $request->session_token,
        ]);

        return response()->json([
            'success' => true,
            'return_note_id' => $returnNote->return_note_id,
            'id' => $returnNote->id,
        ]);
    }

    /**
     * Optional index (for future)
     */
    public function index()
    {
        $returnNotes = ReturnNote::with(['company', 'customer', 'agent'])
            ->latest()
            ->paginate(10);

        return view('return-notes.index', compact('returnNotes'));
    }

    /**
     * Change Return Note status (e.g. to Ignored)
     */
    public function changeStatus(Request $request, ReturnNote $returnNote)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Processed,Ignored',
        ]);

        DB::beginTransaction();
        try {
            // Update Return Note status
            $returnNote->update([
                'status' => $validated['status'],
            ]);

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

            // ✅ Update Return Note and linked Receive Note
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

    public function show(ReturnNote $returnNote)
    {
        $returnNote->load(['company', 'customer', 'agent', 'product']);
        return view('return-notes.show', compact('returnNote'));
    }





}
