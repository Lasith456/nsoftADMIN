<?php

namespace App\Http\Controllers;

use App\Models\DebitNote;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DebitNoteController extends Controller
{
    /** List all debit notes */
    public function index(): View
    {
        $debitNotes = DebitNote::with('customer')->latest()->paginate(10);
        return view('debit_notes.index', compact('debitNotes'));
    }

    /** Show create form */
    public function create(): View
    {
        $customers = Customer::orderBy('customer_name')->get();
        return view('debit_notes.create', compact('customers'));
    }

    /** Store a new debit note */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
            'issued_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            // Generate Debit Note ID
            $last = DebitNote::orderBy('id', 'desc')->first();
            $next = $last ? intval(substr($last->debit_note_id, 3)) + 1 : 1;
            $formatted = str_pad($next, 4, '0', STR_PAD_LEFT);
            $debitNoteId = "DN-" . $formatted;

            $validated['debit_note_id'] = $debitNoteId;
            $validated['status'] = 'unused';

            DebitNote::create($validated);

            DB::commit();
            return redirect()->route('debit-notes.index')->with('success', 'Debit Note created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create debit note: ' . $e->getMessage()]);
        }
    }

    /** Edit debit note */
    public function edit(DebitNote $debitNote): View
    {
        $customers = Customer::orderBy('customer_name')->get();
        return view('debit_notes.edit', compact('debitNote', 'customers'));
    }

    /** Update debit note */
    public function update(Request $request, DebitNote $debitNote): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
            'issued_date' => 'required|date',
            'status' => 'required|in:unused,partially-used,used',
        ]);

        $debitNote->update($validated);

        return redirect()->route('debit-notes.index')->with('success', 'Debit Note updated successfully.');
    }

    /** Delete debit note */
    public function destroy(DebitNote $debitNote): RedirectResponse
    {
        $debitNote->delete();
        return redirect()->route('debit-notes.index')->with('success', 'Debit Note deleted successfully.');
    }

    /** Return total available debit balance for a customer (for your Alpine fetch) */
    public function balance(Customer $customer)
    {
        $balance = $customer->debitNotes()
            ->where('status', '!=', 'used')
            ->sum(DB::raw('amount - used_amount'));
        return response()->json(['balance' => round($balance, 2)]);
    }
}
