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
    $allCustomers = Customer::whereHas('invoices', function ($query) {
        $query->whereIn('status', ['unpaid', 'partially-paid']);
    })->orderBy('customer_name')->get();
    
    $selectedCustomerId = $customer?->id;
    return view('payments.create_bulk', [
        'customers' => $allCustomers,
        'selectedCustomerId' => $selectedCustomerId,
    ]);
}
    /**
     * Store a new bulk payment and distribute it across selected invoices.
     */
    public function storeBulk(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $amountToDistribute = $request->amount;
            
            // ** THE FIX IS HERE: Use the correct polymorphic relationship columns **
            $invoices = Invoice::where('invoiceable_id', $request->customer_id)
                ->where('invoiceable_type', Customer::class) // Ensure we only get customer invoices
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
                        'payment_date' => $request->payment_date,
                        'amount' => $paymentAmount,
                        'payment_method' => $request->payment_method,
                        'reference_number' => $request->reference_number,
                    ]);

                    // Refresh the sum of payments to get the most accurate total
                    $totalPaid = $invoice->payments()->sum('amount');
                    $invoice->amount_paid = $totalPaid;
                    
                    if (abs($invoice->amount_paid - $invoice->total_amount) < 0.01) {
                        $invoice->status = 'paid';
                    } else {
                        $invoice->status = 'partially-paid';
                    }
                    $invoice->save();
                    
                    $amountToDistribute -= $paymentAmount;
                }
            }

            DB::commit();
            return redirect()->route('payments.history.customer')->with('success', 'Bulk payment recorded and distributed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
    
}
