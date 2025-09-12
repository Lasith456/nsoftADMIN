<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\DeliveryNoteItem; 
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Agent;
use App\Models\Grn;
use App\Models\DeliveryNote;
use App\Models\ReceiveNote;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:invoice-list|invoice-create|invoice-show', ['only' => ['index','show', 'print']]);
        $this->middleware('permission:invoice-create', ['only' => ['create', 'createFromPurchaseOrder', 'storeFromPurchaseOrder']]);
    }
    
    public function index(Request $request): View
    {
        $query = Invoice::with('invoiceable');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('invoice_id', 'LIKE', "%{$search}%")
                ->orWhereHasMorph('invoiceable', [Customer::class, Supplier::class, Agent::class], function ($q, $type) use ($search) {
                    $nameColumn = match ($type) {
                        Customer::class => 'customer_name',
                        Supplier::class => 'supplier_name',
                        Agent::class => 'name',
                    };
                    $q->where($nameColumn, 'LIKE', "%{$search}%");
                });
        }

        $invoices = $query->latest()->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['items', 'payments', 'invoiceable']);
        $outstandingBalance = 0;
        if ($invoice->invoiceable_type === 'App\Models\Customer') {
            $outstandingBalance = $invoice->invoiceable->invoices()->sum(DB::raw('total_amount - amount_paid'));
        }
        return view('invoices.show', compact('invoice', 'outstandingBalance'));
    }

    /**
     * Show the print-friendly version of the invoice.
     */
    public function print(Invoice $invoice): View
    {
        $invoice->load(['items', 'invoiceable']);
        return view('invoices.print', compact('invoice'));
    }
    
    public function create(): View
    {
        return view('invoices.create_selection');
    }

    public function createFromReceiveNote(ReceiveNote $receiveNote): View
    {
        $receiveNote->load('items.product', 'deliveryNotes.purchaseOrders.customer');
        return view('invoices.create_from_rn', compact('receiveNote'));
    }

    public function storeFromReceiveNote(Request $request, ReceiveNote $receiveNote): RedirectResponse
    {
        if ($receiveNote->status === 'invoiced') {
            return back()->withErrors(['error' => 'An invoice has already been generated for this receive note.']);
        }
        
        DB::beginTransaction();
        try {
            $customer = $receiveNote->deliveryNotes->first()->purchaseOrders->first()->customer;
            if (!$customer) {
                throw new \Exception('The selected receive note is not associated with a customer.');
            }

            $totalAmount = 0;
            $invoiceItemsData = [];

            foreach ($receiveNote->items as $item) {
                $total = $item->product->selling_price * $item->quantity_received;
                $totalAmount += $total;
                $invoiceItemsData[] = [
                    'description' => $item->product->name,
                    'quantity' => $item->quantity_received,
                    'unit_price' => $item->product->selling_price,
                    'total' => $total,
                ];
            }

            $invoice = $customer->invoices()->create([
                'invoice_id' => 'INV-CUST-' . strtoupper(Str::random(6)),
                'due_date' => now()->addDays(30),
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
            ]);

            $invoice->items()->createMany($invoiceItemsData);
            $receiveNote->update(['status' => 'invoiced']);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice generated from Receive Note successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function createSupplierInvoice(): View
    {
        $suppliers = Supplier::whereHas('grns', function ($query) {
            $query->where('status', 'confirmed');
        })->with(['grns' => function ($query) {
            $query->where('status', 'confirmed');
        }])->get();

        return view('invoices.create_supplier_invoice', compact('suppliers'));
    }

    /**
     * Store a new invoice generated from one or more GRNs.
     */
    public function storeSupplierInvoice(Request $request): RedirectResponse
    {
        $request->validate([
            'grn_ids' => 'required|array|min:1',
            'grn_ids.*' => 'exists:grns,id',
        ]);

        DB::beginTransaction();
        try {
            $grns = Grn::with('supplier', 'items.product')->whereIn('id', $request->grn_ids)->get();
            $supplier = $grns->first()->supplier;

            if (!$supplier || $grns->pluck('supplier_id')->unique()->count() > 1) {
                throw new \Exception('All selected GRNs must belong to the same supplier.');
            }

            $totalAmount = $grns->sum('net_amount');
            $invoiceItemsData = [];

            foreach ($grns as $grn) {
                if ($grn->status === 'invoiced') {
                    throw new \Exception("GRN {$grn->grn_id} has already been invoiced.");
                }
                foreach ($grn->items as $item) {
                    $invoiceItemsData[] = [
                        'description' => $item->product->name . " (from GRN: {$grn->grn_id})",
                        'quantity' => $item->quantity_received,
                        'unit_price' => $item->cost_price,
                        'total' => ($item->cost_price * $item->quantity_received) - $item->discount,
                    ];
                }
            }

            $invoice = $supplier->invoices()->create([
                'invoice_id' => 'INV-SUPP-' . strtoupper(Str::random(6)),
                'due_date' => now()->addDays(30),
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
            ]);

            $invoice->items()->createMany($invoiceItemsData);
            Grn::whereIn('id', $request->grn_ids)->update(['status' => 'invoiced']);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Supplier invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * CUSTOMER INVOICING (from Receive Notes)
     */
 public function createCustomerInvoice(): View
    {
        // **THE FIX IS HERE**: Use the full, nested relationship path for the query.
        $customers = Customer::whereHas('purchaseOrders.deliveryNotes.receiveNotes', function ($query) {
            $query->where('status', '!=', 'invoiced');
        })
        // Eager load the relationship chain to prevent performance issues (N+1 problem)
        ->with('purchaseOrders.deliveryNotes.receiveNotes')
        ->get();

        return view('invoices.create_customer_invoice', compact('customers'));
    }

    public function storeCustomerInvoice(Request $request): RedirectResponse
    {
        $request->validate(['receive_note_ids' => 'required|array|min:1']);
        
        DB::beginTransaction();
        try {
            $receiveNotes = ReceiveNote::with('items.product', 'deliveryNotes.purchaseOrders.customer')->whereIn('id', $request->receive_note_ids)->get();
            $customer = $receiveNotes->first()->deliveryNotes->first()->purchaseOrders->first()->customer;

            if (!$customer || $receiveNotes->some(fn($rn) => $rn->deliveryNotes->first()->purchaseOrders->first()->customer_id !== $customer->id)) {
                throw new \Exception('All selected receive notes must belong to the same customer.');
            }

            $totalAmount = 0;
            $invoiceItemsData = [];

            foreach ($receiveNotes as $rn) {
                if ($rn->status === 'invoiced') throw new \Exception("Receive Note {$rn->receive_note_id} has already been invoiced.");
                foreach ($rn->items as $item) {
                    $total = $item->product->selling_price * $item->quantity_received;
                    $totalAmount += $total;
                    $invoiceItemsData[] = [
                        'description' => $item->product->name . " (from RN: {$rn->receive_note_id})",
                        'quantity' => $item->quantity_received,
                        'unit_price' => $item->product->selling_price,
                        'total' => $total,
                    ];
                }
            }

            $invoice = $customer->invoices()->create([
                'invoice_id' => 'INV-CUST-' . strtoupper(Str::random(6)),
                'due_date' => now()->addDays(30),
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
            ]);

            $invoice->items()->createMany($invoiceItemsData);
            ReceiveNote::whereIn('id', $request->receive_note_ids)->update(['status' => 'invoiced']);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Customer invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * AGENT INVOICING (from Delivery Note Items)
     */
    public function createAgentInvoice(): View
    {
        $agents = Agent::whereHas('deliveryItems', function ($query) {
            $query->where('quantity_from_agent', '>', 0)
                  ->where('agent_invoiced', false)
                  ->whereHas('deliveryNote.receiveNotes');
        })->with(['deliveryItems' => function ($query) {
            $query->where('quantity_from_agent', '>', 0)
                  ->where('agent_invoiced', false)
                  ->whereHas('deliveryNote.receiveNotes')
                  ->with('deliveryNote');
        }])->get();
        return view('invoices.create_agent_invoice', compact('agents'));
    }

    /**
     * Store a new invoice generated for an Agent.
     */
    public function storeAgentInvoice(Request $request): RedirectResponse
    {
        $request->validate(['agent_id' => 'required|exists:agents,id']);
        DB::beginTransaction();
        try {
            $agent = Agent::findOrFail($request->agent_id);
            
            $itemsToInvoice = DeliveryNoteItem::where('agent_id', $agent->id)
                ->where('quantity_from_agent', '>', 0)
                ->where('agent_invoiced', false)
                ->whereHas('deliveryNote.receiveNotes')
                ->with('deliveryNote', 'agent.product')
                ->get();

            if($itemsToInvoice->isEmpty()) {
                return back()->withErrors(['error' => 'No pending items to invoice for this agent.']);
            }

            $totalAmount = 0;
            $invoiceItemsData = [];
            foreach ($itemsToInvoice as $item) {
                $total = $item->agent->price_per_case * $item->quantity_from_agent;
                $totalAmount += $total;
                $invoiceItemsData[] = [
                    'description' => "Fulfilled Shortage: {$item->quantity_from_agent} x {$item->product_name} for DN-{$item->deliveryNote->delivery_note_id}",
                    'quantity' => $item->quantity_from_agent,
                    'unit_price' => $item->agent->price_per_case,
                    'total' => $total,
                ];
            }

            $invoice = $agent->invoices()->create([
                'invoice_id' => 'INV-AGENT-' . strtoupper(Str::random(6)),
                'due_date' => now()->addDays(30),
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
            ]);

            $invoice->items()->createMany($invoiceItemsData);
            DeliveryNoteItem::whereIn('id', $itemsToInvoice->pluck('id'))->update(['agent_invoiced' => true]);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Agent invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}