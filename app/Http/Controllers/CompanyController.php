<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // Apply permission middleware
        $this->middleware('permission:company-list|company-create|company-edit|company-delete', ['only' => ['index']]);
        $this->middleware('permission:company-create', ['only' => ['create','store']]);
        $this->middleware('permission:company-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:company-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $companies = Company::orderBy('id', 'desc')->paginate(10);
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|unique:companies,company_name|max:255',
        ]);

        Company::create($request->only('company_name'));

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'company_name' => 'required|unique:companies,company_name,' . $company->id . '|max:255',
        ]);

        $company->update($request->only('company_name'));

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
