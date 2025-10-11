<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WastageLog;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WastageLogsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class StockManagementController extends Controller
{
    /**
     * Show the main form for managing stock.
     */
    public function index(): View
    {
        $products = Product::where('is_active', true)
                           ->orderBy('name')
                           ->get();
        $departments = Department::orderBy('name')->get();
                           
        return view('stock_management.index', compact('products', 'departments' ));
    }

    /**
     * API endpoint to convert non-clear stock to clear stock.
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
                'product' => $product->fresh() // Return updated product data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * API endpoint to log a quantity of stock as wastage.
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
            } else { // non-clear
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
                'product' => $product->fresh() // Return updated product data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

public function exportWastageExcel(Request $request)
{
    return Excel::download(new WastageLogsExport($request), 'wastage_report.xlsx');
}

public function exportWastagePdf(Request $request)
{
    // reuse same query as report
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
 * Show the wastage report.
 */
public function wastageReport(Request $request): View
{
    $query = WastageLog::with('product.department')->latest();

    // Optional filters
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

        // 🔍 If product is assigned to an agent, don't increment stock
        if ($product->agent_id) {
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Return Note created — agent product, stock unchanged.',
            ]);
        }

        // ✅ For non-agent products: add to clear stock
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

}

