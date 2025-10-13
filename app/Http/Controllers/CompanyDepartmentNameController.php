<?php

namespace App\Http\Controllers;

use App\Models\CompanyDepartmentName;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;

class CompanyDepartmentNameController extends Controller
{
    public function __construct()
    {
        // ✅ Require authentication for all actions
        $this->middleware('auth');

        // ✅ Define permissions for each action
        $this->middleware('permission:view company department names')->only(['index', 'show']);
        $this->middleware('permission:create company department names')->only(['create', 'store']);
        $this->middleware('permission:edit company department names')->only(['edit', 'update']);
        $this->middleware('permission:delete company department names')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = CompanyDepartmentName::with(['company', 'department'])->paginate(10);
        return view('company_department_names.index', compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        $departments = Department::all();
        return view('company_department_names.create', compact('companies', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id'    => 'required|exists:companies,id',
            'department_id' => 'required|exists:departments,id',
            'appear_name'   => 'required|string|max:255',
        ]);

        // prevent duplicate company + department pair
        $exists = CompanyDepartmentName::where('company_id', $request->company_id)
                    ->where('department_id', $request->department_id)
                    ->first();

        if ($exists) {
            return redirect()->back()->withErrors([
                'duplicate' => 'This company already has a custom name for the selected department.'
            ])->withInput();
        }

        CompanyDepartmentName::create($request->only(['company_id', 'department_id', 'appear_name']));

        return redirect()->route('company_department_names.index')
                         ->with('success', 'Companywise Department Name created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyDepartmentName $companyDepartmentName)
    {
        return view('company_department_names.show', compact('companyDepartmentName'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyDepartmentName $companyDepartmentName)
    {
        $companies = Company::all();
        $departments = Department::all();
        return view('company_department_names.edit', compact('companyDepartmentName', 'companies', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompanyDepartmentName $companyDepartmentName)
    {
        $request->validate([
            'company_id'    => 'required|exists:companies,id',
            'department_id' => 'required|exists:departments,id',
            'appear_name'   => 'required|string|max:255',
        ]);

        // prevent duplicate company + department pair (excluding current record)
        $exists = CompanyDepartmentName::where('company_id', $request->company_id)
                    ->where('department_id', $request->department_id)
                    ->where('id', '!=', $companyDepartmentName->id)
                    ->first();

        if ($exists) {
            return redirect()->back()->withErrors([
                'duplicate' => 'This company already has a custom name for the selected department.'
            ])->withInput();
        }

        $companyDepartmentName->update($request->only(['company_id', 'department_id', 'appear_name']));

        return redirect()->route('company_department_names.index')
                         ->with('success', 'Companywise Department Name updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyDepartmentName $companyDepartmentName)
    {
        $companyDepartmentName->delete();

        return redirect()->route('company_department_names.index')
                         ->with('success', 'Companywise Department Name deleted successfully.');
    }
}
