<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WastageLog;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WastageLogsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class StockManagementController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // ✅ Require authentication for all actions
        $this->middleware('auth');

        // ✅ Apply permission-based access control (consistent with CustomerController pattern)
        $this->middleware('permission:stockmanagement-list|stockmanagement-create|stockmanagement-edit|stockmanagement-delete', 
            ['only' => ['index', 'wastageReport']]
        );
        $this->middleware('permission:stockmanagement-create', 
            ['only' => ['apiConvert', 'apiConvertINRN', 'apiWastage', 'apiWastageRN']]
        );
        $this->middleware('permission:stockmanagement-edit', 
            ['only' => ['exportWastageExcel', 'exportWastagePdf']]
        );
        $this->middleware('permission:stockmanagement-delete', 
            ['only' => []] // reserved for future delete-related actions
        );
    }

    /**
     * Show the main stock management dashboard.
     */
    public function index(): View
    {
        $products = Product::where('is_active', true)
                           ->orderBy('name')
                           ->get();
        $departments = Department::orderBy('name')->get();
                           
        return view('stock_management.index', compact('products', 'departments'));
    }

    /**
     * Convert non-clear stock to clear stock.
     */
    public function apiConvert(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = $request->quantity;

            if ($product->non_clear_stock_quantity < $quantity) {
                return response()->json(['success' => false, 'message' => 'Not enough non-clear stock to convert.']);
            }

            $product->decrement('non_clear_stock_quantity', $quantity);
            $product->increment('clear_stock_quantity', $quantity);

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Stock converted successfully.',
                'product' => $product->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Log wastage from stock.
     */
    public function apiWastage(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'stock_type' => 'required|string|in:clear,non-clear',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = $request->quantity;
            $stockType = $request->stock_type;

            if ($stockType === 'clear') {
                if ($product->clear_stock_quantity < $quantity) {
                    return response()->json(['success' => false, 'message' => 'Not enough clear stock for wastage.']);
                }
                $product->decrement('clear_stock_quantity', $quantity);
            } else {
                if ($product->non_clear_stock_quantity < $quantity) {
                    return response()->json(['success' => false, 'message' => 'Not enough non-clear stock for wastage.']);
                }
                $product->decrement('non_clear_stock_quantity', $quantity);
            }

            WastageLog::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'stock_type' => $stockType,
                'reason' => $request->reason,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Wastage logged successfully.',
                'product' => $product->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Export wastage report to Excel.
     */
    public function exportWastageExcel(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new WastageLogsExport($request), 'wastage_report.xlsx');
    }

    /**
     * Export wastage report to PDF.
     */
    public function exportWastagePdf(Request $request)
    {
        $query = WastageLog::with('product.department')->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('product', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->get();

        $pdf = Pdf::loadView('stock_management.wastage_report_pdf', compact('logs'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('wastage_report.pdf');
    }

    /**
     * Show the wastage report page.
     */
    public function wastageReport(Request $request): View
    {
        $query = WastageLog::with('product.department')->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('product', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $wastageLogs = $query->paginate(20);
        $products = Product::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('stock_management.wastage_report', compact('wastageLogs', 'products', 'departments'));
    }

    /**
     * Convert stock from Receive Note.
     */
    public function apiConvertINRN(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = $request->quantity;

            // Agent products: skip stock updates
            if ($product->agent_id) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Return Note created — agent product, stock unchanged.',
                ]);
            }

            // Non-agent products: increment stock
            $product->increment('clear_stock_quantity', $quantity);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Return Note created and stock updated successfully.',
                'product' => $product->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Log wastage from Receive Note.
     */
    public function apiWastageRN(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = $request->quantity;

            WastageLog::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'stock_type' => 'RN_wastage',
                'reason' => $request->reason,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Wastage logged successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
public function wastageLogs(Request $request): View
{
    $query = WastageLog::with('product')->latest();

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('search')) {
        $query->whereHas('product', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }

    $logs = $query->paginate(10);

    return view('stock_management.wastage_logs', compact('logs'));
}

public function markAsReturned(WastageLog $log): RedirectResponse
    {
        if ($log->status === 'returned') {
            return back()->with('info', 'This wastage has already been returned.');
        }

        // Update status
        $log->update(['status' => 'returned']);

        // Add back to the correct stock type
        $product = $log->product;

        if ($log->stock_type === 'clear') {
            $product->increment('clear_stock', $log->quantity);
        } elseif ($log->stock_type === 'non-clear') {
            $product->increment('non_clear_stock', $log->quantity);
        }

        return back()->with('success', 'Wastage returned and stock updated.');
    }
}
