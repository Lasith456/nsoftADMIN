<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReceiveNote;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:report-view');
    }

    public function index(): View
    {
        return view('reports.index');
    }

    // --- Sales & Financial Reports ---

    public function salesReport(Request $request): View
    {
        $query = Invoice::with('invoiceable');

        if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);

        $sales = $query->latest()->paginate(15)->withQueryString();
        return view('reports.sales_report', compact('sales'));
    }

    // --- Entity Performance Reports ---

    public function customerReport(Request $request): View
    {
        $query = Customer::withCount('purchaseOrders')->withSum('invoices', 'total_amount');
        if ($request->filled('search')) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }
        $customers = $query->paginate(15)->withQueryString();
        return view('reports.customer_report', compact('customers'));
    }

    public function supplierReport(Request $request): View
    {
        $query = Supplier::withCount('grns')->withSum('invoices', 'total_amount');
         if ($request->filled('search')) {
            $query->where('supplier_name', 'like', '%' . $request->search . '%');
        }
        $suppliers = $query->paginate(15)->withQueryString();
        return view('reports.supplier_report', compact('suppliers'));
    }

    public function agentReport(Request $request): View
    {
        $query = Agent::withCount('deliveryItems')->withSum('invoices', 'total_amount');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $agents = $query->paginate(15)->withQueryString();
        return view('reports.agent_report', compact('agents'));
    }
    
    // --- Operational Reports ---

    public function purchaseOrderReport(Request $request): View
    {
        $query = PurchaseOrder::with('customer');
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);
        if ($request->filled('start_date')) $query->whereDate('delivery_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('delivery_date', '<=', $request->end_date);
        
        $purchaseOrders = $query->latest()->paginate(15)->withQueryString();
        $customers = Customer::orderBy('customer_name')->get(); // For filter dropdown
        return view('reports.purchase_order_report', compact('purchaseOrders', 'customers'));
    }

    public function deliveryNoteReport(Request $request): View
    {
        $query = DeliveryNote::with('vehicle');
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->whereDate('delivery_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('delivery_date', '<=', $request->end_date);
        $deliveryNotes = $query->latest()->paginate(15)->withQueryString();
        return view('reports.delivery_note_report', compact('deliveryNotes'));
    }

    public function receiveNoteReport(Request $request): View
    {
        $query = ReceiveNote::with('deliveryNotes');
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->whereDate('received_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('received_date', '<=', $request->end_date);
        $receiveNotes = $query->latest()->paginate(15)->withQueryString();
        return view('reports.receive_note_report', compact('receiveNotes'));
    }

    // --- Stock & Connectivity Reports ---
    
    public function stockLevelReport(Request $request): View
    {
        $products = Product::orderBy('name')->paginate(15);
        return view('reports.stock_level_report', compact('products'));
    }

    public function orderFlowReport(Request $request): View
    {
        $query = PurchaseOrder::with(['items', 'deliveryNotes.items', 'deliveryNotes.receiveNotes.items'])
                                ->whereHas('deliveryNotes');
        
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);
        if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);

        $purchaseOrders = $query->latest()->paginate(10)->withQueryString();
        $customers = Customer::orderBy('customer_name')->get();
        return view('reports.order_flow_report', compact('purchaseOrders', 'customers'));
    }
public function outstandingPayments(Request $request)
{
    $type   = $request->get('type', 'all');
    $from   = $request->get('from');
    $to     = $request->get('to');

    $query = Invoice::with(['invoiceable', 'payments']);

    // Filter by type
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

    // Filter by date
    if ($from && $to) {
        $query->whereBetween('created_at', [$from, $to]);
    }

    $invoices = $query->get()->map(function ($invoice) {
        $paid = $invoice->payments->sum('amount');
        $balance = $invoice->total_amount - $paid;

        // Get first receipt (batch id)
        $receiptId = $invoice->payments->first()?->batch_id;

        return [
            'invoice_id'    => $invoice->invoice_id,
            'receipt_id'    => $receiptId,
            'type'          => class_basename($invoice->invoiceable_type),
            'name'          => $invoice->invoiceable?->name ?? 
                               $invoice->invoiceable?->customer_name ?? 
                               $invoice->invoiceable?->supplier_name,
            'date'          => $invoice->created_at->format('Y-m-d'),
            'total'         => $invoice->total_amount,
            'paid'          => $paid,
            'outstanding'   => $balance,
        ];
    })->filter(fn($row) => $row['outstanding'] > 0);

    return view('reports.outstanding', compact('invoices', 'type', 'from', 'to'));
}

}

