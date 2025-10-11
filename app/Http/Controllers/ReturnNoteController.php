<?php

namespace App\Http\Controllers;

use App\Models\ReturnNote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReturnNoteController extends Controller
{
    /**
     * AJAX create (for popup in Receive Note page)
     */
    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'customer_id' => 'nullable|exists:customers,id',
            'return_date' => 'nullable|date'
        ]);

        $returnNote = ReturnNote::create([
            'company_id' => $validated['company_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'reason' => $validated['reason'],
            'return_date' => $validated['return_date'] ?? now(),
            'created_by' => auth()->id(),
            'agent_id'     => $request->agent_id,
        ]);

        return response()->json([
            'success' => true,
            'return_note_id' => $returnNote->return_note_id,
            'id' => $returnNote->id
        ]);
    }

    /**
     * Optional index (for future)
     */
    public function index()
    {
        $returnNotes = ReturnNote::latest()->paginate(10);
        return view('return-notes.index', compact('returnNotes'));
    }
}
