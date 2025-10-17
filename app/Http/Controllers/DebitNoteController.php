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
        $debitNotes = DebitNote::with('customer')->latest()->paginate(15);
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
            'reason' => 'nullable|string|max:255',
            'issued_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            DebitNote::create($validated);

            DB::commit();
            return redirect()->route('debit-notes.index')
                ->with('success', 'Debit Note created successfully.');
        } catch (\Throwable $e) {
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
            'used_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
            'issued_date' => 'required|date',
            'status' => 'required|in:unused,partially-used,used',
        ]);

        // Auto-update status based on usage
        if ($validated['used_amount'] >= $validated['amount']) {
            $validated['status'] = 'used';
        } elseif ($validated['used_amount'] > 0) {
            $validated['status'] = 'partially-used';
        }

        $debitNote->update($validated);

        return redirect()->route('debit-notes.index')
            ->with('success', 'Debit Note updated successfully.');
    }

    /** Delete debit note */
    public function destroy(DebitNote $debitNote): RedirectResponse
    {
        $debitNote->delete();
        return redirect()->route('debit-notes.index')
            ->with('success', 'Debit Note deleted successfully.');
    }

    /** ───────────────────────────────
     *  API Endpoint: Return total available debit balance for a customer
     *  (Used in Alpine.js fetchDebitBalance)
     *  ─────────────────────────────── */
    public function balance(Customer $customer)
    {
        $balance = $customer->debitNotes()
            ->active()
            ->sum(DB::raw('amount - used_amount'));

        return response()->json(['balance' => round($balance, 2)]);
    }

    /** ───────────────────────────────
     *  (Optional) Method to auto-create Debit Note on Overpayment
     *  ─────────────────────────────── */
    public static function createAutoDebit(int $customerId, float $excess, string $reason = 'Overpayment Adjustment'): void
    {
        if ($excess > 0) {
            DebitNote::create([
                'customer_id' => $customerId,
                'amount' => $excess,
                'reason' => $reason,
                'status' => 'unused',
                'issued_date' => now(),
            ]);
        }
    }
}
