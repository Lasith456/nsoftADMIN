<?php

namespace App\Exports;

use App\Models\WastageLog;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;

class WastageLogsExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = WastageLog::with('product.department')->latest();

        if ($this->request->filled('product_id')) {
            $query->where('product_id', $this->request->product_id);
        }
        if ($this->request->filled('department_id')) {
            $query->whereHas('product', fn($q) => $q->where('department_id', $this->request->department_id));
        }
        if ($this->request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $this->request->from_date);
        }
        if ($this->request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $this->request->to_date);
        }

        $logs = $query->get();

        return view('stock_management.exports.wastage_excel', compact('logs'));
    }
}
