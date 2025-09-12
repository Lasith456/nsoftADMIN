<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Bank;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payment-create', ['only' => ['create', 'store']]);
    }

    /**
     * Show the form for creating a new payment for a specific invoice.
     */
 public function create(Invoice $invoice): View
    {
        $banks = Bank::where('is_active', true)->orderBy('name')->get();
        return view('payments.create', [
            'invoice' => $invoice,
            'banks' => $banks,
        ]);
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:' . ($invoice->total_amount - $invoice->amount_paid),
            'payment_method' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create the payment record
            $invoice->payments()->create($request->all());

            // Update the invoice's paid amount and status
            $totalPaid = $invoice->payments()->sum('amount');
            $invoice->amount_paid = $totalPaid;
            
            if ($totalPaid >= $invoice->total_amount) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partially-paid';
            }
            $invoice->save();

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function customerHistory(Request $request): View
    {
        $query = Invoice::where('invoiceable_type', 'App\Models\Customer')->with('invoiceable');
        $invoices = $query->latest()->paginate(15);
        return view('payments.customer_history', compact('invoices'));
    }

    public function agentHistory(Request $request): View
    {
        $query = Invoice::where('invoiceable_type', 'App\Models\Agent')->with('invoiceable');
        $invoices = $query->latest()->paginate(15);
        return view('payments.agent_history', compact('invoices'));
    }

    public function supplierHistory(Request $request): View
    {
        $query = Invoice::where('invoiceable_type', 'App\Models\Supplier')->with('invoiceable');
        $invoices = $query->latest()->paginate(15);
        return view('payments.supplier_history', compact('invoices'));
    }
}
