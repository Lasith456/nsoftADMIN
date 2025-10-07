<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Bank;
use App\Models\Customer; 
use App\Models\Agent;
use App\Models\Supplier;
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
    public function customerOutstanding(Request $request): View
    {
        $customers = Customer::with('invoices')
            ->whereHas('invoices', function ($query) {
                $query->whereIn('status', ['unpaid', 'partially-paid']);
            })
            ->get()
            ->map(function ($customer) {
                $customer->outstanding_balance = $customer->invoices->sum(function ($invoice) {
                    return $invoice->total_amount - $invoice->amount_paid;
                });
                return $customer;
            })
            ->filter(function ($customer) {
                return $customer->outstanding_balance > 0;
            });

        return view('payments.customerOutstanding', compact('customers'));
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
public function createBulk(Request $request, Customer $customer = null): View
{
    // Load all companies
    $companies = \App\Models\Company::orderBy('company_name')->get();

    // Load all customers that have unpaid or partially-paid invoices
    $allCustomers = Customer::whereHas('invoices', function ($query) {
            $query->whereIn('status', ['unpaid', 'partially-paid']);
        })
        ->with('company:id,company_name')
        ->orderBy('customer_name')
        ->get();

    // Active banks
    $banks = Bank::where('is_active', true)->orderBy('name')->get();

    return view('payments.create_bulk', [
        'companies' => $companies,
        'customers' => $allCustomers,
        'selectedCustomerId' => $customer?->id,
        'banks' => $banks,
    ]);
}

    /**
     * Store a new bulk payment and distribute it across selected invoices.
     */
public function storeBulk(Request $request): RedirectResponse
{
    $request->validate([
        'customer_id'      => 'required|exists:customers,id',
        'invoice_ids'      => 'required|array|min:1',
        'invoice_ids.*'    => 'exists:invoices,id',
        'amount'           => 'required|numeric|min:0.01',
        'stamp_fee'        => 'nullable|numeric|min:0',
        'surcharge_fee'    => 'nullable|numeric|min:0',
        'payment_date'     => 'required|date',
        'payment_method'   => 'required|string',
        'reference_number' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        $stampFee     = $request->stamp_fee ?? 0;
        $surchargeFee = $request->surcharge_fee ?? 0;

        // ✅ Only this much goes to invoices
        $amountToDistribute = $request->amount - ($stampFee + $surchargeFee);

        $batchId = uniqid('BATCH-');

        $invoices = Invoice::where('invoiceable_id', $request->customer_id)
            ->where('invoiceable_type', Customer::class)
            ->whereIn('id', $request->invoice_ids)
            ->whereIn('status', ['unpaid', 'partially-paid'])
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        $firstPayment = true; // ✅ fees will only be saved once

        foreach ($invoices as $invoice) {
            if ($amountToDistribute <= 0) break;

            $balanceDue    = $invoice->total_amount - $invoice->amount_paid;
            $paymentAmount = min($amountToDistribute, $balanceDue);

            if ($paymentAmount > 0) {
                $invoice->payments()->create([
                    'payment_date'         => $request->payment_date,
                    'amount'               => $paymentAmount,   // only invoice portion
                    'payment_method'       => $request->payment_method,
                    'reference_number'     => $request->reference_number,
                    'notes'                => $request->notes ?? null,
                    'batch_id'             => $batchId,
                    'bank_id'              => $request->bank_id,
                    'cheque_number'        => $request->cheque_number,
                    'cheque_date'          => $request->cheque_date,
                    'cheque_received_date' => $request->cheque_received_date,
                    'stamp_fee'            => $firstPayment ? $stampFee : 0,
                    'surcharge_fee'        => $firstPayment ? $surchargeFee : 0,
                ]);

                // Update invoice totals
                $invoice->amount_paid = $invoice->payments()->sum('amount');
                $invoice->status      = abs($invoice->amount_paid - $invoice->total_amount) < 0.01
                                        ? 'paid'
                                        : 'partially-paid';
                $invoice->save();

                $amountToDistribute -= $paymentAmount;
                $firstPayment = false; // ✅ only first payment carries fees
            }
        }

        DB::commit();

        return redirect()->route('payments.receipt', $batchId);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
    }
}



    // public function showReceipt(string $batchId): View
    // {
    //     $payments = Payment::with('invoice', 'bank')
    //         ->where('batch_id', $batchId)
    //         ->get();

    //     if ($payments->isEmpty()) {
    //         abort(404, 'Receipt not found.');
    //     }

    //     $customer = $payments->first()->invoice->invoiceable;

    //     $vatTotal = $payments->filter(fn($p) => $p->invoice->is_vat_invoice)->sum('amount');
    //     $nonVatTotal = $payments->filter(fn($p) => !$p->invoice->is_vat_invoice)->sum('amount');

    //     $stampFee = session('stampFee', 0);

    //     return view('payments.receipt', [
    //         'payments'   => $payments,
    //         'customer'   => $customer,
    //         'batchId'    => $batchId,
    //         'vatTotal'   => $vatTotal,
    //         'nonVatTotal'=> $nonVatTotal,
    //         'stampFee'   => $stampFee,
    //     ]);
    // }

public function showReceipt(string $batchId): View
{
    $payments = Payment::with(['invoice.invoiceable', 'bank'])
        ->where('batch_id', $batchId)
        ->get();

    if ($payments->isEmpty()) {
        abort(404, 'Receipt not found.');
    }

    $invoiceable = $payments->first()->invoice->invoiceable;

    $vatTotal    = $payments->filter(fn($p) => $p->invoice->is_vat_invoice)->sum('amount');
    $nonVatTotal = $payments->filter(fn($p) => !$p->invoice->is_vat_invoice)->sum('amount');

    // ✅ Now directly from DB
    $stampFee    = $payments->first()->stamp_fee ?? 0;
    $surchargeFee = $payments->first()->surcharge_fee ?? 0;

    // Supplier Receipt
    if ($invoiceable instanceof \App\Models\Supplier) {
        return view('payments.supplier_receipt', [
            'payments'      => $payments,
            'supplier'      => $invoiceable,
            'batchId'       => $batchId,
            'vatTotal'      => $vatTotal,
            'nonVatTotal'   => $nonVatTotal,
            'stampFee'      => $stampFee,
            'surchargeFee'  => $surchargeFee,
        ]);
    }

    // Agent Receipt
    if ($invoiceable instanceof \App\Models\Agent) {
        return view('payments.agent_receipt', [
            'payments'      => $payments,
            'agent'         => $invoiceable,
            'batchId'       => $batchId,
            'vatTotal'      => $vatTotal,
            'nonVatTotal'   => $nonVatTotal,
            'stampFee'      => $stampFee,
            'surchargeFee'  => $surchargeFee,
        ]);
    }

    // Customer Receipt (default fallback)
    if ($invoiceable instanceof \App\Models\Customer) {
        return view('payments.receipt', [
            'payments'      => $payments,
            'customer'      => $invoiceable,
            'batchId'       => $batchId,
            'vatTotal'      => $vatTotal,
            'nonVatTotal'   => $nonVatTotal,
            'stampFee'      => $stampFee,
            'surchargeFee'  => $surchargeFee,
        ]);
    }

    abort(404, 'Unsupported receipt type.');
}




    public function history(Customer $customer)
        {
            // Load all payments with invoice + bank details
            $payments = Payment::with(['invoice', 'bank'])
                ->whereHas('invoice', fn($q) => $q->where('invoiceable_id', $customer->id)
                                                ->where('invoiceable_type', Customer::class))
                ->orderBy('payment_date', 'desc')
                ->get();

            // Group by batch (so one receipt = one group)
            $paymentsByBatch = $payments->groupBy('batch_id');

            return view('payments.history', [
                'customer' => $customer,
                'paymentsByBatch' => $paymentsByBatch,
            ]);
        }



// ...

/**
 * Show agents with outstanding balances
 */
public function agentOutstanding(Request $request): View
{
    $agents = Agent::with('invoices')
        ->whereHas('invoices', function ($query) {
            $query->whereIn('status', ['unpaid', 'partially-paid']);
        })
        ->get()
        ->map(function ($agent) {
            $agent->outstanding_balance = $agent->invoices->sum(function ($invoice) {
                return $invoice->total_amount - $invoice->amount_paid;
            });
            return $agent;
        })
        ->filter(fn($agent) => $agent->outstanding_balance > 0);

    return view('payments.agentOutstanding', compact('agents'));
}

/**
 * Show bulk payment form for agent
 */
public function createBulkAgent(Request $request, Agent $agent = null): View
{
    $allAgents = Agent::whereHas('invoices', function ($query) {
        $query->whereIn('status', ['unpaid', 'partially-paid']);
    })->orderBy('name')->get();

    $banks = Bank::where('is_active', true)->orderBy('name')->get();

    return view('payments.create_bulk_agent', [
        'agents' => $allAgents,
        'selectedAgentId' => $agent?->id,
        'banks' => $banks,
    ]);
}

/**
 * Store a new bulk agent payment
 */
public function storeBulkAgent(Request $request): RedirectResponse
{
    $request->validate([
        'agent_id' => 'required|exists:agents,id',
        'invoice_ids' => 'required|array|min:1',
        'invoice_ids.*' => 'exists:invoices,id',
        'amount' => 'required|numeric|min:0.01',
        'stamp_fee' => 'nullable|numeric|min:0',
        'payment_date' => 'required|date',
        'payment_method' => 'required|string',
        'reference_number' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        $amountToDistribute = $request->amount;
        $stampFee = $request->stamp_fee ?? 0;

        $batchId = uniqid('BATCH-');

        $invoices = Invoice::where('invoiceable_id', $request->agent_id)
            ->where('invoiceable_type', Agent::class)
            ->whereIn('id', $request->invoice_ids)
            ->whereIn('status', ['unpaid', 'partially-paid'])
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($invoices as $invoice) {
            if ($amountToDistribute <= 0) break;

            $balanceDue = $invoice->total_amount - $invoice->amount_paid;
            $paymentAmount = min($amountToDistribute, $balanceDue);

            if ($paymentAmount > 0) {
                $invoice->payments()->create([
                    'payment_date'        => $request->payment_date,
                    'amount'              => $paymentAmount,
                    'payment_method'      => $request->payment_method,
                    'reference_number'    => $request->reference_number,
                    'notes'               => $request->notes ?? null,
                    'batch_id'            => $batchId,
                    'bank_id'             => $request->bank_id,
                    'cheque_number'       => $request->cheque_number,
                    'cheque_date'         => $request->cheque_date,
                    'cheque_received_date'=> $request->cheque_received_date,
                    'stamp_fee'       => $request->stamp_fee ?? 0,    
                    'surcharge_fee'   => $request->surcharge_fee ?? 0, 
                ]);

                $totalPaid = $invoice->payments()->sum('amount');
                $invoice->amount_paid = $totalPaid;

                $invoice->status = abs($invoice->amount_paid - $invoice->total_amount) < 0.01
                    ? 'paid'
                    : 'partially-paid';
                $invoice->save();

                $amountToDistribute -= $paymentAmount;
            }
        }

        DB::commit();

        return redirect()->route('payments.receipt', $batchId)
            ->with('stampFee', $stampFee);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
    }
}
public function supplierOutstanding(): View
{
    $suppliers = Supplier::whereHas('invoices', function ($q) {
        $q->whereIn('status', ['unpaid', 'partially-paid']);
    })
    ->with(['invoices' => function ($q) {
        $q->whereIn('status', ['unpaid', 'partially-paid']);
    }])
    ->orderBy('supplier_name')
    ->get();

    return view('payments.supplierOutstanding', compact('suppliers'));
}
public function createBulkSupplier(Request $request, Supplier $supplier = null): View
{
    $suppliers = Supplier::whereHas('invoices', function ($q) {
        $q->whereIn('status', ['unpaid', 'partially-paid']);
    })
    ->orderBy('supplier_name')
    ->get();

    $banks = Bank::where('is_active', true)->orderBy('name')->get();

    return view('payments.create_bulk_supplier', [
        'suppliers' => $suppliers,
        'selectedSupplierId' => $supplier?->id,
        'banks' => $banks,
    ]);
}

// Store bulk supplier payment
public function storeBulkSupplier(Request $request): RedirectResponse
{
    $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'invoice_ids' => 'required|array|min:1',
        'invoice_ids.*' => 'exists:invoices,id',
        'amount' => 'required|numeric|min:0.01',
        'stamp_fee' => 'nullable|numeric|min:0',
        'payment_date' => 'required|date',
        'payment_method' => 'required|string',
        'reference_number' => 'nullable|string|max:255',
    ]);

    DB::beginTransaction();
    try {
        $amountToDistribute = $request->amount;
        $stampFee = $request->stamp_fee ?? 0;
        $batchId = uniqid('SUP-BATCH-');

        $invoices = Invoice::where('invoiceable_id', $request->supplier_id)
            ->where('invoiceable_type', Supplier::class)
            ->whereIn('id', $request->invoice_ids)
            ->whereIn('status', ['unpaid', 'partially-paid'])
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($invoices as $invoice) {
            if ($amountToDistribute <= 0) break;

            $balanceDue = $invoice->total_amount - $invoice->amount_paid;
            $paymentAmount = min($amountToDistribute, $balanceDue);

            if ($paymentAmount > 0) {
                $invoice->payments()->create([
                    'payment_date'         => $request->payment_date,
                    'amount'               => $paymentAmount,
                    'payment_method'       => $request->payment_method,
                    'reference_number'     => $request->reference_number,
                    'notes'                => $request->notes ?? null,
                    'batch_id'             => $batchId,
                    'bank_id'              => $request->bank_id,
                    'cheque_number'        => $request->cheque_number,
                    'cheque_date'          => $request->cheque_date,
                    'cheque_received_date' => $request->cheque_received_date,
                    'stamp_fee'       => $request->stamp_fee ?? 0,    
                    'surcharge_fee'   => $request->surcharge_fee ?? 0, 
                ]);

                $totalPaid = $invoice->payments()->sum('amount');
                $invoice->amount_paid = $totalPaid;
                $invoice->status = abs($invoice->amount_paid - $invoice->total_amount) < 0.01
                    ? 'paid'
                    : 'partially-paid';
                $invoice->save();

                $amountToDistribute -= $paymentAmount;
            }
        }

        DB::commit();

        return redirect()->route('payments.receipt', $batchId)
            ->with('stampFee', $stampFee);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
    }
}public function agentPaymentHistory(Agent $agent): View
{
    $payments = Payment::with(['invoice', 'bank'])
        ->whereHas('invoice', fn($q) => 
            $q->where('invoiceable_id', $agent->id)
              ->where('invoiceable_type', \App\Models\Agent::class)
        )
        ->orderBy('payment_date', 'desc')
        ->get();

    $paymentsByBatch = $payments->groupBy('batch_id');

    return view('payments.agent_payment_history', [
        'agent' => $agent,
        'paymentsByBatch' => $paymentsByBatch,
    ]);
}
public function supplierPaymentHistory(Supplier $supplier): View
{
    $payments = Payment::with(['invoice', 'bank'])
        ->whereHas('invoice', fn($q) =>
            $q->where('invoiceable_id', $supplier->id)
              ->where('invoiceable_type', \App\Models\Supplier::class)
        )
        ->orderBy('payment_date', 'desc')
        ->get();

    $paymentsByBatch = $payments->groupBy('batch_id');

    return view('payments.supplier_payment_history', [
        'supplier' => $supplier,
        'paymentsByBatch' => $paymentsByBatch,
    ]);
}
public function customerPayments($customerId): View
{
    $customer = \App\Models\Customer::with(['invoices' => function($q) {
        $q->with('payments')->latest();
    }])->findOrFail($customerId);

    return view('customers.customer_history', compact('customer'));
}

}
