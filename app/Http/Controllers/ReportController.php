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
use App\Models\Department;
use App\Models\Payment;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // filters...
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            switch ($request->type) {
                case 'customer':
                    $query->where('invoiceable_type', \App\Models\Customer::class);
                    break;
                case 'agent':
                    $query->where('invoiceable_type', \App\Models\Agent::class);
                    break;
                case 'supplier':
                    $query->where('invoiceable_type', \App\Models\Supplier::class);
                    break;
            }
        }

        if ($request->filled('customer_id')) {
            $query->where('invoiceable_type', \App\Models\Customer::class)
                ->where('invoiceable_id', $request->customer_id);
        }

        if ($request->filled('agent_id')) {
            $query->where('invoiceable_type', \App\Models\Agent::class)
                ->where('invoiceable_id', $request->agent_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('invoiceable_type', \App\Models\Supplier::class)
                ->where('invoiceable_id', $request->supplier_id);
        }

        $sales = $query->latest()->paginate(15)->withQueryString();
        $type = $request->get('type', 'all');

        $customers = \App\Models\Customer::orderBy('customer_name')->get();
        $agents = \App\Models\Agent::orderBy('name')->get();
        $suppliers = \App\Models\Supplier::orderBy('supplier_name')->get();

        return view('reports.sales_report', compact('sales', 'type', 'customers', 'agents', 'suppliers'));
    }



    // --- Entity Performance Reports ---

    public function customerReport(Request $request): View
    {
        $query = Customer::withCount('purchaseOrders')
            ->withSum(['invoices' => function ($q) use ($request) {
                // Apply date filters to invoices
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        // Search filter
        if ($request->filled('search')) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }

        $customers = $query->paginate(15)->withQueryString();

        // Calculate overall total for all filtered customers
        $totalInvoices = $query->get()->sum('invoices_sum_total_amount');

        return view('reports.customer_report', compact('customers', 'totalInvoices'));
    }


    public function supplierReport(Request $request): View
    {
        $query = Supplier::withCount('grns')
            ->withSum(['invoices' => function ($q) use ($request) {
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        if ($request->filled('search')) {
            $query->where('supplier_name', 'like', '%' . $request->search . '%');
        }

        $suppliers = $query->paginate(15)->withQueryString();
        $totalInvoices = $query->get()->sum('invoices_sum_total_amount');

        return view('reports.supplier_report', compact('suppliers', 'totalInvoices'));
    }


    public function agentReport(Request $request): View
    {
        $query = Agent::withCount('deliveryItems')
            ->withSum(['invoices' => function ($q) use ($request) {
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $agents = $query->paginate(15)->withQueryString();
        $totalInvoices = $query->get()->sum('invoices_sum_total_amount');

        return view('reports.agent_report', compact('agents', 'totalInvoices'));
    }

    
    // --- Operational Reports ---

   public function purchaseOrderReport(Request $request): View
{
    $query = PurchaseOrder::with(['customer.company', 'items.product']);

    // ✅ Filter by Company through related Customer
    if ($request->filled('company_id')) {
        $query->whereHas('customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    // ✅ Other filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('customer_id')) {
        $query->where('customer_id', $request->customer_id);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('delivery_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('delivery_date', '<=', $request->end_date);
    }

    $purchaseOrders = $query->latest()->paginate(15)->withQueryString();

    // ✅ Load Customers and Companies for filter dropdowns
    $customers = Customer::orderBy('customer_name')->get();
    $companies = Company::orderBy('company_name')->get();

    return view('reports.purchase_order_report', compact('purchaseOrders', 'customers', 'companies'));
}


public function deliveryNoteReport(Request $request): View
{
    $query = DeliveryNote::with(['vehicle', 'purchaseOrders.customer.company']);

    if ($request->filled('company_id')) {
        $query->whereHas('purchaseOrders.customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('delivery_date', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('delivery_date', '<=', $request->end_date);
    }

    $deliveryNotes = $query->latest()->paginate(15)->withQueryString();

    // 👇 add this line to load all companies for filter dropdown
    $companies = \App\Models\Company::orderBy('company_name')->get();

    return view('reports.delivery_note_report', compact('deliveryNotes', 'companies'));
}



public function receiveNoteReport(Request $request): View
{
    // ✅ Eager load all nested relationships for reporting
    $query = ReceiveNote::with([
        'deliveryNotes.purchaseOrders.customer.company'
    ]);

    // ✅ Filter by company (nested)
    if ($request->filled('company_id')) {
        $query->whereHas('deliveryNotes.purchaseOrders.customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    // ✅ Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // ✅ Filter by start date
    if ($request->filled('start_date')) {
        $query->whereDate('received_date', '>=', $request->start_date);
    }

    // ✅ Filter by end date
    if ($request->filled('end_date')) {
        $query->whereDate('received_date', '<=', $request->end_date);
    }

    // ✅ Always include receive_note_id and prevent hidden field issues
    $receiveNotes = $query
        ->select('id', 'receive_note_id', 'received_date', 'status')
        ->latest()
        ->paginate(15)
        ->withQueryString();

    // ✅ Load companies for the dropdown filter in the view
    $companies = \App\Models\Company::orderBy('company_name')->get();

    return view('reports.receive_note_report', compact('receiveNotes', 'companies'));
}



    // --- Stock & Connectivity Reports ---
    
    public function stockLevelReport(Request $request): View
    {
        $query = Product::query();

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $departments = \App\Models\Department::orderBy('name')->get();

        return view('reports.stock_level_report', compact('products', 'departments'));
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
            'inv_id'    => $invoice->id,
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
    public function exportCustomerExcel(Request $request)
    {
        $query = Customer::withCount('purchaseOrders')
            ->withSum(['invoices' => function ($q) use ($request) {
                // Apply date filters to invoices
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        // Search filter
        if ($request->filled('search')) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }

        $customers = $query->get();
        $totalInvoices = $customers->sum('invoices_sum_total_amount'); // overall total

        return Excel::download(new class($customers, $totalInvoices) implements 
            \Maatwebsite\Excel\Concerns\FromCollection, 
            \Maatwebsite\Excel\Concerns\WithHeadings 
        {
            private $customers;
            private $totalInvoices;

            public function __construct($customers, $totalInvoices) {
                $this->customers = $customers;
                $this->totalInvoices = $totalInvoices;
            }

            public function collection() {
                $rows = $this->customers->map(fn($c) => [
                    'Customer Name' => $c->customer_name,
                    'Total POs'     => $c->purchase_orders_count,
                    'Total Invoiced'=> number_format($c->invoices_sum_total_amount, 2),
                ]);

                // Add grand total row at the end
                $rows->push([
                    'Customer Name' => 'TOTAL',
                    'Total POs'     => '',
                    'Total Invoiced'=> number_format($this->totalInvoices, 2),
                ]);

                return $rows;
            }

            public function headings(): array {
                return ['Customer Name', 'Total POs', 'Total Invoiced (LKR)'];
            }
        }, 'customer_report.xlsx');
    }


    public function exportCustomerPdf(Request $request)
    {
        $query = Customer::withCount('purchaseOrders')
            ->withSum(['invoices' => function ($q) use ($request) {
                // Apply date filters
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        if ($request->filled('search')) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }

        $customers = $query->get();
        $totalInvoices = $customers->sum('invoices_sum_total_amount'); // overall total

        $pdf = Pdf::loadView('reports.pdf.customer_report', compact('customers', 'totalInvoices'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('customer_report.pdf');
    }


    public function exportSalesExcel(Request $request)
    {
        $query = Invoice::with('invoiceable');

        // Date filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Type filter
        if ($request->filled('type') && $request->type !== 'all') {
            switch ($request->type) {
                case 'customer':
                    $query->where('invoiceable_type', \App\Models\Customer::class);
                    break;
                case 'agent':
                    $query->where('invoiceable_type', \App\Models\Agent::class);
                    break;
                case 'supplier':
                    $query->where('invoiceable_type', \App\Models\Supplier::class);
                    break;
            }
        }

        // Customer filter (only for customer invoices)
        if ($request->filled('customer_id')) {
            $query->where('invoiceable_type', \App\Models\Customer::class)
                ->where('invoiceable_id', $request->customer_id);
        }

        $sales = $query->latest()->get();

        return Excel::download(new class($sales) implements \Maatwebsite\Excel\Concerns\FromCollection,
                                                        \Maatwebsite\Excel\Concerns\WithHeadings {
            private $sales;
            public function __construct($sales) { $this->sales = $sales; }

            public function collection() {
                return $this->sales->map(fn($s) => [
                    'Date'       => $s->created_at->format('Y-m-d'),
                    'Invoice ID' => $s->invoice_id,
                    'Billed To'  => $s->invoiceable->customer_name
                                ?? $s->invoiceable->supplier_name
                                ?? $s->invoiceable->name
                                ?? 'N/A',
                    'Amount'     => number_format($s->total_amount, 2),
                ]);
            }

            public function headings(): array {
                return ['Date', 'Invoice ID', 'Billed To', 'Amount (LKR)'];
            }
        }, 'sales_report.xlsx');
    }



    public function exportSalesPdf(Request $request)
    {
        $query = Invoice::with('invoiceable');

        // Date filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Type filter
        if ($request->filled('type') && $request->type !== 'all') {
            switch ($request->type) {
                case 'customer':
                    $query->where('invoiceable_type', \App\Models\Customer::class);
                    break;
                case 'agent':
                    $query->where('invoiceable_type', \App\Models\Agent::class);
                    break;
                case 'supplier':
                    $query->where('invoiceable_type', \App\Models\Supplier::class);
                    break;
            }
        }

        $customer = null;
        if ($request->filled('customer_id')) {
            $query->where('invoiceable_type', \App\Models\Customer::class)
                ->where('invoiceable_id', $request->customer_id);
            $customer = \App\Models\Customer::find($request->customer_id);
        }

        $sales = $query->latest()->get();
        $total = $sales->sum('total_amount');
        $type  = $request->get('type', 'all');

        $pdf = Pdf::loadView('reports.pdf.sales_report', compact('sales', 'total', 'type', 'customer'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('sales_report.pdf');
    }
    public function exportStockLevelExcel(Request $request)
    {
        $query = Product::query();

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $products = $query->orderBy('name')->get();

        return Excel::download(new class($products) implements \Maatwebsite\Excel\Concerns\FromCollection,
                                                        \Maatwebsite\Excel\Concerns\WithHeadings {
            private $products;
            public function __construct($products) { $this->products = $products; }

            public function collection() {
                return $this->products->map(fn($p) => [
                    'Product Name'    => $p->name,
                    'Clear Stock'     => $p->clear_stock_quantity,
                    'Non-Clear Stock' => $p->non_clear_stock_quantity,
                    'Total Stock'     => $p->total_stock,
                ]);
            }

            public function headings(): array {
                return ['Product Name', 'Clear Stock', 'Non-Clear Stock', 'Total Stock'];
            }
        }, 'stock_level_report.xlsx');
    }

    public function exportStockLevelPdf(Request $request)
    {
        $query = Product::query();

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $products = $query->orderBy('name')->get();
        $department = null;
        if ($request->filled('department_id')) {
            $department = \App\Models\Department::find($request->department_id);
        }

        $pdf = Pdf::loadView('reports.pdf.stock_level_report', compact('products', 'department'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('stock_level_report.pdf');
    }
    public function exportSupplierExcel(Request $request)
    {
        $query = Supplier::withCount('grns')
            ->withSum(['invoices' => function ($q) use ($request) {
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        if ($request->filled('search')) {
            $query->where('supplier_name', 'like', '%' . $request->search . '%');
        }

        $suppliers = $query->get();
        $totalInvoices = $suppliers->sum('invoices_sum_total_amount');

        return Excel::download(new class($suppliers, $totalInvoices) implements 
            \Maatwebsite\Excel\Concerns\FromCollection, 
            \Maatwebsite\Excel\Concerns\WithHeadings 
        {
            private $suppliers;
            private $totalInvoices;
            public function __construct($suppliers, $totalInvoices) {
                $this->suppliers = $suppliers;
                $this->totalInvoices = $totalInvoices;
            }
            public function collection() {
                $rows = $this->suppliers->map(fn($s) => [
                    'Supplier Name'   => $s->supplier_name,
                    'Total GRNs'      => $s->grns_count,
                    'Total Invoiced'  => number_format($s->invoices_sum_total_amount, 2),
                ]);
                $rows->push([
                    'Supplier Name'   => 'TOTAL',
                    'Total GRNs'      => '',
                    'Total Invoiced'  => number_format($this->totalInvoices, 2),
                ]);
                return $rows;
            }
            public function headings(): array {
                return ['Supplier Name', 'Total GRNs', 'Total Invoiced (LKR)'];
            }
        }, 'supplier_report.xlsx');
    }

    public function exportSupplierPdf(Request $request)
    {
        $query = Supplier::withCount('grns')
            ->withSum(['invoices' => function ($q) use ($request) {
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        if ($request->filled('search')) {
            $query->where('supplier_name', 'like', '%' . $request->search . '%');
        }

        $suppliers = $query->get();
        $totalInvoices = $suppliers->sum('invoices_sum_total_amount');

        $pdf = Pdf::loadView('reports.pdf.supplier_report', compact('suppliers', 'totalInvoices'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('supplier_report.pdf');
    }
    public function exportAgentExcel(Request $request)
    {
        $query = Agent::withCount('deliveryItems')
            ->withSum(['invoices' => function ($q) use ($request) {
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $agents = $query->get();
        $totalInvoices = $agents->sum('invoices_sum_total_amount');

        return Excel::download(new class($agents, $totalInvoices) implements 
            \Maatwebsite\Excel\Concerns\FromCollection, 
            \Maatwebsite\Excel\Concerns\WithHeadings 
        {
            private $agents;
            private $totalInvoices;
            public function __construct($agents, $totalInvoices) {
                $this->agents = $agents;
                $this->totalInvoices = $totalInvoices;
            }
            public function collection() {
                $rows = $this->agents->map(fn($a) => [
                    'Agent Name'       => $a->name,
                    'Deliveries Count' => $a->delivery_items_count,
                    'Total Payout'     => number_format($a->invoices_sum_total_amount, 2),
                ]);
                $rows->push([
                    'Agent Name'       => 'TOTAL',
                    'Deliveries Count' => '',
                    'Total Payout'     => number_format($this->totalInvoices, 2),
                ]);
                return $rows;
            }
            public function headings(): array {
                return ['Agent Name', 'Deliveries Fulfilled', 'Total Payout (LKR)'];
            }
        }, 'agent_report.xlsx');
    }
    public function exportAgentPdf(Request $request)
    {
        $query = Agent::withCount('deliveryItems')
            ->withSum(['invoices' => function ($q) use ($request) {
                if ($request->filled('start_date')) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'total_amount');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $agents = $query->get();
        $totalInvoices = $agents->sum('invoices_sum_total_amount');

        $pdf = Pdf::loadView('reports.pdf.agent_report', compact('agents', 'totalInvoices'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('agent_report.pdf');
    }

    public function exportDeliveryNotesExcel(Request $request)
{
    $query = DeliveryNote::with(['vehicle', 'purchaseOrders.customer.company']);

    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('delivery_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('delivery_date', '<=', $request->end_date);
    }
    if ($request->filled('company_id')) {
        $query->whereHas('purchaseOrders.customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    $deliveryNotes = $query->get();

    return Excel::download(new class($deliveryNotes) implements 
        \Maatwebsite\Excel\Concerns\FromCollection, 
        \Maatwebsite\Excel\Concerns\WithHeadings 
    {
        private $deliveryNotes;
        public function __construct($deliveryNotes)
        {
            $this->deliveryNotes = $deliveryNotes;
        }

        public function collection()
        {
            return $this->deliveryNotes->map(fn($dn) => [
                'DN ID'         => $dn->delivery_note_id,
                'Company'       => $dn->company->company_name ?? 'N/A',
                'Vehicle'       => $dn->vehicle->vehicle_no ?? 'N/A',
                'Driver Name'   => $dn->driver_name ?? 'N/A',
                'Contact No'    => $dn->driver_mobile ?? 'N/A',
                'Delivery Date' => $dn->delivery_date?->format('Y-m-d') ?? 'N/A',
                'Status'        => ucfirst($dn->status),
            ]);
        }

        public function headings(): array
        {
            return ['DN ID', 'Company', 'Vehicle', 'Driver Name', 'Contact No', 'Delivery Date', 'Status'];
        }
    }, 'delivery_note_report.xlsx');
}


public function exportDeliveryNotesPdf(Request $request)
{
    $query = DeliveryNote::with(['vehicle', 'purchaseOrders.customer.company']);

    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('delivery_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('delivery_date', '<=', $request->end_date);
    }
    if ($request->filled('company_id')) {
        $query->whereHas('purchaseOrders.customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    $deliveryNotes = $query->get();

    $pdf = Pdf::loadView('reports.pdf.delivery_note_report', compact('deliveryNotes'))
        ->setPaper('a4', 'landscape');

    return $pdf->download('delivery_note_report.pdf');
}

   public function exportReceiveNotesExcel(Request $request)
{
    $query = ReceiveNote::with(['deliveryNotes.purchaseOrders.customer.company']);

    // ✅ Apply filters
    if ($request->filled('company_id')) {
        $query->whereHas('deliveryNotes.purchaseOrders.customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('received_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('received_date', '<=', $request->end_date);
    }

    $receiveNotes = $query->get();

    return Excel::download(new class($receiveNotes) implements 
        \Maatwebsite\Excel\Concerns\FromCollection, 
        \Maatwebsite\Excel\Concerns\WithHeadings 
    {
        private $receiveNotes;
        public function __construct($receiveNotes) { $this->receiveNotes = $receiveNotes; }

        public function collection() {
            return $this->receiveNotes->map(function ($rn) {
                // ✅ Collect data safely
                $deliveryNotes = $rn->deliveryNotes->pluck('delivery_note_id')->implode(', ') ?: 'N/A';

                $poList = $rn->deliveryNotes
                    ->flatMap(function ($dn) {
                        return $dn->purchaseOrders->pluck('purchase_order_id')
                            ->merge($dn->purchaseOrders->pluck('po_id'));
                    })
                    ->filter()
                    ->unique()
                    ->implode(', ') ?: 'N/A';

                $companyName = $rn->deliveryNotes
                    ->flatMap(fn($dn) => $dn->purchaseOrders->pluck('customer.company.company_name'))
                    ->filter()
                    ->unique()
                    ->implode(', ') ?: 'N/A';

                return [
                    'RN ID'          => $rn->receive_note_id,
                    'Associated DNs' => $deliveryNotes,
                    'Assigned POs'   => $poList,
                    'Company'        => $companyName,
                    'Received Date'  => $rn->received_date->format('Y-m-d'),
                    'Status'         => ucfirst($rn->status),
                ];
            });
        }

        public function headings(): array {
            return ['RN ID', 'Associated DNs', 'Assigned POs', 'Company', 'Received Date', 'Status'];
        }
    }, 'receive_note_report.xlsx');
}

public function exportReceiveNotesPdf(Request $request)
{
    $query = ReceiveNote::with(['deliveryNotes.purchaseOrders.customer.company']);

    // ✅ Apply filters
    if ($request->filled('company_id')) {
        $query->whereHas('deliveryNotes.purchaseOrders.customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('received_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('received_date', '<=', $request->end_date);
    }

    $receiveNotes = $query->get();

    // ✅ Export with updated PDF layout (includes Company)
    $pdf = Pdf::loadView('reports.pdf.receive_note_report', compact('receiveNotes'))
            ->setPaper('a4', 'landscape');

    return $pdf->download('receive_note_report.pdf');
}

   public function exportPurchaseOrdersExcel(Request $request)
{
    $query = PurchaseOrder::with(['customer.company', 'items.product']);

    // ✅ Filter by company through related customer
    if ($request->filled('company_id')) {
        $query->whereHas('customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    // ✅ Other filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('customer_id')) {
        $query->where('customer_id', $request->customer_id);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('delivery_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('delivery_date', '<=', $request->end_date);
    }

    $purchaseOrders = $query->get();

    return Excel::download(new class($purchaseOrders) implements 
        \Maatwebsite\Excel\Concerns\FromCollection, 
        \Maatwebsite\Excel\Concerns\WithHeadings 
    {
        private $purchaseOrders;
        public function __construct($purchaseOrders) { $this->purchaseOrders = $purchaseOrders; }

        public function collection() {
            return $this->purchaseOrders->map(fn($po) => [
                'PO ID'          => $po->po_id,
                'Company'        => $po->customer->company->company_name ?? 'N/A',
                'Customer'       => $po->customer->customer_name ?? 'N/A',
                'Delivery Date'  => $po->delivery_date?->format('Y-m-d') ?? 'N/A',
                'Products'       => $po->items->pluck('product.name')->implode(', ') ?: 'N/A',
                'Status'         => ucfirst($po->status),
            ]);
        }

        public function headings(): array {
            return ['PO ID', 'Company', 'Customer', 'Delivery Date', 'Products', 'Status'];
        }
    }, 'purchase_order_report.xlsx');
}

public function exportPurchaseOrdersPdf(Request $request)
{
    $query = PurchaseOrder::with(['customer.company', 'items.product']);

    // ✅ Apply filters
    if ($request->filled('company_id')) {
        $query->whereHas('customer', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('customer_id')) {
        $query->where('customer_id', $request->customer_id);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('delivery_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('delivery_date', '<=', $request->end_date);
    }

    $purchaseOrders = $query->get();

    $pdf = Pdf::loadView('reports.pdf.purchase_order_report', compact('purchaseOrders'))
            ->setPaper('a4', 'landscape');

    return $pdf->download('purchase_order_report.pdf');
}

    public function exportOrderFlowExcel(Request $request)
    {
        $query = PurchaseOrder::with(['customer', 'items', 'deliveryNotes.items', 'deliveryNotes.receiveNotes.items']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $purchaseOrders = $query->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new class($purchaseOrders) implements 
            \Maatwebsite\Excel\Concerns\FromCollection, 
            \Maatwebsite\Excel\Concerns\WithHeadings 
        {
            private $purchaseOrders;
            public function __construct($purchaseOrders) { $this->purchaseOrders = $purchaseOrders; }

            public function collection() {
                $rows = collect();
                foreach ($this->purchaseOrders as $po) {
                    foreach ($po->items->groupBy('product_id') as $productId => $poItems) {
                        $productName = $poItems->first()->product_name;
                        $poQty = $poItems->sum('quantity');
                        $dnQty = $po->deliveryNotes->flatMap->items->where('product_id', $productId)->sum('quantity_requested');
                        $rnQty = $po->deliveryNotes->flatMap->receiveNotes->flatMap->items->where('product_id', $productId)->sum('quantity_received');
                        $discrepancy = $poQty - $rnQty;

                        $rows->push([
                            'PO ID'        => $po->po_id,
                            'Customer'     => $po->customer->customer_name ?? 'N/A',
                            'Product'      => $productName,
                            'Ordered'      => $poQty,
                            'Delivered'    => $dnQty,
                            'Received'     => $rnQty,
                            'Discrepancy'  => $discrepancy,
                        ]);
                    }
                }
                return $rows;
            }

            public function headings(): array {
                return ['PO ID', 'Customer', 'Product', 'Ordered (PO)', 'Delivered (DN)', 'Received (RN)', 'Discrepancy'];
            }
        }, 'order_flow_report.xlsx');
    }
    public function exportOrderFlowPdf(Request $request)
    {
        $query = PurchaseOrder::with(['customer', 'items', 'deliveryNotes.items', 'deliveryNotes.receiveNotes.items']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $purchaseOrders = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.order_flow_report', compact('purchaseOrders'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('order_flow_report.pdf');
    }

    public function exportOutstandingExcel(Request $request)
    {
        $type   = $request->get('type', 'all');
        $from   = $request->get('from');
        $to     = $request->get('to');

        $query = Invoice::with(['invoiceable', 'payments']);

        switch ($type) {
            case 'customer': $query->where('invoiceable_type', Customer::class); break;
            case 'supplier': $query->where('invoiceable_type', Supplier::class); break;
            case 'agent':    $query->where('invoiceable_type', Agent::class); break;
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $invoices = $query->get()->map(function ($invoice) {
            $paid = $invoice->payments->sum('amount');
            $balance = $invoice->total_amount - $paid;
            return [
                'Invoice ID'   => $invoice->invoice_id,
                'Receipt ID'   => $invoice->payments->first()?->batch_id ?? '—',
                'Type'         => class_basename($invoice->invoiceable_type),
                'Name'         => $invoice->invoiceable?->name ?? 
                                $invoice->invoiceable?->customer_name ?? 
                                $invoice->invoiceable?->supplier_name,
                'Date'         => $invoice->created_at->format('Y-m-d'),
                'Total'        => $invoice->total_amount,
                'Paid'         => $paid,
                'Outstanding'  => $balance,
            ];
        })->filter(fn($row) => $row['Outstanding'] > 0);

        // Totals
        $totalSum       = $invoices->sum('Total');
        $paidSum        = $invoices->sum('Paid');
        $outstandingSum = $invoices->sum('Outstanding');

        return \Maatwebsite\Excel\Facades\Excel::download(new class($invoices, $totalSum, $paidSum, $outstandingSum) implements 
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings
        {
            private $invoices, $totalSum, $paidSum, $outstandingSum;
            public function __construct($invoices, $totalSum, $paidSum, $outstandingSum) {
                $this->invoices = $invoices;
                $this->totalSum = $totalSum;
                $this->paidSum = $paidSum;
                $this->outstandingSum = $outstandingSum;
            }

            public function collection() {
                $rows = collect($this->invoices);
                // Add total row
                $rows->push([
                    'Invoice ID'  => 'TOTAL',
                    'Receipt ID'  => '',
                    'Type'        => '',
                    'Name'        => '',
                    'Date'        => '',
                    'Total'       => number_format($this->totalSum, 2),
                    'Paid'        => number_format($this->paidSum, 2),
                    'Outstanding' => number_format($this->outstandingSum, 2),
                ]);
                return $rows;
            }

            public function headings(): array {
                return ['Invoice ID', 'Receipt ID', 'Type', 'Name', 'Date', 'Total', 'Paid', 'Outstanding'];
            }
        }, 'outstanding_payments.xlsx');
    }


    public function exportOutstandingPdf(Request $request)
    {
        $type   = $request->get('type', 'all');
        $from   = $request->get('from');
        $to     = $request->get('to');

        $query = Invoice::with(['invoiceable', 'payments']);

        switch ($type) {
            case 'customer': $query->where('invoiceable_type', Customer::class); break;
            case 'supplier': $query->where('invoiceable_type', Supplier::class); break;
            case 'agent':    $query->where('invoiceable_type', Agent::class); break;
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $invoices = $query->get()->map(function ($invoice) {
            $paid = $invoice->payments->sum('amount');
            $balance = $invoice->total_amount - $paid;
            return [
                'invoice_id'   => $invoice->invoice_id,
                'receipt_id'   => $invoice->payments->first()?->batch_id ?? '—',
                'type'         => class_basename($invoice->invoiceable_type),
                'name'         => $invoice->invoiceable?->name ?? 
                                $invoice->invoiceable?->customer_name ?? 
                                $invoice->invoiceable?->supplier_name,
                'date'         => $invoice->created_at->format('Y-m-d'),
                'total'        => $invoice->total_amount,
                'paid'         => $paid,
                'outstanding'  => $balance,
            ];
        })->filter(fn($row) => $row['outstanding'] > 0);

        $totalSum       = $invoices->sum('total');
        $paidSum        = $invoices->sum('paid');
        $outstandingSum = $invoices->sum('outstanding');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.pdf.outstanding',
            compact('invoices', 'type', 'from', 'to', 'totalSum', 'paidSum', 'outstandingSum')
        )->setPaper('a4', 'landscape');

        return $pdf->download('outstanding_payments.pdf');
    }
    public function selectCompany()
{
    $companies = \App\Models\Company::orderBy('company_name')->get();
    return view('reports.select_company', compact('companies'));
}

public function companyReport(Request $request)
{
    $company = \App\Models\Company::findOrFail($request->company_id);

    // All customers under the selected company
    $customers = \App\Models\Customer::where('company_id', $company->id)->get();

    $startDate = $request->start_date;
    $endDate   = $request->end_date;

    $reportData = [];

    foreach ($customers as $customer) {
        // Fetch invoices for each customer
        $invoices = $customer->invoices()
            ->with(['items.product.department'])
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->get();

        $departmentWise = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $deptName = $item->product->department->name ?? 'Unassigned';
                $key = $deptName . ($invoice->is_vat_invoice ? ' (VAT)' : ' (Non-VAT)');

                // ✅ Use stored database values
                $amount = $item->total;
                $vat = $invoice->is_vat_invoice ? $item->vat_amount : null;
                $total = $amount + ($vat ?? 0);

                if (!isset($departmentWise[$key])) {
                    $departmentWise[$key] = [
                        'amount' => 0,
                        'vat'    => 0,
                        'total'  => 0,
                    ];
                }

                $departmentWise[$key]['amount'] += $amount;
                $departmentWise[$key]['vat']    += $vat ?? 0;
                $departmentWise[$key]['total']  += $total;
            }
        }

        $reportData[] = [
            'customer'       => $customer->customer_name,
            'departmentWise' => $departmentWise,
            'total'          => array_sum(array_column($departmentWise, 'total')),
        ];
    }

    return view('reports.company_report', compact(
        'company', 'reportData', 'startDate', 'endDate'
    ));
}




// public function companyReport(Request $request)
// {
//     $company = \App\Models\Company::findOrFail($request->company_id);

//     // Customers under this company
//     $customers = \App\Models\Customer::where('company_id', $company->id)->get();

//     $startDate = $request->start_date;
//     $endDate   = $request->end_date;

//     // Build results
//     $reportData = [];

//     foreach ($customers as $customer) {
//         $invoices = $customer->invoices()
//             ->with(['items.product.department'])
//             ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
//             ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
//             ->get();

//         $departmentWise = [];

//         foreach ($invoices as $invoice) {
//             foreach ($invoice->items as $item) {
//                 $deptName = $item->product->department->name ?? 'Unassigned';
//                 $key = $deptName . ($invoice->is_vat_invoice ? ' (VAT)' : ' (Non-VAT)');

//                 if (!isset($departmentWise[$key])) {
//                     $departmentWise[$key] = 0;
//                 }

//                 $departmentWise[$key] += $item->total; // use `total` from InvoiceItem
//             }
//         }

//         $reportData[] = [
//             'customer'       => $customer->customer_name,
//             'departmentWise' => $departmentWise,
//             'total'          => array_sum($departmentWise),
//         ];
//     }

//     return view('reports.company_report', compact(
//         'company', 'reportData', 'startDate', 'endDate'
//     ));
// }
public function exportCompanyExcel(Request $request)
{
    $company = Company::findOrFail($request->company_id);

    // Get all customers under the company
    $customerIds = Customer::where('company_id', $company->id)->pluck('id');

    $invoices = Invoice::with(['items.product.department', 'invoiceable'])
        ->where('invoiceable_type', Customer::class)
        ->whereIn('invoiceable_id', $customerIds) // ✅ safer than whereHas
        ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
        ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
        ->get();

    return Excel::download(new class($invoices, $company) implements 
        \Maatwebsite\Excel\Concerns\FromCollection, 
        \Maatwebsite\Excel\Concerns\WithHeadings 
    {
        private $invoices, $company;
        public function __construct($invoices, $company) {
            $this->invoices = $invoices;
            $this->company = $company;
        }
        public function collection() {
            return $this->invoices->flatMap(fn($inv) =>
                $inv->items->map(fn($item) => [
                    'Company'    => $this->company->company_name,
                    'Customer'   => $inv->invoiceable->customer_name ?? '-',
                    'Department' => $item->product->department->department_name ?? '-',
                    'Product'    => $item->product->product_name ?? $item->description,
                    'Qty'        => $item->quantity,
                    'Unit Price' => $item->unit_price,
                    'Total'      => $item->total,
                    'VAT Invoice'=> $inv->is_vat_invoice ? 'Yes' : 'No',
                    'Date'       => $inv->created_at->format('Y-m-d'),
                ])
            );
        }
        public function headings(): array {
            return ['Company','Customer','Department','Product','Qty','Unit Price','Total','VAT Invoice','Date'];
        }
    }, 'company_report.xlsx');
}
public function exportCompanyPdf(Request $request)
{
    $company = Company::findOrFail($request->company_id);
    $customers = Customer::where('company_id', $company->id)->get();
    $startDate = $request->start_date;
    $endDate   = $request->end_date;

    $reportData = [];

    foreach ($customers as $customer) {
        $invoices = $customer->invoices()
            ->with(['items.product.department'])
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->get();

        $departmentWise = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $deptName = $item->product->department->name ?? 'Unassigned';
                $key = $deptName . ($invoice->is_vat_invoice ? ' (VAT)' : ' (Non-VAT)');

                // Ensure it's always an array structure
                if (!isset($departmentWise[$key]) || !is_array($departmentWise[$key])) {
                    $departmentWise[$key] = ['amount' => 0, 'vat' => 0, 'total' => 0];
                }

                $amount = $item->total;
                $vat = $invoice->is_vat_invoice ? $item->vat_amount : 0;
                $total = $amount + $vat;

                $departmentWise[$key]['amount'] += $amount;
                $departmentWise[$key]['vat'] += $vat;
                $departmentWise[$key]['total'] += $total;
            }
        }

        $reportData[] = [
            'customer'       => $customer->customer_name,
            'departmentWise' => $departmentWise,
            'total'          => array_sum(array_column($departmentWise, 'total')),
        ];
    }

    $pdf = \PDF::loadView('reports.pdf.company_report', [
        'company'    => $company,
        'reportData' => $reportData,
        'start_date' => $startDate,
        'end_date'   => $endDate,
    ])->setPaper('a4', 'landscape');

    return $pdf->download('company_report.pdf');
}

public function companyOutstandingReport(Request $request)
{
    $companies = Company::orderBy('company_name')->get();
    $selectedCompany = $request->company_id ? Company::find($request->company_id) : null;
    $startDate = $request->start_date;
    $endDate   = $request->end_date;

    // Base query for all customers or company-specific
    $query = Customer::with(['invoices.payments']);
    if ($selectedCompany) {
        $query->where('company_id', $selectedCompany->id);
    }

    $customers = $query->orderBy('customer_name')->get();

    $reportData = [];
    foreach ($customers as $customer) {
        $invoices = $customer->invoices
            ->when($startDate, fn($c) => $c->whereBetween('created_at', [$startDate, $endDate]))
            ->values();

        $totalAmount = $invoices->sum('total_amount');
        $paidAmount  = $invoices->flatMap->payments->sum('amount');
        $outstanding = $totalAmount - $paidAmount;

        if ($totalAmount > 0 || $paidAmount > 0) {
            $reportData[] = [
                'company'     => $customer->company->company_name ?? '-',
                'customer'    => $customer->customer_name,
                'total'       => $totalAmount,
                'paid'        => $paidAmount,
                'outstanding' => $outstanding,
            ];
        }
    }

    // Calculate overall totals
    $totals = [
        'totalSum'       => collect($reportData)->sum('total'),
        'paidSum'        => collect($reportData)->sum('paid'),
        'outstandingSum' => collect($reportData)->sum('outstanding'),
    ];

    return view('reports.company_outstanding', compact(
        'companies', 'selectedCompany', 'reportData', 'startDate', 'endDate', 'totals'
    ));
}


public function exportCompanyOutstandingExcel(Request $request)
{
    $company = $request->company_id ? Company::find($request->company_id) : null;
    $startDate = $request->start_date;
    $endDate   = $request->end_date;

    $query = Customer::with(['invoices.payments', 'company']);
    if ($company) {
        $query->where('company_id', $company->id);
    }
    $customers = $query->orderBy('customer_name')->get();

    $rows = collect();

    foreach ($customers as $customer) {
        $invoices = $customer->invoices
            ->when($startDate, fn($c) => $c->whereBetween('created_at', [$startDate, $endDate]))
            ->values();

        $totalAmount = $invoices->sum('total_amount');
        $paidAmount  = $invoices->flatMap->payments->sum('amount');
        $outstanding = $totalAmount - $paidAmount;

        if ($totalAmount > 0 || $paidAmount > 0) {
            $rows->push([
                'Company'      => $customer->company->company_name ?? '-',
                'Customer'     => $customer->customer_name,
                'Total Amount' => number_format($totalAmount, 2),
                'Paid Amount'  => number_format($paidAmount, 2),
                'Outstanding'  => number_format($outstanding, 2),
            ]);
        }
    }

    // Add totals row
    $rows->push([
        'Company'      => 'TOTAL',
        'Customer'     => '',
        'Total Amount' => number_format($rows->sum(fn($r) => str_replace(',', '', $r['Total Amount'])), 2),
        'Paid Amount'  => number_format($rows->sum(fn($r) => str_replace(',', '', $r['Paid Amount'])), 2),
        'Outstanding'  => number_format($rows->sum(fn($r) => str_replace(',', '', $r['Outstanding'])), 2),
    ]);

    return Excel::download(new class($rows) implements 
        \Maatwebsite\Excel\Concerns\FromCollection, 
        \Maatwebsite\Excel\Concerns\WithHeadings 
    {
        private $rows;
        public function __construct($rows) { $this->rows = $rows; }
        public function collection() { return $this->rows; }
        public function headings(): array {
            return ['Company', 'Customer Name', 'Total Amount (LKR)', 'Paid Amount (LKR)', 'Outstanding (LKR)'];
        }
    }, 'customer_outstanding_report.xlsx');
}


public function exportCompanyOutstandingPdf(Request $request)
{
    $company = $request->company_id ? Company::find($request->company_id) : null;
    $startDate = $request->start_date;
    $endDate   = $request->end_date;

    $query = Customer::with(['invoices.payments', 'company']);
    if ($company) {
        $query->where('company_id', $company->id);
    }
    $customers = $query->orderBy('customer_name')->get();

    $reportData = [];
    foreach ($customers as $customer) {
        $invoices = $customer->invoices
            ->when($startDate, fn($c) => $c->whereBetween('created_at', [$startDate, $endDate]))
            ->values();

        $totalAmount = $invoices->sum('total_amount');
        $paidAmount  = $invoices->flatMap->payments->sum('amount');
        $outstanding = $totalAmount - $paidAmount;

        if ($totalAmount > 0 || $paidAmount > 0) {
            $reportData[] = [
                'company'     => $customer->company->company_name ?? '-',
                'customer'    => $customer->customer_name,
                'total'       => $totalAmount,
                'paid'        => $paidAmount,
                'outstanding' => $outstanding,
            ];
        }
    }

    $totals = [
        'totalSum'       => collect($reportData)->sum('total'),
        'paidSum'        => collect($reportData)->sum('paid'),
        'outstandingSum' => collect($reportData)->sum('outstanding'),
    ];

    $pdf = Pdf::loadView('reports.pdf.company_outstanding', [
        'company'    => $company,
        'reportData' => $reportData,
        'startDate'  => $startDate,
        'endDate'    => $endDate,
        'totals'     => $totals,
    ])->setPaper('a4', 'landscape');

    return $pdf->download('customer_outstanding_report.pdf');
}
public function returnNoteReport(Request $request): View
{
    $query = \App\Models\ReturnNote::with(['company', 'customer', 'agent', 'receiveNote', 'product']);

    // ✅ Filters
    if ($request->filled('company_id')) {
        $query->where('company_id', $request->company_id);
    }
    if ($request->filled('customer_id')) {
        $query->where('customer_id', $request->customer_id);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('return_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('return_date', '<=', $request->end_date);
    }

    $returnNotes = $query->latest()->paginate(15)->withQueryString();

    // Dropdown data
    $companies = \App\Models\Company::orderBy('company_name')->get();
    $customers = \App\Models\Customer::orderBy('customer_name')->get();

    return view('reports.return_note_report', compact('returnNotes', 'companies', 'customers'));
}
public function exportReturnNotesExcel(Request $request)
{
    $query = \App\Models\ReturnNote::with(['company', 'customer', 'agent', 'receiveNote', 'product']);

    // Apply filters
    if ($request->filled('company_id')) {
        $query->where('company_id', $request->company_id);
    }
    if ($request->filled('customer_id')) {
        $query->where('customer_id', $request->customer_id);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('return_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('return_date', '<=', $request->end_date);
    }

    $returnNotes = $query->get();

    return \Maatwebsite\Excel\Facades\Excel::download(new class($returnNotes) implements 
        \Maatwebsite\Excel\Concerns\FromCollection,
        \Maatwebsite\Excel\Concerns\WithHeadings
    {
        private $returnNotes;
        public function __construct($returnNotes) { $this->returnNotes = $returnNotes; }

        public function collection()
        {
            return $this->returnNotes->map(fn($rn) => [
                'Return Note ID' => $rn->return_note_id,
                'Company'        => $rn->company->company_name ?? '-',
                'Customer'       => $rn->customer->customer_name ?? '-',
                'Agent'          => $rn->agent->name ?? '-',
                'Product'        => $rn->product->name ?? '-',
                'Quantity'       => $rn->quantity,
                'Receive Note'   => $rn->receiveNote->receive_note_id ?? '-',
                'Reason'         => $rn->reason ?? '-',
                'Return Date'    => optional($rn->return_date)->format('Y-m-d') ?? '-',
                'Status'         => ucfirst($rn->status),
            ]);
        }

        public function headings(): array
        {
            return [
                'Return Note ID', 'Company', 'Customer', 'Agent', 'Product',
                'Quantity', 'Receive Note', 'Reason', 'Return Date', 'Status'
            ];
        }
    }, 'return_note_report.xlsx');
}
public function exportReturnNotesPdf(Request $request)
{
    $query = \App\Models\ReturnNote::with(['company', 'customer', 'agent', 'receiveNote', 'product']);

    // Apply filters
    if ($request->filled('company_id')) {
        $query->where('company_id', $request->company_id);
    }
    if ($request->filled('customer_id')) {
        $query->where('customer_id', $request->customer_id);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('return_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('return_date', '<=', $request->end_date);
    }

    $returnNotes = $query->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.return_note_report', compact('returnNotes'))
            ->setPaper('a4', 'landscape');

    return $pdf->download('return_note_report.pdf');
}


}