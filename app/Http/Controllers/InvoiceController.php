<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\DeliveryNoteItem;
use App\Models\Grn;
use App\Models\Invoice;
use App\Models\ReceiveNote;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:invoice-list|invoice-create|invoice-show', ['only' => ['index','show']]);
        $this->middleware('permission:invoice-create', ['only' => ['create', 'createCustomerInvoice', 'storeCustomerInvoice', 'createAgentInvoice', 'storeAgentInvoice', 'createSupplierInvoice', 'storeSupplierInvoice']]);
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

    public function create(): View
    {
        return view('invoices.create_selection');
    }

    /**
     * CUSTOMER INVOICING (from Receive Notes)
     */
    public function createCustomerInvoice(): View
    {
        $customers = Customer::whereHas('purchaseOrders.deliveryNotes.receiveNotes', function ($query) {
            $query->where('status', '!=', 'invoiced');
        })->with(['purchaseOrders.deliveryNotes.receiveNotes' => function ($query) {
            $query->where('status', '!=', 'invoiced');
        }])->get();

        $customers->each(function ($customer) {
            $customer->receive_notes = $customer->purchaseOrders->flatMap(function ($po) {
                return $po->deliveryNotes->flatMap(function ($dn) {
                    return $dn->receiveNotes;
                });
            })->unique('id');
        });
        
        $customersWithInvoices = $customers->filter(fn($customer) => $customer->receive_notes->isNotEmpty());

        return view('invoices.create_customer_invoice', compact('customersWithInvoices'));
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

            $vatItems = [];
            $nonVatItems = [];

            foreach ($receiveNotes as $rn) {
                if ($rn->status === 'invoiced') throw new \Exception("Receive Note {$rn->receive_note_id} has already been invoiced.");
                foreach ($rn->items as $item) {
                    if ($item->product->is_vat) {
                        $vatItems[] = $item;
                    } else {
                        $nonVatItems[] = $item;
                    }
                }
            }

            // Create Non-VAT Invoice if there are non-vat items
            if (!empty($nonVatItems)) {
                $this->createInvoiceForItems($customer, $nonVatItems, false);
            }

            // Create VAT Invoice if there are vat items
            if (!empty($vatItems)) {
                $this->createInvoiceForItems($customer, $vatItems, true);
            }
            
            ReceiveNote::whereIn('id', $request->receive_note_ids)->update(['status' => 'invoiced']);

            DB::commit();
            return redirect()->route('invoices.index')->with('success', 'Invoice(s) generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * SUPPLIER INVOICING (from GRNs)
     */
    public function createSupplierInvoice(): View
    {
        $suppliers = Supplier::whereHas('grns', function ($query) {
            $query->where('status', 'confirmed');
        })->with(['grns' => function ($query) {
            $query->where('status', 'confirmed');
        }])->get();

        return view('invoices.create_supplier_invoice', compact('suppliers'));
    }

    public function storeSupplierInvoice(Request $request): RedirectResponse
    {
        $request->validate(['grn_ids' => 'required|array|min:1']);
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

    public function storeAgentInvoice(Request $request): RedirectResponse
    {
        $request->validate(['delivery_item_ids' => 'required|array|min:1']);
        DB::beginTransaction();
        try {
            $itemsToInvoice = DeliveryNoteItem::with('agent', 'deliveryNote')
                                ->whereIn('id', $request->delivery_item_ids)->get();

            $agentId = $itemsToInvoice->first()->agent_id;
            if ($itemsToInvoice->some(fn($item) => $item->agent_id !== $agentId)) {
                 throw new \Exception('All selected items must belong to the same agent.');
            }
            
            $agent = Agent::find($agentId);
            $totalAmount = 0;
            $invoiceItemsData = [];

            foreach ($itemsToInvoice as $item) {
                 if ($item->agent_invoiced) {
                    throw new \Exception("Item #{$item->id} has already been invoiced.");
                }
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
            DeliveryNoteItem::whereIn('id', $request->delivery_item_ids)->update(['agent_invoiced' => true]);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Agent invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Helper function to create an invoice from a collection of items.
     */
    private function createInvoiceForItems($customer, $items, bool $isVatInvoice)
    {
        $subTotal = 0;
        $vatAmount = 0;
        $invoiceItemsData = [];

        foreach ($items as $item) {
            $itemTotal = $item->product->selling_price * $item->quantity_received;
            $subTotal += $itemTotal;

            $itemVat = 0;
            if ($isVatInvoice) {
                $itemVat = $itemTotal * 0.18; // 18% VAT
                $vatAmount += $itemVat;
            }

            $invoiceItemsData[] = [
                'description' => $item->product->name . " (from RN: {$item->receive_note_id})",
                'quantity' => $item->quantity_received,
                'unit_price' => $item->product->selling_price,
                'total' => $itemTotal,
                'vat_amount' => $itemVat,
            ];
        }

        $invoice = $customer->invoices()->create([
            'invoice_id' => 'INV-' . ($isVatInvoice ? 'VAT-' : 'NOVAT-') . strtoupper(Str::random(6)),
            'type' => $isVatInvoice ? 'vat' : 'non-vat',
            'due_date' => now()->addDays(30),
            'sub_total' => $subTotal,
            'vat_percentage' => $isVatInvoice ? 18.00 : 0.00,
            'vat_amount' => $vatAmount,
            'total_amount' => $subTotal + $vatAmount,
            'status' => 'unpaid',
        ]);

        $invoice->items()->createMany($invoiceItemsData);
    }
}

