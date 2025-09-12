<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Grn;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReceiveNote;
use App\Models\Supplier;
use App\Models\WastageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        // --- Stat Card Data ---
        $customerCount = Customer::count();
        $supplierCount = Supplier::count();
        $agentCount = Agent::count();
        $purchaseOrderCount = PurchaseOrder::count();
        $deliveryNoteCount = DeliveryNote::count();
        $receiveNoteCount = ReceiveNote::count();
        $invoiceCount = Invoice::count();
        $wastageLogCount = WastageLog::count();

        // --- Chart Data ---
        $clearStock = Product::sum('clear_stock_quantity');
        $nonClearStock = Product::sum('non_clear_stock_quantity');
        $totalWastage = WastageLog::sum('quantity');
        $stockData = [$clearStock, $nonClearStock, $totalWastage];

        $today = Carbon::today();
        $weeklySalesData = Invoice::where('created_at', '>=', $today->startOfWeek())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->get()->pluck('total', 'date')->toArray();
        
        $weeklyLabels = [];
        $weeklySales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $weeklyLabels[] = $date->format('l');
            $weeklySales[] = $weeklySalesData[$dateString] ?? 0;
        }
        
        return view('home', compact(
            'customerCount', 'supplierCount', 'agentCount', 'purchaseOrderCount',
            'deliveryNoteCount', 'receiveNoteCount', 'invoiceCount', 'wastageLogCount',
            'stockData', 'weeklyLabels', 'weeklySales'
        ));
    }
}

