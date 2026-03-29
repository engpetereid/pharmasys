<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Pharmacist;
use Illuminate\Http\Request;

class PharmacistController extends Controller
{
    public function index(Request $request)
    {
        $query = Pharmacist::with('center');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        }

        $pharmacists = $query->latest()->paginate(10);

        $pharmacists->appends($request->all());

        return view('admin.pharmacists.index', compact('pharmacists'));
    }


    public function create(Request $request) {
        $centers = Center::all();
        $selected_center_id = $request->get('center_id');
        return view('admin.pharmacists.create', compact('centers', 'selected_center_id'));
    }

    public function store(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'center_id' => 'required|integer|exists:centers,id',
        ]);
        try {
            Pharmacist::create($validatedData);
            return redirect()->route('admin.pharmacists.index')->with(['success' => 'تم الإضافة بنجاح']);
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }

    public function show(Pharmacist $pharmacist) {
        $pharmacist->load('center.province');

        $invoices = $pharmacist->invoices()
            ->with(['doctors', 'representative', 'details'])
            ->latest()
            ->paginate(10);

        $totalSales = $pharmacist->invoices()->sum('final_total');
        $totalPaid = $pharmacist->invoices()->sum('paid_amount');
        $totalDue = $pharmacist->invoices()->sum('remaining_amount');

        return view('admin.pharmacists.show', compact('pharmacist', 'invoices', 'totalSales', 'totalPaid', 'totalDue'));
    }

    public function edit(Pharmacist $pharmacist) {
        $centers = Center::all();
        return view('admin.pharmacists.edit', compact('pharmacist', 'centers'));
    }

    public function update(Request $request, Pharmacist $pharmacist) {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'center_id' => 'required|integer|exists:centers,id',
        ]);
        try {
            $pharmacist->update($validatedData);
            return redirect()->route('admin.pharmacists.index')->with(['success' => 'تم التعديل بنجاح']);
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }

    public function destroy(Pharmacist $pharmacist) {
        try {
            $pharmacist->delete();
            return redirect()->route('admin.pharmacists.index')->with(['success' => 'تم الحذف بنجاح']);
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
}
