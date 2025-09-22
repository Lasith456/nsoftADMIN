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
}

