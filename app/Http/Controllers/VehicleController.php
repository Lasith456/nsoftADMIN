<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:vehicle-list|vehicle-create|vehicle-edit|vehicle-delete', ['only' => ['index','show']]);
        $this->middleware('permission:vehicle-create', ['only' => ['create','store']]);
        $this->middleware('permission:vehicle-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:vehicle-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $query = Vehicle::query();

        // Handle the search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle_no', 'LIKE', "%{$search}%")
                  ->orWhere('driver_name', 'LIKE', "%{$search}%")
                  ->orWhere('driver_mobile', 'LIKE', "%{$search}%");
            });
        }

        $vehicles = $query->latest()->paginate(10);
        return view('vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_no' => 'required|string|max:255|unique:vehicles,vehicle_no',
            'title' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_nic' => 'nullable|string|max:255|unique:vehicles,driver_nic',
            'driver_address' => 'required|string',
            'driver_mobile' => 'required|string|max:20',
            'assistant_nic' => 'nullable|string|max:255|unique:vehicles,assistant_nic',
        ]);

        $input = $request->all();
        $input['vehicle_id'] = 'VEH-' . strtoupper(Str::random(6));
        $input['is_active'] = $request->has('is_active');

        Vehicle::create($input);

        return redirect()->route('vehicles.index')
                         ->with('success','Vehicle created successfully.');
    }

    public function show(Vehicle $vehicle): View
    {
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->validate([
            'vehicle_no' => 'required|string|max:255|unique:vehicles,vehicle_no,' . $vehicle->id,
            'title' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_nic' => 'nullable|string|max:255|unique:vehicles,driver_nic,' . $vehicle->id,
            'driver_address' => 'required|string',
            'driver_mobile' => 'required|string|max:20',
            'assistant_nic' => 'nullable|string|max:255|unique:vehicles,assistant_nic,' . $vehicle->id,
        ]);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active');

        $vehicle->update($input);

        return redirect()->route('vehicles.index')
                         ->with('success','Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')
                         ->with('success','Vehicle deleted successfully.');
    }
}