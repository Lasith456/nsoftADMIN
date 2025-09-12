<?php

namespace App\Http\Controllers;

use App\Models\SubDepartment;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class SubDepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:subdepartment-list|subdepartment-create|subdepartment-edit|subdepartment-delete', ['only' => ['index','show']]);
        $this->middleware('permission:subdepartment-create', ['only' => ['create','store']]);
        $this->middleware('permission:subdepartment-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:subdepartment-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $query = SubDepartment::with('department');

        // Handle the search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhereHas('department', function ($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
        }

        $subDepartments = $query->latest()->paginate(10);
        return view('subdepartments.index', compact('subDepartments'));
    }
    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        return view('subdepartments.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        SubDepartment::create($request->all());

        return redirect()->route('subdepartments.index')
                         ->with('success','Sub-Department created successfully.');
    }

    public function show(SubDepartment $subdepartment): View
    {
        return view('subdepartments.show', compact('subdepartment'));
    }

    public function edit(SubDepartment $subdepartment): View
    {
        $departments = Department::orderBy('name')->get();
        return view('subdepartments.edit', compact('subdepartment', 'departments'));
    }

    public function update(Request $request, SubDepartment $subdepartment): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $subdepartment->update($request->all());

        return redirect()->route('subdepartments.index')
                         ->with('success','Sub-Department updated successfully.');
    }

    public function destroy(SubDepartment $subdepartment): RedirectResponse
    {
        $subdepartment->delete();

        return redirect()->route('subdepartments.index')
                         ->with('success','Sub-Department deleted successfully.');
    }
        public function apiStore(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $subDepartment = SubDepartment::create($request->all());

        return response()->json($subDepartment);
    }
}