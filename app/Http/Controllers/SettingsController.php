<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $vatRate = Setting::firstOrCreate(['key' => 'vat_rate'], ['value' => '18.00']);
        $banks = Bank::orderBy('name')->get();

        return view('settings.index', compact('vatRate', 'banks'));
    }

    /**
     * Update the VAT rate setting.
     */
    public function updateVat(Request $request): RedirectResponse
    {
        $request->validate(['vat_rate' => 'required|numeric|min:0']);

        Setting::updateOrCreate(
            ['key' => 'vat_rate'],
            ['value' => $request->vat_rate]
        );

        return redirect()->route('settings.index')->with('success', 'VAT rate updated successfully.');
    }

    /**
     * Store a new bank.
     */
    public function storeBank(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:banks,name',
            'code' => 'nullable|string|max:255|unique:banks,code,NULL,id',
        ]);

        Bank::create($request->all());

        return redirect()->route('settings.index')->with('success', 'Bank added successfully.');
    }
    
    /**
     * Toggle the active status of a bank.
     */
    public function toggleBankStatus(Bank $bank): RedirectResponse
    {
        $bank->update(['is_active' => !$bank->is_active]);

        $status = $bank->is_active ? 'activated' : 'deactivated';
        return redirect()->route('settings.index')->with('success', "Bank '{$bank->name}' has been {$status}.");
    }
}

