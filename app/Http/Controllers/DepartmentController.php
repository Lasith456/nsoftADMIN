<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:department-list|department-create|department-edit|department-delete', ['only' => ['index','show']]);
        $this->middleware('permission:department-create', ['only' => ['create','store']]);
        $this->middleware('permission:department-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:department-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $query = Department::query();

        // Handle the search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $departments = $query->latest()->paginate(10);
        return view('departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create($request->all());

        return redirect()->route('departments.index')
                         ->with('success','Department created successfully.');
    }

    public function show(Department $department): View
    {
        // Load the related sub-departments to display them
        $department->load('subDepartments');
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update($request->all());

        return redirect()->route('departments.index')
                         ->with('success','Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        // The onDelete('cascade') in the migration will handle deleting related sub-departments.
        $department->delete();

        return redirect()->route('departments.index')
                         ->with('success','Department deleted successfully.');
    }
    public function apiStore(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        $department = Department::create($request->all());

        return response()->json($department);
    }
}