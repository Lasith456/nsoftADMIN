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

        // ** THE FIX IS HERE: Filter by the selected invoice type **
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

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_id', 'LIKE', "%{$search}%")
                  ->orWhereHasMorph('invoiceable', [Customer::class, Supplier::class, Agent::class], function ($subQuery, $modelType) use ($search) {
                      $nameColumn = match ($modelType) {
                          Customer::class => 'customer_name',
                          Supplier::class => 'supplier_name',
                          Agent::class => 'name',
                      };
                      $subQuery->where($nameColumn, 'LIKE', "%{$search}%");
                  });
            });
        }

        $invoices = $query->latest()->paginate(15);
        
        // Pass the current type to the view to highlight the active tab
        return view('invoices.index', compact('invoices', 'type'));
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
    public function createCustomerInvoice(): View
    {
        // ** THE FIX IS HERE: Create a clean data structure for the view. **

        // 1. Get all uninvoiced receive notes that have a customer.
        $receiveNotes = ReceiveNote::where('status', '!=', 'invoiced')
            ->whereHas('deliveryNotes.purchaseOrders.customer')
            ->with('deliveryNotes.purchaseOrders.customer')
            ->get();

        // 2. Group these notes by their customer's ID.
        $groupedByCustomer = $receiveNotes->groupBy(function ($rn) {
            return $rn->deliveryNotes->first()?->purchaseOrders->first()?->customer?->id;
        })->filter(); // Use filter() to remove any notes that failed to group (e.g., null customer ID).

        // 3. Map the grouped data into a simple array for the view. This avoids model serialization issues.
        $customersWithInvoices = $groupedByCustomer->map(function ($notes, $customerId) {
            $customer = $notes->first()->deliveryNotes->first()->purchaseOrders->first()->customer;
            return [
                'id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'customer_id' => $customer->customer_id,
                'uninvoiced_receive_notes' => $notes->map(fn($rn) => [
                    'id' => $rn->id,
                    'receive_note_id' => $rn->receive_note_id,
                    'received_date' => $rn->received_date,
                ])->values()->all(),
            ];
        })->sortBy('customer_name')->values();

        return view('invoices.create_customer_invoice', compact('customersWithInvoices'));
    }

// public function storeCustomerInvoice(Request $request): RedirectResponse{

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

//     // 2. Collect discrepancies
//     $discrepancies = collect();
//     $shortages = [];

//     foreach ($receiveNotes as $rn) {
//         if ($rn->status === 'discrepancy') {
//             $deliveryNote = $rn->deliveryNotes->first();
//             $discrepancies->push([
//                 'rn' => $rn->receive_note_id,
//                 'dn' => $deliveryNote?->delivery_note_id ?? 'N/A',
//             ]);

//             // build shortages for Create PO
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

//     // 3. If any discrepancies found → block invoice and show Create PO button
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


//         /**
//          * ✅ Continue with invoice creation (same logic as before)
//          */
//         $rawLines = collect();
//         foreach ($receiveNotes as $rn) {
//             foreach ($rn->items as $it) {
//                 if (!$it->product) continue;

//                 $p   = $it->product;
//                 $qty = (float) ($it->quantity_received ?? 0);
//                 if ($qty <= 0) continue;

//                 $rawLines->push([
//                     'product_id'     => $p->id,
//                     'product_name'   => $p->appear_name ?: $p->name,
//                     'department_id'  => $p->department_id,
//                     'department_name'=> optional($p->department)->name,
//                     'is_vat'         => (bool) $p->is_vat,
//                     'quantity'       => $qty,
//                     'unit_price'     => (float) $p->selling_price,
//                 ]);
//             }
//         }

//         if ($rawLines->isEmpty()) {
//             return back()->withErrors(['items' => 'No products found to invoice.']);
//         }

//         $aggregated = $rawLines->groupBy('product_id')->map(function ($rows) {
//             $first = $rows->first();
//             return [
//                 'product_id'     => $first['product_id'],
//                 'product_name'   => $first['product_name'],
//                 'department_id'  => $first['department_id'],
//                 'department_name'=> $first['department_name'],
//                 'is_vat'         => $first['is_vat'],
//                 'quantity'       => $rows->sum('quantity'),
//                 'unit_price'     => (float) $first['unit_price'],
//             ];
//         })->values();

//         $groups = $customer->separate_department_invoice
//             ? $aggregated->groupBy(fn($l) => $l['department_id'] ?? 'none')
//             : collect(['all' => $aggregated]);

//         $createdInvoiceIds = [];

//         DB::transaction(function () use ($groups, $customer, $vatRate, $receiveNotes, &$createdInvoiceIds) {
//             foreach ($groups as $deptKey => $lines) {
//                 $invoice = new Invoice([
//                     'invoice_id'     => 'INV-CUST-' . strtoupper(Str::random(6)),
//                     'status'         => 'unpaid',
//                     'notes'          => ($deptKey !== 'all' && $deptKey !== 'none')
//                                         ? 'Department: '.$lines->first()['department_name']
//                                         : null,
//                     'sub_total'      => 0,
//                     'vat_percentage' => $vatRate,
//                     'vat_amount'     => 0,
//                     'total_amount'   => 0,
//                     'amount_paid'    => 0,
//                     'due_date'       => now()->addDays(30),
//                 ]);
//                 $invoice->invoiceable()->associate($customer);
//                 $invoice->save();

//                 $subTotal = 0; $totalVat = 0;
//                 foreach ($lines as $l) {
//                     $lineSub = round($l['quantity'] * $l['unit_price'], 2);
//                     $lineVat = $l['is_vat'] ? round($lineSub * ($vatRate / 100), 2) : 0;

//                     $invoice->items()->create([
//                         'product_id'  => $l['product_id'],
//                         'description' => $l['product_name'],
//                         'quantity'    => $l['quantity'],
//                         'unit_price'  => $l['unit_price'],
//                         'total'       => $lineSub + $lineVat,
//                         'vat_amount'  => $lineVat,
//                     ]);

//                     $subTotal += $lineSub;
//                     $totalVat += $lineVat;
//                 }

//                 $invoice->update([
//                     'sub_total'    => round($subTotal, 2),
//                     'vat_amount'   => round($totalVat, 2),
//                     'total_amount' => round($subTotal + $totalVat, 2),
//                 ]);

//                 $createdInvoiceIds[] = $invoice->invoice_id;
//             }

//             $receiveNotes->each->update(['status' => 'invoiced']);
//         });

//         return redirect()
//             ->route('invoices.index')
//             ->with('success', sprintf(
//                 'Created %d invoice(s) for %s: %s',
//                 count($createdInvoiceIds),
//                 $customer->customer_name,
//                 implode(', ', $createdInvoiceIds)
//             ));
//     }

public function storeCustomerInvoice(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'customer_id'       => ['required', 'exists:customers,id'],
        'receive_note_ids'  => ['required','array','min:1'],
        'receive_note_ids.*'=> ['integer','exists:receive_notes,id'],
    ]);

    $customer = Customer::findOrFail($validated['customer_id']);

    // 1. Load receive notes
    $receiveNotes = ReceiveNote::with(['items.product', 'deliveryNotes'])
        ->whereIn('id', $validated['receive_note_ids'])
        ->get();

    // 2. Handle discrepancies
    $discrepancies = collect();
    $shortages = [];

    foreach ($receiveNotes as $rn) {
        if ($rn->status === 'discrepancy') {
            $deliveryNote = $rn->deliveryNotes->first();
            $discrepancies->push([
                'rn' => $rn->receive_note_id,
                'dn' => $deliveryNote?->delivery_note_id ?? 'N/A',
            ]);

            foreach ($rn->items as $item) {
                if ($item->quantity_received < 0) {
                    $shortages[] = [
                        'product_id' => $item->product_id,
                        'qty'        => $item->quantity_received,
                    ];
                }
            }
        }
    }

    if ($discrepancies->isNotEmpty()) {
        $messages = $discrepancies->map(fn($d) => "Receive Note: {$d['rn']}, Delivery Note: {$d['dn']}")->implode('<br>');

        $createPoUrl = route('purchase-orders.create', [
            'customer_id' => $customer->id,
            'shortages'   => $shortages
        ]);

        $htmlMessage = "
            Cannot generate invoice because some notes have discrepancies:<br>
            {$messages}<br><br>
            <a href='{$createPoUrl}' 
               class='inline-block mt-2 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700'>
               ➕ Create New PO
            </a>
        ";

        return back()->withInput()->with('html_error', $htmlMessage);
    }

    /**
     * ✅ Collect invoice lines with company-wise price and department appear name
     */
    $rawLines = collect();
    foreach ($receiveNotes as $rn) {
        foreach ($rn->items as $it) {
            if (!$it->product) continue;

            $p   = $it->product;
            $qty = (float) ($it->quantity_received ?? 0);
            if ($qty <= 0) continue;

            // 👇 Fetch company-specific product price
            $companyPrice = $p->companyPrices()
                ->where('company_id', $customer->company_id)
                ->first();

            $unitPrice = $companyPrice->selling_price ?? $p->selling_price;
            $costPrice = $companyPrice->cost_price ?? $p->cost_price;

            // 👇 Fetch company-specific department appear name
            $companyDept = $p->companyDepartments()
                ->where('company_id', $customer->company_id)
                ->first();

            $deptName = $companyDept?->appear_name ?? optional($p->department)->name;

            $rawLines->push([
                'product_id'      => $p->id,
                'product_name'    => $p->appear_name ?: $p->name,
                'department_key'  => $deptName,   // 👈 use appear name as grouping key
                'department_name' => $deptName,   // 👈 consistent
                'is_vat'          => (bool) $p->is_vat,
                'quantity'        => $qty,
                'unit_price'      => (float) $unitPrice,
                'cost_price'      => (float) $costPrice,
            ]);
        }
    }

    if ($rawLines->isEmpty()) {
        return back()->withErrors(['items' => 'No products found to invoice.']);
    }

    $aggregated = $rawLines->groupBy('product_id')->map(function ($rows) {
        $first = $rows->first();
        return [
            'product_id'      => $first['product_id'],
            'product_name'    => $first['product_name'],
            'department_key'  => $first['department_key'],
            'department_name' => $first['department_name'],
            'is_vat'          => $first['is_vat'],
            'quantity'        => $rows->sum('quantity'),
            'unit_price'      => (float) $first['unit_price'],
            'cost_price'      => (float) $first['cost_price'],
        ];
    })->values();

    // 👇 Group by appear name instead of raw department_id
    $groups = $customer->separate_department_invoice
        ? $aggregated->groupBy(fn($l) => $l['department_key'] ?? 'none')
        : collect(['all' => $aggregated]);

    $createdInvoiceIds = [];
    $vatRate = (float) (Setting::where('key', 'vat_rate')->value('value') ?? 0);

    DB::transaction(function () use ($groups, $customer, $vatRate, $receiveNotes, &$createdInvoiceIds) {
        foreach ($groups as $deptKey => $lines) {
                    $isVatInvoice = $lines->contains(fn($l) => $l['is_vat']);

            $invoice = new Invoice([
                'invoice_id'     => 'INV-CUST-' . strtoupper(Str::random(6)),
                'status'         => 'unpaid',
                'notes'          => ($deptKey !== 'all' && $deptKey !== 'none')
                                    ? 'Department: '.$lines->first()['department_name']
                                    : null,
                'sub_total'      => 0,
                'vat_percentage' => $vatRate,
                'vat_amount'     => 0,
                'total_amount'   => 0,
                'amount_paid'    => 0,
                'due_date'       => now()->addDays(30),
                'is_vat_invoice' => $isVatInvoice,   // 👈 SAVE VAT FLAG

            ]);
            $invoice->invoiceable()->associate($customer);
            $invoice->save();

            $subTotal = 0; $totalVat = 0;
            foreach ($lines as $l) {
                $lineSub = round($l['quantity'] * $l['unit_price'], 2);
                $lineVat = $l['is_vat'] ? round($lineSub * ($vatRate / 100), 2) : 0;

                $invoice->items()->create([
                    'product_id'  => $l['product_id'],
                    'description' => $l['product_name'],
                    'quantity'    => $l['quantity'],
                    'unit_price'  => $l['unit_price'],
                    'cost_price'  => $l['cost_price'],
                    'total'       => $lineSub + $lineVat,
                    'vat_amount'  => $lineVat,
                ]);

                $subTotal += $lineSub;
                $totalVat += $lineVat;
            }

            $invoice->update([
                'sub_total'    => round($subTotal, 2),
                'vat_amount'   => round($totalVat, 2),
                'total_amount' => round($subTotal + $totalVat, 2),
            ]);

            $createdInvoiceIds[] = $invoice->invoice_id;
        }

        $receiveNotes->each->update(['status' => 'invoiced']);
    });

    return redirect()
        ->route('invoices.index')
        ->with('success', sprintf(
            'Created %d invoice(s) for %s: %s',
            count($createdInvoiceIds),
            $customer->customer_name,
            implode(', ', $createdInvoiceIds)
        ));
}


// public function storeCustomerInvoice(Request $request): RedirectResponse
// {
//     $validated = $request->validate([
//         'customer_id'       => ['required', 'exists:customers,id'],
//         'receive_note_ids'  => ['required','array','min:1'],
//         'receive_note_ids.*'=> ['integer','exists:receive_notes,id'],
//     ]);

//     $customer = Customer::findOrFail($validated['customer_id']);
//     $companyName = strtolower($customer->company_name); // 👈 use for price rules

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

//     /**
//      * ✅ Collect invoice lines with company-wise price and department name
//      */
//     $rawLines = collect();
//     foreach ($receiveNotes as $rn) {
//         foreach ($rn->items as $it) {
//             if (!$it->product) continue;

//             $p   = $it->product;
//             $qty = (float) ($it->quantity_received ?? 0);
//             if ($qty <= 0) continue;

//             // 👇 Fetch company-specific product price
//             $companyPrice = $p->companyPrices()
//                 ->where('company_id', $customer->company_id) // 👈 use company_id
//                 ->first();

//             $unitPrice = $companyPrice->selling_price ?? $p->selling_price;
//             $costPrice = $companyPrice->cost_price ?? $p->cost_price;


//             // 👇 Fetch company-specific department name
//                 $companyDept = $p->companyDepartments()
//                     ->where('company_id', $customer->company_id)
//                     ->first();

//                 $deptName = $companyDept?->appear_name ?? optional($p->department)->name;



//             $rawLines->push([
//                 'product_id'      => $p->id,
//                 'product_name'    => $p->appear_name ?: $p->name,
//                 'department_id'   => $p->department_id,
//                 'department_name' => $deptName,
//                 'is_vat'          => (bool) $p->is_vat,
//                 'quantity'        => $qty,
//                 'unit_price'      => (float) $unitPrice,  // 👈 billing price
//                 'cost_price'      => (float) $costPrice,  // 👈 cost price
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
//             'department_id'   => $first['department_id'],
//             'department_name' => $first['department_name'],
//             'is_vat'          => $first['is_vat'],
//             'quantity'        => $rows->sum('quantity'),
//             'unit_price'      => (float) $first['unit_price'],
//             'cost_price'      => (float) $first['cost_price'],
//         ];
//     })->values();

// $groups = $customer->separate_department_invoice
//     ? $aggregated->groupBy(fn($l) => $l['department_name'] ?? 'none') // 👈 group by appear_name
//     : collect(['all' => $aggregated]);


//     $createdInvoiceIds = [];
// $vatRate = (float) (Setting::where('key', 'vat_rate')->value('value') ?? 0);

//     DB::transaction(function () use ($groups, $customer, $vatRate, $receiveNotes, &$createdInvoiceIds) {
//         foreach ($groups as $deptKey => $lines) {
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
//             ]);
//             $invoice->invoiceable()->associate($customer);
//             $invoice->save();

//             $subTotal = 0; $totalVat = 0;
//             foreach ($lines as $l) {
//                 $lineSub = round($l['quantity'] * $l['unit_price'], 2);
//                 $lineVat = $l['is_vat'] ? round($lineSub * ($vatRate / 100), 2) : 0;

//                 $invoice->items()->create([
//                     'product_id'  => $l['product_id'],
//                     'description' => $l['product_name'],
//                     'quantity'    => $l['quantity'],
//                     'unit_price'  => $l['unit_price'],  // billing
//                     'cost_price'  => $l['cost_price'],  // cost
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

//             $createdInvoiceIds[] = $invoice->invocompanyPricesice_id;
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
    $request->validate(['agent_id' => 'required|exists:agents,id']);

    DB::beginTransaction();
    try {
        $agent = Agent::findOrFail($request->agent_id);

        $itemsToInvoice = DeliveryNoteItem::where('agent_id', $agent->id)
            ->where('quantity_from_agent', '>', 0)
            ->where('agent_invoiced', false)
            ->whereHas('deliveryNote.receiveNotes')
            ->with(['deliveryNote', 'product', 'agent']) // include agent + product
            ->get();

        if ($itemsToInvoice->isEmpty()) {
            return back()->withErrors(['error' => 'No pending items to invoice for this agent.']);
        }

        $totalAmount      = 0;
        $invoiceItemsData = [];

        foreach ($itemsToInvoice as $item) {
            if (!$item->agent) {
                throw new \Exception("Agent not found for delivery item ID {$item->id}");
            }

            // ✅ get pivot record (price_per_case from agent_product_pivot)
            $pivot = $item->agent->products()
                ->where('products.id', $item->product_id) // disambiguated
                ->first();

            if (!$pivot || is_null($pivot->pivot->price_per_case)) {
                throw new \Exception(
                    "No price_per_case found for Agent {$item->agent->id} and Product {$item->product_id}"
                );
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

        // Create the invoice for the agent
        $invoice = $agent->invoices()->create([
            'invoice_id'     => 'INV-AGENT-' . strtoupper(Str::random(6)),
            'due_date'       => now()->addDays(30),
            'sub_total'      => $totalAmount,
            'vat_percentage' => 0,
            'vat_amount'     => 0,
            'total_amount'   => $totalAmount,
            'status'         => 'unpaid',
            'is_vat_invoice' => false,
        ]);

        $invoice->items()->createMany($invoiceItemsData);

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

}