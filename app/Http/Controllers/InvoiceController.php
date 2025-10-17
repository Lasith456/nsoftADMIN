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
    $companies = \App\Models\Company::orderBy('company_name')->get();

    // Base query for completed receive notes
    $query = \App\Models\ReceiveNote::where('status', 'completed')
        ->whereHas('deliveryNotes.purchaseOrders.customer')
        ->with('deliveryNotes.purchaseOrders.customer');

    // ✅ Filter by company if provided
    if ($request->filled('company_id')) {
        $query->whereHas('deliveryNotes.purchaseOrders.customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    // ✅ Filter by customer if provided
    if ($request->filled('customer_id')) {
        $query->whereHas('deliveryNotes.purchaseOrders.customer', function ($q) use ($request) {
            $q->where('id', $request->customer_id);
        });
    }

    // ✅ Fetch all relevant receive notes
    $receiveNotes = $query->get();

    // ✅ Group receive notes by customer
    $groupedByCustomer = $receiveNotes->groupBy(function ($rn) {
        return $rn->deliveryNotes->first()?->purchaseOrders->first()?->customer?->id;
    })->filter();

    // ✅ Build customer data with price hierarchy
    $customersWithInvoices = $groupedByCustomer->map(function ($notes, $customerId) {
        $customer = $notes->first()->deliveryNotes->first()->purchaseOrders->first()->customer;
        $companyId = $customer->company_id;

        // --------------------------
        // 1️⃣ Customer-specific prices
        // --------------------------
        $customerPrices = \App\Models\CustomerProductPrice::where('customer_id', $customer->id)
            ->pluck('selling_price', 'product_id'); // product_id => price

        // --------------------------
        // 2️⃣ Company-specific prices
        // --------------------------
        $companyPrices = \App\Models\CompanyProductPrice::where('company_id', $companyId)
            ->pluck('selling_price', 'product_id'); // product_id => price

        // --------------------------
        // 3️⃣ Default product prices
        // --------------------------
        $defaultPrices = \App\Models\Product::pluck('selling_price', 'id');

        // ✅ Combine hierarchy
        $finalPrices = collect();
        foreach ($defaultPrices as $productId => $defaultPrice) {
            $price = $customerPrices[$productId]
                ?? $companyPrices[$productId]
                ?? $defaultPrice;

            $productName = \App\Models\Product::find($productId)?->name ?? 'N/A';
            $finalPrices->push([
                'product_id' => $productId,
                'product_name' => $productName,
                'selling_price' => (float) $price,
                'source' => isset($customerPrices[$productId]) ? 'Customer'
                            : (isset($companyPrices[$productId]) ? 'Company' : 'Default'),
            ]);
        }

        // ✅ Return structured data
        return [
            'id' => $customer->id,
            'customer_name' => $customer->customer_name,
            'customer_id' => $customer->customer_id,
            'company_id' => $companyId,
            'uninvoiced_receive_notes' => $notes->map(fn($rn) => [
                'id' => $rn->id,
                'receive_note_id' => $rn->receive_note_id,
                'received_date' => $rn->received_date,
            ])->values()->all(),
            'product_prices' => $finalPrices->values(), // ✅ unified pricing
        ];
    })->sortBy('customer_name')->values();

    // ✅ Get all customers who have completed receive notes
    $allCustomers = \App\Models\Customer::whereHas('purchaseOrders.deliveryNotes.receiveNotes', function ($q) {
        $q->where('status', 'completed');
    })->orderBy('customer_name')->get();

    return view('invoices.create_customer_invoice', compact('companies', 'allCustomers', 'customersWithInvoices'));
}


public function storeCustomerInvoice(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'customer_id'        => ['required', 'exists:customers,id'],
        'receive_note_ids'   => ['required', 'array', 'min:1'],
        'receive_note_ids.*' => ['integer', 'exists:receive_notes,id'],
        'updated_prices'     => ['array'],
    ]);

    $customer = \App\Models\Customer::with('company')->findOrFail($validated['customer_id']);
    $companyName  = optional($customer->company)->company_name ?? 'Company';
    $customerName = $customer->customer_name ?? 'Customer';

    // ✅ Check if category/department-wise invoices are required
    $separateDept = (bool) $customer->separate_department_invoice;

    // ✅ Get PO start/end dates (based on linked Delivery Notes)
    $poDates = \App\Models\PurchaseOrder::whereHas('deliveryNotes.receiveNotes', function ($q) use ($validated) {
        $q->whereIn('receive_notes.id', $validated['receive_note_ids']);
    })->pluck('delivery_date')->filter();

    $poStart = $poDates->min();
    $poEnd   = $poDates->max();

    // ✅ Load Receive Notes with relations
    $receiveNotes = \App\Models\ReceiveNote::with([
        'items.product.department',
        'items.product.category',
        'deliveryNotes.purchaseOrders'
    ])
        ->whereIn('id', $validated['receive_note_ids'])
        ->get();

    if ($receiveNotes->isEmpty()) {
        return back()->withErrors(['receive_note_ids' => 'No valid receive notes found.']);
    }

    // ✅ Map updated prices (from frontend JSON inputs)
    $updated = collect($request->input('updated_prices', []))
        ->map(function ($json) {
            $data = json_decode($json, true);
            if (is_array($data) && isset($data['product_id'])) {
                $key = $data['product_id'] . '_' . ($data['receive_note_id'] ?? 'NA') . '_' . ($data['purchase_order_id'] ?? 'NA');
                return [$key => $data];
            }
            return [];
        })
        ->collapse();

    $vatRate = (float)(\App\Models\Setting::where('key', 'vat_rate')->value('value') ?? 12);
    $vatLines = collect();
    $nonVatLines = collect();

    // ✅ Collect all product lines
    foreach ($receiveNotes as $rn) {
        foreach ($rn->items as $item) {
            $product = $item->product;
            if (!$product) continue;

            $qty = (float)$item->quantity_received;
            if ($qty <= 0) continue;

            $poId = $item->purchase_order_id ?? 'NA';
            $key  = $product->id . '_' . $rn->id . '_' . $poId;

            $unitPrice = $updated->has($key)
                ? (float)$updated->get($key)['updated_price']
                : (float)($product->selling_price ?? 0);

            $po = optional($rn->deliveryNotes->first()?->purchaseOrders->first());
            $isCategorized = $po?->is_categorized ?? false;
            $companyId = $customer->company_id ?? null;

            // ✅ Determine correct department for company-wise invoice
            if ($isCategorized) {
                $categoryId = $product->category?->id ?? null;
                $departmentId = null;
            } else {
                // 🟢 Check company-specific department mapping
                $mappedDeptId = \App\Models\ProductDepartmentWise::where('product_id', $product->id)
                    ->where('company_id', $companyId)
                    ->value('department_id');

                $departmentId = $mappedDeptId ?: ($product->department?->id ?? null);
                $categoryId = null;
            }

            $line = [
                'product_id'        => $product->id,
                'description'       => $product->name ?? 'Product',
                'quantity'          => $qty,
                'unit_price'        => $unitPrice,
                'is_vat'            => (bool)$product->is_vat,
                'category_id'       => $categoryId,
                'department_id'     => $departmentId,
                'is_categorized'    => $isCategorized,
                'receive_note_id'   => $rn->id,
                'purchase_order_id' => $poId,
            ];

            if ($line['is_vat']) {
                $vatLines->push($line);
            } else {
                $nonVatLines->push($line);
            }
        }
    }

    if ($vatLines->isEmpty() && $nonVatLines->isEmpty()) {
        return back()->withErrors(['items' => 'No products found to invoice.']);
    }

    // ✅ Prepare clean line structures
    $processLines = function ($lines) {
        return collect($lines)->map(function ($l) {
            return [
                'product_id'        => $l['product_id'],
                'description'       => $l['description'],
                'quantity'          => $l['quantity'],
                'unit_price'        => $l['unit_price'],
                'is_vat'            => $l['is_vat'],
                'category_id'       => $l['category_id'],
                'department_id'     => $l['department_id'],
                'is_categorized'    => $l['is_categorized'],
                'receive_note_id'   => $l['receive_note_id'] ?? null,
                'purchase_order_id' => $l['purchase_order_id'] ?? null,
            ];
        })->values();
    };

    $vatGrouped    = $processLines($vatLines);
    $nonVatGrouped = $processLines($nonVatLines);

    // ✅ Department/Category separation
    if ($separateDept) {
        $vatGrouped = $vatGrouped->groupBy(fn($l) => 
            $l['is_categorized'] 
                ? 'category_'.$l['category_id'] 
                : 'department_'.$l['department_id']
        );

        $nonVatGrouped = $nonVatGrouped->groupBy(fn($l) => 
            $l['is_categorized'] 
                ? 'category_'.$l['category_id'] 
                : 'department_'.$l['department_id']
        );
    } else {
        $vatGrouped = collect(['default' => $vatGrouped]);
        $nonVatGrouped = collect(['default' => $nonVatGrouped]);
    }

    DB::beginTransaction();
    try {
        $createdCodes = [];

        // 🧾 Create NON-VAT invoices
        foreach ($nonVatGrouped as $groupKey => $groupLines) {
            if ($groupLines->isEmpty()) continue;

            $invoice = new \App\Models\Invoice([
                'invoice_id'     => $this->generateInvoiceCode(false, $companyName, $customerName),
                'status'         => 'unpaid',
                'sub_total'      => 0,
                'vat_percentage' => $vatRate,
                'vat_amount'     => 0,
                'total_amount'   => 0,
                'amount_paid'    => 0,
                'due_date'       => now()->addDays(30),
                'is_vat_invoice' => false,
                'po_start_date'  => $poStart,
                'po_end_date'    => $poEnd,
                'notes'          => $this->getInvoiceGroupLabel($groupKey),
            ]);
            $invoice->invoiceable()->associate($customer);
            $invoice->save();

            $sub = 0;
            foreach ($groupLines as $line) {
                $lineSub = round($line['quantity'] * $line['unit_price'], 2);
                $invoice->items()->create([
                    'product_id'       => $line['product_id'],
                    'description'      => $line['description'],
                    'quantity'         => $line['quantity'],
                    'unit_price'       => $line['unit_price'],
                    'cost_price'       => 0,
                    'vat_amount'       => 0,
                    'total'            => $lineSub,
                    'receive_note_id'  => $line['receive_note_id'] ?? null,
                    'purchase_order_id'=> $line['purchase_order_id'] ?? null,
                ]);
                $sub += $lineSub;
            }

            $invoice->update([
                'sub_total'    => $sub,
                'total_amount' => $sub,
            ]);
            $invoice->receiveNotes()->attach($receiveNotes->pluck('id'));
            $createdCodes[] = $invoice->invoice_id;
        }

        // 🧾 Create VAT invoices
        foreach ($vatGrouped as $groupKey => $groupLines) {
            if ($groupLines->isEmpty()) continue;

            $invoice = new \App\Models\Invoice([
                'invoice_id'     => $this->generateInvoiceCode(true, $companyName, $customerName),
                'status'         => 'unpaid',
                'sub_total'      => 0,
                'vat_percentage' => $vatRate,
                'vat_amount'     => 0,
                'total_amount'   => 0,
                'amount_paid'    => 0,
                'due_date'       => now()->addDays(30),
                'is_vat_invoice' => true,
                'po_start_date'  => $poStart,
                'po_end_date'    => $poEnd,
                'notes'          => $this->getInvoiceGroupLabel($groupKey),
            ]);
            $invoice->invoiceable()->associate($customer);
            $invoice->save();

            $sub = 0; 
            $vat = 0;
            foreach ($groupLines as $line) {
                $lineSub = round($line['quantity'] * $line['unit_price'], 2);
                $lineVat = round($lineSub * ($vatRate / 100), 2);
                $invoice->items()->create([
                    'product_id'       => $line['product_id'],
                    'description'      => $line['description'],
                    'quantity'         => $line['quantity'],
                    'unit_price'       => $line['unit_price'],
                    'cost_price'       => 0,
                    'vat_amount'       => $lineVat,
                    'total'            => $lineSub + $lineVat,
                    'receive_note_id'  => $line['receive_note_id'] ?? null,
                    'purchase_order_id'=> $line['purchase_order_id'] ?? null,
                ]);
                $sub += $lineSub;
                $vat += $lineVat;
            }

            $invoice->update([
                'sub_total'    => $sub,
                'vat_amount'   => $vat,
                'total_amount' => $sub + $vat,
            ]);
            $invoice->receiveNotes()->attach($receiveNotes->pluck('id'));
            $createdCodes[] = $invoice->invoice_id;
        }

        // ✅ Mark RN as invoiced
        $receiveNotes->each->update(['status' => 'invoiced']);

        DB::commit();

        return redirect()
            ->route('invoices.index')
            ->with('success', '✅ Created invoice(s): '.implode(', ', $createdCodes));

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Failed to create invoices: '.$e->getMessage()]);
    }
}


private function getInvoiceGroupLabel($key)
{
    if (str_starts_with($key, 'category_')) {
        $catId = (int)str_replace('category_', '', $key);
        $catName = \App\Models\Category::find($catId)?->name ?? 'Category';
        return "Category: {$catName}";
    }

    if (str_starts_with($key, 'department_')) {
        $deptId = (int)str_replace('department_', '', $key);
        $deptName = \App\Models\Department::find($deptId)?->name ?? 'Department';
        return "Department: {$deptName}";
    }

    return null;
}



    public function createAgentInvoice(): View
    {
        $agents = Agent::whereHas('deliveryItems', function ($query) {
            $query->where('agent_invoiced', false);
        })->with(['deliveryItems' => function ($query) {
            $query->where('agent_invoiced', false)
                ->with(['deliveryNote', 'product']);
        }])->get();
        foreach ($agents as $agent) {
            foreach ($agent->deliveryItems as $item) {
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
            $itemsToInvoice = DeliveryNoteItem::where('agent_id', $agent->id)
                ->where('quantity_from_agent', '>', 0)
                ->where('agent_invoiced', false)
                ->whereIn('id', $validated['delivery_item_ids'])
                ->with(['deliveryNote.receiveNotes', 'product', 'agent'])
                ->get();

            if ($itemsToInvoice->isEmpty()) {
                return back()->withErrors(['error' => 'No pending items found for the selected delivery notes.']);
            }
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

//Bill Show NAVY
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
        $invoice->amount_in_words = $this->convertToWords($invoice->total_amount);

        return view('invoices.printopt3', compact('invoice'));
    }


    private function convertToWords($number)
    {
        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        return ucfirst($f->format($number));
    }
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $totalPaid = $invoice->payments()->sum('amount');

        if ($invoice->status === 'paid' || $totalPaid > 0) {
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
            if (method_exists($invoice, 'receiveNotes')) {
                $rnIds = $invoice->receiveNotes()->pluck('receive_notes.id'); 

                if ($rnIds->isNotEmpty()) {
                    \App\Models\ReceiveNote::whereIn('id', $rnIds)
                        ->update(['status' => 'completed']); 
                }

                $invoice->receiveNotes()->detach();
            }
            $invoice->items()->delete();
            $invoice->payments()->delete();
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

        $notes = \App\Models\ReceiveNote::with([
            'items.product',
            'deliveryNotes.purchaseOrders'  // kept (don’t remove)
        ])
        ->where('status', 'completed')
        ->whereIn('id', $request->receive_note_ids)
        ->get();

        $products = $notes->flatMap(function ($rn) {
            // Keep your old PO logic — now secondary only
            $poIds = $rn->deliveryNotes
                ->flatMap(fn($dn) => $dn->purchaseOrders)
                ->pluck('id')
                ->unique()
                ->values();

            $poCodes = $rn->deliveryNotes
                ->flatMap(fn($dn) => $dn->purchaseOrders)
                ->pluck('po_number')
                ->unique()
                ->values();

            // ✅ MAIN: use PO ID from ReceiveNoteItem
            return $rn->items->map(function ($item) use ($rn, $poIds, $poCodes) {
                return [
                    'product_id'        => $item->product->id ?? null,
                    'product_name'      => $item->product->name ?? 'Unknown Product',
                    'quantity_received' => $item->quantity_received ?? 0,
                    'default_price'     => $item->product->selling_price ?? 0,
                    'receive_note_id'   => $rn->id,
                    'purchase_order_id' => $item->purchase_order_id ?? null, // ✅ direct PO relationship
                ];
            });
        })
        ->filter(fn($p) => !is_null($p['product_id']))
        ->values();

        // ✅ Optional debug log
        \Log::info('✅ Products fetched from Receive Notes:', $products->toArray());

        return response()->json(array_values($products->toArray())); // ✅ pure JSON array

    } catch (\Throwable $e) {
        \Log::error('❌ Failed to fetch products:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'error'   => 'Failed to fetch products',
            'message' => $e->getMessage(),
        ], 500);
    }
}



private function generateInvoiceCode(bool $isVat, string $companyName, string $customerName): string
{
    // Extract company initials (1 or 2 letters)
    $companyWords = preg_split('/\s+/', trim($companyName));
    $companyInitial = strtoupper(substr($companyWords[0], 0, 1));

    if (count($companyWords) > 1) {
        $companyInitial .= strtoupper(substr($companyWords[1], 0, 1));
    }

    // Extract customer initial (always first letter)
    $customerInitial = strtoupper(substr($customerName, 0, 1));

    // VAT suffix
    $suffix = $isVat ? '-V' : '';

    // Get last invoice
    $last = \App\Models\Invoice::where('is_vat_invoice', $isVat)
        ->orderBy('id', 'desc')
        ->first();

    $next = $last ? intval(preg_replace('/\D/', '', $last->invoice_id)) + 1 : 1;
    $formatted = str_pad($next, 4, '0', STR_PAD_LEFT);

    // Example: INV-0005-IGC
    return "INV-{$formatted}-{$companyInitial}{$customerInitial}{$suffix}";
}



// In InvoiceController (private method)
private function nextInvoiceCode(bool $isVat, string $companyName, string $customerName): string
{
    $c = strtoupper(substr($companyName ?: 'C', 0, 1));
    $u = strtoupper(substr($customerName ?: 'C', 0, 1));
    $suffix = $isVat ? '-V' : '';

    // Find last code for this type (by suffix) and increment the 4-digit number
    $like = "INV-%-$c$u$suffix";

    $last = \App\Models\Invoice::where('invoice_id', 'like', $like)
        ->orderBy('id', 'desc')
        ->first();

    $next = 1;
    if ($last && preg_match('/INV-(\d{4})-/', $last->invoice_id, $m)) {
        $next = ((int)$m[1]) + 1;
    }

    return 'INV-'.str_pad($next, 4, '0', STR_PAD_LEFT)."-{$c}{$u}{$suffix}";
}



}