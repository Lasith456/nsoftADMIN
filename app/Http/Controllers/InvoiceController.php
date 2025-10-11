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
use App\Models\PurchaseOrderItem;
use App\Models\Setting;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\InvoiceItem; // ⬅ adjust if your item model is named differently
use Illuminate\Support\Carbon;
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
        $type = $request->query('type', 'all');
        $query = Invoice::with('invoiceable');

        // ✅ Filter by the selected invoice type
        switch ($type) {
            case 'customer':
                $query->where('invoiceable_type', Customer::class);
                break;
            case 'supplier':
                $query->where('invoiceable_type', Supplier::class);
                break;
            case 'agent':
                $query->where('invoiceable_type', Agent::class);
                break;
        }

        // ✅ Company filter (apply only for customer invoices)
        if ($type === 'customer' && $request->filled('company_id')) {
            $query->whereHasMorph('invoiceable', [Customer::class], function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        // ✅ Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_id', 'LIKE', "%{$search}%")
                ->orWhereHasMorph(
                    'invoiceable',
                    [Customer::class, Supplier::class, Agent::class],
                    function ($subQuery, $modelType) use ($search) {
                        $nameColumn = match ($modelType) {
                            Customer::class => 'customer_name',
                            Supplier::class => 'supplier_name',
                            Agent::class    => 'name',
                        };
                        $subQuery->where($nameColumn, 'LIKE', "%{$search}%");
                    }
                );
            });
        }

        // ✅ Fetch companies for dropdown
        $companies = \App\Models\Company::orderBy('company_name')->get();

        $invoices = $query->latest()->paginate(15);

        return view('invoices.index', compact('invoices', 'type', 'companies'));
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
     * THE FIX IS HERE: This method was missing or not being loaded correctly.
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
            
            // ** THE FIX IS HERE: Add the missing fields to the create() call **
            $invoice = $supplier->invoices()->create([
                'invoice_id' => 'INV-SUPP-' . strtoupper(Str::random(6)),
                'due_date' => now()->addDays(30),
                'sub_total' => $totalAmount,
                'vat_percentage' => 0,
                'vat_amount' => 0,
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
                'is_vat_invoice' => false,
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
    public function createCustomerInvoice(Request $request): View
{
    // ✅ Load all companies
    $companies = \App\Models\Company::orderBy('company_name')->get();

    // ✅ Base query: Only Receive Notes with status 'completed' (not invoiced)
    $query = ReceiveNote::where('status', 'completed')
        ->whereHas('deliveryNotes.purchaseOrders.customer')
        ->with('deliveryNotes.purchaseOrders.customer');

    // ✅ Optional: filter by company
    if ($request->filled('company_id')) {
        $query->whereHas('deliveryNotes.purchaseOrders.customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    // ✅ Optional: filter by customer
    if ($request->filled('customer_id')) {
        $query->whereHas('deliveryNotes.purchaseOrders.customer', function ($q) use ($request) {
            $q->where('id', $request->customer_id);
        });
    }

    // ✅ Fetch completed receive notes
    $receiveNotes = $query->get();

    // ✅ Group receive notes by customer
    $groupedByCustomer = $receiveNotes->groupBy(function ($rn) {
        return $rn->deliveryNotes->first()?->purchaseOrders->first()?->customer?->id;
    })->filter();

    // ✅ Build customer + RN data structure
    $customersWithInvoices = $groupedByCustomer->map(function ($notes, $customerId) {
        $customer = $notes->first()->deliveryNotes->first()->purchaseOrders->first()->customer;
        return [
            'id' => $customer->id,
            'customer_name' => $customer->customer_name,
            'customer_id' => $customer->customer_id,
            'company_id' => $customer->company_id,
            'uninvoiced_receive_notes' => $notes->map(fn($rn) => [
                'id' => $rn->id,
                'receive_note_id' => $rn->receive_note_id,
                'received_date' => $rn->received_date,
            ])->values()->all(),
        ];
    })->sortBy('customer_name')->values();

    // ✅ Fetch all customers who have completed (not invoiced) RNs
    $allCustomers = \App\Models\Customer::whereHas('purchaseOrders.deliveryNotes.receiveNotes', function ($q) {
        $q->where('status', 'completed');
    })->orderBy('customer_name')->get();

    // ✅ Return view with data
    return view('invoices.create_customer_invoice', compact('companies', 'allCustomers', 'customersWithInvoices'));
}



// public function storeCustomerInvoice(Request $request): RedirectResponse
// {
//     $validated = $request->validate([
//         'customer_id'       => ['required', 'exists:customers,id'],
//         'receive_note_ids'  => ['required','array','min:1'],
//         'receive_note_ids.*'=> ['integer','exists:receive_notes,id'],
//     ]);

//     $customer = Customer::findOrFail($validated['customer_id']);

//     // 1. Load receive notes
//     $receiveNotes = ReceiveNote::with(['items.product', 'deliveryNotes'])
//         ->whereIn('id', $validated['receive_note_ids'])
//         ->get();

//     // 2. Handle discrepancies
//     $discrepancies = collect();
//     $shortages = [];

//     foreach ($receiveNotes as $rn) {
//         if ($rn->status === 'discrepancy') {
//             $deliveryNote = $rn->deliveryNotes->first();
//             $discrepancies->push([
//                 'rn' => $rn->receive_note_id,
//                 'dn' => $deliveryNote?->delivery_note_id ?? 'N/A',
//             ]);

//             foreach ($rn->items as $item) {
//                 if ($item->quantity_received < 0) {
//                     $shortages[] = [
//                         'product_id' => $item->product_id,
//                         'qty'        => $item->quantity_received,
//                     ];
//                 }
//             }
//         }
//     }

//     if ($discrepancies->isNotEmpty()) {
//         $messages = $discrepancies->map(fn($d) => "Receive Note: {$d['rn']}, Delivery Note: {$d['dn']}")->implode('<br>');

//         $createPoUrl = route('purchase-orders.create', [
//             'customer_id' => $customer->id,
//             'shortages'   => $shortages
//         ]);

//         $htmlMessage = "
//             Cannot generate invoice because some notes have discrepancies:<br>
//             {$messages}<br><br>
//             <a href='{$createPoUrl}' 
//                class='inline-block mt-2 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700'>
//                ➕ Create New PO
//             </a>
//         ";

//         return back()->withInput()->with('html_error', $htmlMessage);
//     }

//     // 3. Collect invoice lines
//     $rawLines = collect();
//     foreach ($receiveNotes as $rn) {
//         foreach ($rn->items as $it) {
//             if (!$it->product) continue;

//             $p   = $it->product;
//             $qty = (float) ($it->quantity_received ?? 0);
//             if ($qty <= 0) continue;

//             // Company-specific pricing
//             $companyPrice = $p->companyPrices()
//                 ->where('company_id', $customer->company_id)
//                 ->first();

//             $unitPrice = $companyPrice->selling_price ?? $p->selling_price;
//             $costPrice = $companyPrice->cost_price ?? $p->cost_price;

//             // Department appear name
//             $companyDept = $p->companyDepartments()
//                 ->where('company_id', $customer->company_id)
//                 ->first();

//             $deptName = $companyDept?->appear_name ?? optional($p->department)->name;

//             $rawLines->push([
//                 'product_id'      => $p->id,
//                 'product_name'    => $p->appear_name ?: $p->name,
//                 'department_key'  => $deptName,
//                 'department_name' => $deptName,
//                 'is_vat'          => (bool) $p->is_vat,
//                 'quantity'        => $qty,
//                 'unit_price'      => (float) $unitPrice,
//                 'cost_price'      => (float) $costPrice,
//             ]);
//         }
//     }

//     if ($rawLines->isEmpty()) {
//         return back()->withErrors(['items' => 'No products found to invoice.']);
//     }

//     $aggregated = $rawLines->groupBy('product_id')->map(function ($rows) {
//         $first = $rows->first();
//         return [
//             'product_id'      => $first['product_id'],
//             'product_name'    => $first['product_name'],
//             'department_key'  => $first['department_key'],
//             'department_name' => $first['department_name'],
//             'is_vat'          => $first['is_vat'],
//             'quantity'        => $rows->sum('quantity'),
//             'unit_price'      => (float) $first['unit_price'],
//             'cost_price'      => (float) $first['cost_price'],
//         ];
//     })->values();

//     $groups = $customer->separate_department_invoice
//         ? $aggregated->groupBy(fn($l) => $l['department_key'] ?? 'none')
//         : collect(['all' => $aggregated]);

//     $createdInvoiceIds = [];
//     $vatRate = (float) (Setting::where('key', 'vat_rate')->value('value') ?? 0);

//     DB::transaction(function () use ($groups, $customer, $vatRate, $receiveNotes, &$createdInvoiceIds) {
//         foreach ($groups as $deptKey => $lines) {
//             $isVatInvoice = $lines->contains(fn($l) => $l['is_vat']);

//             $invoice = new Invoice([
//                 'invoice_id'     => 'INV-CUST-' . strtoupper(Str::random(6)),
//                 'status'         => 'unpaid',
//                 'notes'          => ($deptKey !== 'all' && $deptKey !== 'none')
//                                     ? 'Department: '.$lines->first()['department_name']
//                                     : null,
//                 'sub_total'      => 0,
//                 'vat_percentage' => $vatRate,
//                 'vat_amount'     => 0,
//                 'total_amount'   => 0,
//                 'amount_paid'    => 0,
//                 'due_date'       => now()->addDays(30),
//                 'is_vat_invoice' => $isVatInvoice,
//             ]);
//             $invoice->invoiceable()->associate($customer);
//             $invoice->save();

//             $subTotal = 0; 
//             $totalVat = 0;
//             foreach ($lines as $l) {
//                 $lineSub = round($l['quantity'] * $l['unit_price'], 2);
//                 $lineVat = $l['is_vat'] ? round($lineSub * ($vatRate / 100), 2) : 0;

//                 $invoice->items()->create([
//                     'product_id'  => $l['product_id'],
//                     'description' => $l['product_name'],
//                     'quantity'    => $l['quantity'],
//                     'unit_price'  => $l['unit_price'],
//                     'cost_price'  => $l['cost_price'],
//                     'total'       => $lineSub + $lineVat,
//                     'vat_amount'  => $lineVat,
//                 ]);

//                 $subTotal += $lineSub;
//                 $totalVat += $lineVat;
//             }

//             $invoice->update([
//                 'sub_total'    => round($subTotal, 2),
//                 'vat_amount'   => round($totalVat, 2),
//                 'total_amount' => round($subTotal + $totalVat, 2),
//             ]);

//             // ✅ Attach Receive Notes to invoice
//             $invoice->receiveNotes()->attach($receiveNotes->pluck('id'));

//             $createdInvoiceIds[] = $invoice->invoice_id;
//         }

//         // ✅ Mark all selected RNs as invoiced
//         $receiveNotes->each->update(['status' => 'invoiced']);
//     });

//     return redirect()
//         ->route('invoices.index')
//         ->with('success', sprintf(
//             'Created %d invoice(s) for %s: %s',
//             count($createdInvoiceIds),
//             $customer->customer_name,
//             implode(', ', $createdInvoiceIds)
//         ));
// }

// public function storeCustomerInvoice(Request $request): RedirectResponse
// {
//     $validated = $request->validate([
//         'customer_id'        => ['required', 'exists:customers,id'],
//         'receive_note_ids'   => ['required', 'array', 'min:1'],
//         'receive_note_ids.*' => ['integer', 'exists:receive_notes,id'],
//     ]);

//     $customer = Customer::findOrFail($validated['customer_id']);

//     // Load RNs with items + DN items
//     $receiveNotes = ReceiveNote::with(['items.product', 'deliveryNotes.items'])
//         ->whereIn('id', $validated['receive_note_ids'])
//         ->get();

//     $discrepancies = collect();
//     $shortages = [];

//     foreach ($receiveNotes as $rn) {
//         if ($rn->status === 'discrepancy') {
//             $deliveryNote = $rn->deliveryNotes->first();
//             $discrepancies->push([
//                 'rn' => $rn->receive_note_id,
//                 'dn' => $deliveryNote?->delivery_note_id ?? 'N/A',
//             ]);

//             foreach ($rn->items as $rnItem) {
//                 $dnItem = $rn->deliveryNotes
//                     ->flatMap->items
//                     ->firstWhere('product_id', $rnItem->product_id);

//                 // Expected qty = requested OR stock+agent
//                 $expected = (float)($dnItem?->quantity_requested ?? 0);
//                 if ($expected === 0) {
//                     $expected = (float)(($dnItem?->quantity_from_stock ?? 0) + ($dnItem?->quantity_from_agent ?? 0));
//                 }

//                 $received = (float)($rnItem->quantity_received ?? 0);

//                 if ($expected > $received) {
//                     $shortages[] = [
//                         'product_id' => $rnItem->product_id,
//                         'qty'        => $expected - $received,
//                     ];
//                 }
//             }
//         }
//     }

//     // --------------------------------------
//     // 🟡 YELLOW BUTTON (create PO + invoice)
//     // --------------------------------------
//     $shortageMap = [];
//     $poCode = null;

//     if ($request->filled('create_po')) {
//         if (empty($shortages)) {
//             return back()->withErrors(['error' => 'No shortages found to create a Purchase Order.']);
//         }

//         $merged = collect($shortages)->groupBy('product_id')->map(fn($rows) => [
//             'product_id' => $rows->first()['product_id'],
//             'qty'        => collect($rows)->sum('qty'),
//         ])->values();

//         DB::beginTransaction();
//         try {
//             $last = PurchaseOrder::orderByRaw("CAST(SUBSTRING(po_id, 4) AS UNSIGNED) DESC")->first();
//             $next = $last ? intval(substr($last->po_id, 3)) + 1 : 1;
//             $poCode = 'PO-' . str_pad($next, 4, '0', STR_PAD_LEFT);

//             $po = PurchaseOrder::create([
//                 'po_id'         => $poCode,
//                 'customer_id'   => $customer->id,
//                 'status'        => 'pending',
//                 'delivery_date' => now()->addDays(7),
//                 'notes'         => 'Auto-created for shortages from discrepancy RNs',
//             ]);

//             foreach ($merged as $s) {
//                 $product = Product::find($s['product_id']);
//                 if (!$product) continue;

//                 PurchaseOrderItem::create([
//                     'purchase_order_id' => $po->id,
//                     'product_id'        => $s['product_id'],
//                     'product_name'      => $product->name,
//                     'quantity'          => $s['qty'],
//                     'unit_price'        => (float)$product->selling_price,
//                 ]);
//             }

//             // keep shortage mapping for invoice notes
//             $shortageMap = collect($merged)->pluck('qty', 'product_id')->toArray();

//             DB::commit();
//         } catch (\Throwable $e) {
//             DB::rollBack();
//             return back()->withErrors(['error' => 'Failed to create PO: ' . $e->getMessage()]);
//         }
//     }

//     // --------------------------------------
//     // 🔵 BLUE BUTTON (block if discrepancy)
//     // --------------------------------------
//     if ($discrepancies->isNotEmpty() && !$request->filled('create_po')) {
//         $messages = $discrepancies
//             ->map(fn($d) => "Receive Note: {$d['rn']}, Delivery Note: {$d['dn']}")
//             ->implode('<br>');

//         $html = "
//             Cannot generate invoice because some notes have discrepancies:<br>
//             {$messages}<br><br>
//             Please use the yellow 'Generate Invoice & Create PO' button.
//         ";

//         return back()
//             ->withInput()
//             ->with('html_error', $html);
//     }

//     // --------------------------------------
//     // ✅ INVOICE CREATION
//     // --------------------------------------
//     $rawLines = collect();

//     foreach ($receiveNotes as $rn) {
//         foreach ($rn->items as $it) {
//             if (!$it->product) continue;

//             $receivedQty = (float)($it->quantity_received ?? 0);
//             if ($receivedQty <= 0) continue;

//             $p = $it->product;

//             $companyPrice = $p->companyPrices()->where('company_id', $customer->company_id)->first();
//             $unitPrice = (float)($companyPrice->selling_price ?? $p->selling_price);
//             $costPrice = (float)($companyPrice->cost_price ?? $p->cost_price);

//             $companyDept = $p->companyDepartments()->where('company_id', $customer->company_id)->first();
//             $deptName = $companyDept?->appear_name ?? optional($p->department)->name;

//             // Add shortage note if applicable
//             $shortageNote = '';
//             if (isset($shortageMap[$p->id]) && $poCode) {
//                 $shortageNote = " [{$shortageMap[$p->id]} qty moved to {$poCode}]";
//             }

//             $rawLines->push([
//                 'product_id'      => $p->id,
//                 'product_name'    => ($p->appear_name ?: $p->name) . $shortageNote,
//                 'department_key'  => $deptName,
//                 'department_name' => $deptName,
//                 'is_vat'          => (bool)$p->is_vat,
//                 'quantity'        => $receivedQty,
//                 'unit_price'      => $unitPrice,
//                 'cost_price'      => $costPrice,
//             ]);
//         }
//     }

//     if ($rawLines->isEmpty()) {
//         return back()->withErrors(['items' => 'No products found to invoice.']);
//     }

//     $aggregated = $rawLines->groupBy('product_id')->map(function ($rows) {
//         $first = $rows->first();
//         return [
//             'product_id'      => $first['product_id'],
//             'product_name'    => $first['product_name'],
//             'department_key'  => $first['department_key'],
//             'department_name' => $first['department_name'],
//             'is_vat'          => $first['is_vat'],
//             'quantity'        => collect($rows)->sum('quantity'),
//             'unit_price'      => (float)$first['unit_price'],
//             'cost_price'      => (float)$first['cost_price'],
//         ];
//     })->values();

//     $groups = $customer->separate_department_invoice
//         ? $aggregated->groupBy(fn($l) => $l['department_key'] ?? 'none')
//         : collect(['all' => $aggregated]);

//     $createdInvoiceIds = [];
//     $vatRate = (float)(Setting::where('key', 'vat_rate')->value('value') ?? 0);

//     DB::transaction(function () use ($groups, $customer, $vatRate, $receiveNotes, &$createdInvoiceIds) {
//         foreach ($groups as $deptKey => $lines) {
//             $isVatInvoice = $lines->contains(fn($l) => $l['is_vat']);

//             $invoice = new Invoice([
//                 'invoice_id'     => 'INV-CUST-' . strtoupper(Str::random(6)),
//                 'status'         => 'unpaid',
//                 'notes'          => ($deptKey !== 'all' && $deptKey !== 'none')
//                     ? 'Department: ' . ($lines->first()['department_name'] ?? '')
//                     : null,
//                 'sub_total'      => 0,
//                 'vat_percentage' => $vatRate,
//                 'vat_amount'     => 0,
//                 'total_amount'   => 0,
//                 'amount_paid'    => 0,
//                 'due_date'       => now()->addDays(30),
//                 'is_vat_invoice' => $isVatInvoice,
//             ]);
//             $invoice->invoiceable()->associate($customer);
//             $invoice->save();

//             $subTotal = 0;
//             $totalVat = 0;

//             foreach ($lines as $l) {
//                 $lineSub = round($l['quantity'] * $l['unit_price'], 2);
//                 $lineVat = $l['is_vat'] ? round($lineSub * ($vatRate / 100), 2) : 0;

//                 $invoice->items()->create([
//                     'product_id'  => $l['product_id'],
//                     'description' => $l['product_name'],
//                     'quantity'    => $l['quantity'],
//                     'unit_price'  => $l['unit_price'],
//                     'cost_price'  => $l['cost_price'],
//                     'total'       => $lineSub + $lineVat,
//                     'vat_amount'  => $lineVat,
//                 ]);

//                 $subTotal += $lineSub;
//                 $totalVat += $lineVat;
//             }

//             $invoice->update([
//                 'sub_total'    => round($subTotal, 2),
//                 'vat_amount'   => round($totalVat, 2),
//                 'total_amount' => round($subTotal + $totalVat, 2),
//             ]);

//             if (method_exists($invoice, 'receiveNotes')) {
//                 $invoice->receiveNotes()->attach($receiveNotes->pluck('id'));
//             }

//             $createdInvoiceIds[] = $invoice->invoice_id;
//         }

//         $receiveNotes->each->update(['status' => 'invoiced']);
//     });

//     return redirect()
//         ->route('invoices.index')
//         ->with('success', sprintf(
//             'Created %d invoice(s) for %s: %s',
//             count($createdInvoiceIds),
//             $customer->customer_name,
//             implode(', ', $createdInvoiceIds)
//         ));
// }

public function storeCustomerInvoice(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'customer_id'        => ['required', 'exists:customers,id'],
        'receive_note_ids'   => ['required', 'array', 'min:1'],
        'receive_note_ids.*' => ['integer', 'exists:receive_notes,id'],
        'updated_prices'     => ['nullable', 'array'],
    ]);

    $customer = Customer::findOrFail($validated['customer_id']);

    // Decode edited prices coming from front-end
    $updatedPrices = collect($request->input('updated_prices', []))
        ->map(fn($p) => json_decode($p, true))
        ->keyBy('product_id');

    // Load Receive Notes with product info
    $receiveNotes = ReceiveNote::with(['items.product'])
        ->whereIn('id', $validated['receive_note_ids'])
        ->get();

    // Prepare invoice line data
    $invoiceLines = collect();

    foreach ($receiveNotes as $rn) {
        foreach ($rn->items as $item) {
            if (!$item->product) continue;

            $product = $item->product;
            $qty     = (float) ($item->quantity_received ?? 0);
            if ($qty <= 0) continue;

            // Use updated price if available
            $editedPrice = $updatedPrices->has($product->id)
                ? (float) $updatedPrices[$product->id]['updated_price']
                : (float) ($product->selling_price ?? 0);

            $invoiceLines->push([
                'product_id'   => $product->id,
                'description'  => $product->name,
                'quantity'     => $qty,
                'unit_price'   => $editedPrice,
                'cost_price'   => $product->cost_price ?? 0,
                'is_vat'       => (bool) $product->is_vat,
            ]);
        }
    }

    if ($invoiceLines->isEmpty()) {
        return back()->withErrors(['items' => 'No valid products found for invoice.']);
    }

    // Merge duplicates (same product in multiple RNs)
    $groupedLines = $invoiceLines->groupBy('product_id')->map(function ($rows) {
        $first = $rows->first();
        return [
            'product_id'  => $first['product_id'],
            'description' => $first['description'],
            'quantity'    => collect($rows)->sum('quantity'),
            'unit_price'  => (float) $first['unit_price'],
            'cost_price'  => (float) $first['cost_price'],
            'is_vat'      => $first['is_vat'],
        ];
    })->values();

    // VAT rate
    $vatRate = (float)(Setting::where('key', 'vat_rate')->value('value') ?? 0);

    // Generate custom Invoice ID (first letter of customer + company)
    $custInitial = strtoupper(substr($customer->customer_name, 0, 1));
    $compInitial = strtoupper(substr(optional($customer->company)->company_name ?? 'C', 0, 1));
    $invoiceCode = "INV-CUST-{$custInitial}{$compInitial}-" . strtoupper(Str::random(5));

    DB::beginTransaction();
    try {
        $isVatInvoice = $groupedLines->contains(fn($l) => $l['is_vat']);

        // Create Invoice
        $invoice = new Invoice([
            'invoice_id'     => $invoiceCode,
            'status'         => 'unpaid',
            'due_date'       => now()->addDays(30),
            'is_vat_invoice' => $isVatInvoice,
            'sub_total'      => 0,
            'vat_percentage' => $vatRate,
            'vat_amount'     => 0,
            'total_amount'   => 0,
            'amount_paid'    => 0,
        ]);
        $invoice->invoiceable()->associate($customer);
        $invoice->save();

        // Add line items
        $subTotal = 0;
        $vatTotal = 0;

        foreach ($groupedLines as $line) {
            $lineSub = round($line['quantity'] * $line['unit_price'], 2);
            $lineVat = $line['is_vat'] ? round($lineSub * ($vatRate / 100), 2) : 0;

            $invoice->items()->create([
                'product_id'  => $line['product_id'],
                'description' => $line['description'],
                'quantity'    => $line['quantity'],
                'unit_price'  => $line['unit_price'],
                'cost_price'  => $line['cost_price'],
                'total'       => $lineSub + $lineVat,
                'vat_amount'  => $lineVat,
            ]);

            $subTotal += $lineSub;
            $vatTotal += $lineVat;
        }

        // Update invoice totals
        $invoice->update([
            'sub_total'    => round($subTotal, 2),
            'vat_amount'   => round($vatTotal, 2),
            'total_amount' => round($subTotal + $vatTotal, 2),
        ]);

        // Link RNs and mark them as invoiced
        if (method_exists($invoice, 'receiveNotes')) {
            $invoice->receiveNotes()->attach($receiveNotes->pluck('id'));
        }
        $receiveNotes->each->update(['status' => 'invoiced']);

        DB::commit();

        return redirect()
            ->route('invoices.show', $invoice->id)
            ->with('success', "Invoice {$invoiceCode} created successfully for {$customer->customer_name}.");
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
    }
}





public function createAgentInvoice(): View
{
    $agents = Agent::whereHas('deliveryItems', function ($query) {
        $query->where('agent_invoiced', false);
    })->with(['deliveryItems' => function ($query) {
        $query->where('agent_invoiced', false)
              ->with(['deliveryNote', 'product']);
    }])->get();

    // Replace qty with what is already in invoice_items
    foreach ($agents as $agent) {
        foreach ($agent->deliveryItems as $item) {
            // 🔑 find how much was already invoiced to customer
            $qty = \App\Models\InvoiceItem::where('product_id', $item->product_id)
                ->whereHas('invoice.receiveNotes.deliveryNotes.items', function ($q) use ($item) {
                    $q->where('id', $item->id);
                })
                ->sum('quantity');

            $item->to_invoice_qty = $qty;
        }
    }

    return view('invoices.create_agent_invoice', compact('agents'));
}


// public function storeAgentInvoice(Request $request): RedirectResponse
// {
//     $validated = $request->validate([
//         'agent_id'            => 'required|exists:agents,id',
//         'delivery_item_ids'   => 'required|array|min:1',
//         'delivery_item_ids.*' => 'exists:delivery_note_items,id',
//     ]);

//     DB::beginTransaction();
//     try {
//         $agent = Agent::findOrFail($validated['agent_id']);

//         // Only selected items
//         $itemsToInvoice = DeliveryNoteItem::where('agent_id', $agent->id)
//             ->where('quantity_from_agent', '>', 0)
//             ->where('agent_invoiced', false)
//             ->whereIn('id', $validated['delivery_item_ids']) // ✅ filter selected
//             ->with(['deliveryNote', 'product', 'agent'])
//             ->get();

//         if ($itemsToInvoice->isEmpty()) {
//             return back()->withErrors(['error' => 'No pending items found for the selected delivery notes.']);
//         }

//         $totalAmount = 0;
//         $invoiceItemsData = [];

//         foreach ($itemsToInvoice as $item) {
//             $pivot = $item->agent->products()
//                 ->where('products.id', $item->product_id)
//                 ->first();

//             if (!$pivot || is_null($pivot->pivot->price_per_case)) {
//                 throw new \Exception("No price_per_case found for Agent {$item->agent->id} and Product {$item->product_id}");
//             }

//             $unitPrice = $pivot->pivot->price_per_case;
//             $total     = $unitPrice * $item->quantity_from_agent;

//             $invoiceItemsData[] = [
//                 'description' => "Fulfilled Shortage: {$item->quantity_from_agent} x " .
//                                  ($item->product?->name ?? $item->product_name) .
//                                  " for DN-{$item->deliveryNote->delivery_note_id}",
//                 'quantity'   => $item->quantity_from_agent,
//                 'unit_price' => $unitPrice,
//                 'total'      => $total,
//             ];

//             $totalAmount += $total;
//         }

//         // Create Invoice
//         $invoice = $agent->invoices()->create([
//             'invoice_id'     => 'INV_AGENT-' . strtoupper(Str::random(6)),
//             'due_date'       => now()->addDays(30),
//             'sub_total'      => $totalAmount,
//             'vat_percentage' => 0,
//             'vat_amount'     => 0,
//             'total_amount'   => $totalAmount,
//             'status'         => 'unpaid',
//             'is_vat_invoice' => false,
//         ]);

//         $invoice->items()->createMany($invoiceItemsData);

//         // Mark selected items invoiced
//         DeliveryNoteItem::whereIn('id', $itemsToInvoice->pluck('id'))
//             ->update(['agent_invoiced' => true]);

//         DB::commit();

//         return redirect()->route('invoices.show', $invoice->id)
//             ->with('success', 'Agent invoice generated successfully.');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         return back()->withInput()->withErrors(['error' => $e->getMessage()]);
//     }
// }
public function storeAgentInvoice(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'agent_id'            => 'required|exists:agents,id',
        'delivery_item_ids'   => 'required|array|min:1',
        'delivery_item_ids.*' => 'exists:delivery_note_items,id',
    ]);

    DB::beginTransaction();
    try {
        $agent = Agent::findOrFail($validated['agent_id']);

        // Only selected items
        $itemsToInvoice = DeliveryNoteItem::where('agent_id', $agent->id)
            ->where('quantity_from_agent', '>', 0)
            ->where('agent_invoiced', false)
            ->whereIn('id', $validated['delivery_item_ids'])
            ->with(['deliveryNote.receiveNotes', 'product', 'agent'])
            ->get();

        if ($itemsToInvoice->isEmpty()) {
            return back()->withErrors(['error' => 'No pending items found for the selected delivery notes.']);
        }

        // 🔴 Block if RN has discrepancy
        $discrepancyItems = $itemsToInvoice->filter(function ($item) {
            return $item->deliveryNote
                ->receiveNotes
                ->contains(fn($rn) => $rn->status === 'discrepancy');
        });

        if ($discrepancyItems->isNotEmpty()) {
            $blockedList = $discrepancyItems
                ->map(fn($i) => "DN {$i->deliveryNote->delivery_note_id} / Product {$i->product?->name}")
                ->implode('<br>');

            return back()->withErrors([
                'error' => "Cannot generate Agent Invoice because some linked Receive Notes have discrepancies:<br>{$blockedList}"
            ]);
        }

        // ✅ Safe to continue invoice creation
        $totalAmount = 0;
        $invoiceItemsData = [];

        foreach ($itemsToInvoice as $item) {
            $pivot = $item->agent->products()
                ->where('products.id', $item->product_id)
                ->first();

            if (!$pivot || is_null($pivot->pivot->price_per_case)) {
                throw new \Exception("No price_per_case found for Agent {$item->agent->id} and Product {$item->product_id}");
            }

            $unitPrice = $pivot->pivot->price_per_case;
            $total     = $unitPrice * $item->quantity_from_agent;

            $invoiceItemsData[] = [
                'description' => "Fulfilled Shortage: {$item->quantity_from_agent} x " .
                                 ($item->product?->name ?? $item->product_name) .
                                 " for DN-{$item->deliveryNote->delivery_note_id}",
                'quantity'   => $item->quantity_from_agent,
                'unit_price' => $unitPrice,
                'total'      => $total,
            ];

            $totalAmount += $total;
        }

        // Create Agent Invoice
        $invoice = $agent->invoices()->create([
            'invoice_id'     => 'INV_AGENT-' . strtoupper(Str::random(6)),
            'due_date'       => now()->addDays(30),
            'sub_total'      => $totalAmount,
            'vat_percentage' => 0,
            'vat_amount'     => 0,
            'total_amount'   => $totalAmount,
            'status'         => 'unpaid',
            'is_vat_invoice' => false,
        ]);

        $invoice->items()->createMany($invoiceItemsData);

        // Mark invoiced
        DeliveryNoteItem::whereIn('id', $itemsToInvoice->pluck('id'))
            ->update(['agent_invoiced' => true]);

        DB::commit();

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Agent invoice generated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors(['error' => $e->getMessage()]);
    }
}


    public function showOpt2($id)
{
    $invoice = \App\Models\Invoice::with(['items.product', 'invoiceable'])->findOrFail($id);
    return view('invoices.showopt2', compact('invoice'));
}
public function printInvoice($id)
{
    $invoice = Invoice::with('items')->findOrFail($id);
if (!$invoice->invoice_date && $invoice->created_at) {
        $invoice->invoice_date = $invoice->created_at;
    }
    // Convert total amount to words (you can use helper if you have one)
    $invoice->amount_in_words = $this->convertToWords($invoice->total_amount);

    return view('invoices.printopt3', compact('invoice'));
}

/**
 * Convert numbers to words (basic version).
 * Replace with a package/helper if needed.
 */
private function convertToWords($number)
{
    $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
    return ucfirst($f->format($number));
}
public function destroy(Invoice $invoice): RedirectResponse
{
    // If invoice has payments, check amounts
    $totalPaid = $invoice->payments()->sum('amount');

    if ($invoice->status === 'paid' || $totalPaid > 0) {
        // Calculate outstanding balance
        $outstanding = 0;
        if ($invoice->invoiceable) {
            $outstanding = $invoice->invoiceable->invoices()
                ->where('status', 'unpaid')
                ->sum(DB::raw('total_amount - amount_paid'));
        }

        return redirect()->route('invoices.index')
            ->withErrors([
                'error' => sprintf(
                    'Invoice %s cannot be deleted because it has payments. Outstanding balance for %s is %.2f.',
                    $invoice->invoice_id,
                    class_basename($invoice->invoiceable_type),
                    $outstanding
                )
            ]);
    }

    DB::beginTransaction();
    try {
        // ✅ Restore Receive Notes back to 'completed' if attached
        if (method_exists($invoice, 'receiveNotes')) {
            $rnIds = $invoice->receiveNotes()->pluck('receive_notes.id'); 

            if ($rnIds->isNotEmpty()) {
                \App\Models\ReceiveNote::whereIn('id', $rnIds)
                    ->update(['status' => 'completed']); // <-- change back
            }

            $invoice->receiveNotes()->detach();
        }

        // Delete items first
        $invoice->items()->delete();

        // Delete payments if any (safety)
        $invoice->payments()->delete();

        // Delete invoice
        $invoice->delete();

        DB::commit();
        return redirect()->route('invoices.index')
            ->with('success', "Invoice {$invoice->invoice_id} deleted successfully. Linked Receive Notes set to 'completed'.");
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Failed to delete invoice: ' . $e->getMessage()]);
    }
}
public function fetchReceiveNoteProducts(Request $request)
{
    try {
        $request->validate([
            'receive_note_ids' => 'required|array|min:1',
            'receive_note_ids.*' => 'exists:receive_notes,id',
        ]);

        // ✅ Load related products through items
        $notes = \App\Models\ReceiveNote::with('items.product')
            ->where('status', 'completed')
            ->whereIn('id', $request->receive_note_ids)
            ->get();

        // ✅ Map to product structure
        $products = $notes->flatMap(function ($rn) {
            return $rn->items->map(function ($item) {
                return [
                    'product_id'        => $item->product->id ?? null,
                    'product_name'      => $item->product->name ?? 'Unknown Product',
                    'quantity_received' => $item->quantity_received ?? 0,
                    'default_price'     => $item->product->selling_price ?? 0,
                ];
            });
        })
        ->filter(fn($p) => !is_null($p['product_id'])) // remove nulls
        ->values();

        return response()->json($products);
    } catch (\Throwable $e) {
        // Log and return detailed message
        \Log::error('fetchReceiveNoteProducts error: ' . $e->getMessage());
        return response()->json([
            'error'   => 'Failed to fetch products',
            'message' => $e->getMessage(),
        ], 500);
    }
}



}