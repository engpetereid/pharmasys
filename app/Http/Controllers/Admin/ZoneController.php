<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\Province;
use App\Models\Center;
use App\Models\Representative;
use App\Models\Warehouse; // استدعاء الموديل الجديد
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::with(['province', 'salesRepresentative', 'medicalRepresentative', 'centers', 'warehouse'])
            ->orderBy('province_id')
            ->orderBy('line')
            ->paginate(10);

        return view('admin.zones.index', compact('zones'));
    }

    public function create()
    {
        $provinces = Province::all();
        $centers = Center::select('id', 'name', 'province_id')->get();
        $representatives = Representative::all();
        $warehouses = Warehouse::all();

        return view('admin.zones.create', compact('provinces', 'centers', 'representatives', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'warehouse_id' => 'required|exists:warehouses,id',

            'line1_sales_representative_id' => 'nullable|exists:representatives,id',
            'line1_medical_representative_id' => 'nullable|exists:representatives,id',

            'line2_sales_representative_id' => 'nullable|exists:representatives,id',
            'line2_medical_representative_id' => 'nullable|exists:representatives,id',

            'centers' => 'required|array|min:1',
            'centers.*' => 'exists:centers,id',
        ], [
            'warehouse_id.required' => 'يجب تحديد المخزن المسؤول عن صرف بضاعة هذه المنطقة.',
        ]);

        try {
            DB::beginTransaction();

            $zoneLine1 = Zone::create([
                'name' => $request->name,
                'province_id' => $request->province_id,
                'line' => 1,
                'sales_representative_id' => $request->line1_sales_representative_id,
                'medical_representative_id' => $request->line1_medical_representative_id,
                'warehouse_id' => $request->warehouse_id,
            ]);
            $zoneLine1->centers()->sync($request->centers);

            $zoneLine2 = Zone::create([
                'name' => $request->name,
                'province_id' => $request->province_id,
                'line' => 2,
                'sales_representative_id' => $request->line2_sales_representative_id,
                'medical_representative_id' => $request->line2_medical_representative_id,
                'warehouse_id' => $request->warehouse_id,
            ]);
            $zoneLine2->centers()->sync($request->centers);

            DB::commit();
            return redirect()->route('admin.zones.index')->with(['success' => 'تم إضافة المنطقتين (Line 1 و Line 2) بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Zone $zone)
    {
        $provinces = Province::all();
        $centers = Center::select('id', 'name', 'province_id')->get();
        $representatives = Representative::all();
        $warehouses = Warehouse::all();

        $zone->load('centers');
        $selectedCenters = $zone->centers->pluck('id')->toArray();

        return view('admin.zones.edit', compact('zone', 'provinces', 'centers', 'representatives', 'warehouses', 'selectedCenters'));
    }

    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'line' => 'required|in:1,2',
            'sales_representative_id' => 'nullable|exists:representatives,id',
            'medical_representative_id' => 'nullable|exists:representatives,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'centers' => 'required|array|min:1',
            'centers.*' => 'exists:centers,id',
        ]);

        try {
            DB::beginTransaction();
            $zone->update([
                'name' => $request->name,
                'province_id' => $request->province_id,
                'line' => $request->line,
                'sales_representative_id' => $request->sales_representative_id,
                'medical_representative_id' => $request->medical_representative_id,
                'warehouse_id' => $request->warehouse_id,
            ]);
            $zone->centers()->sync($request->centers);
            DB::commit();
            return redirect()->route('admin.zones.index')->with(['success' => 'تم تحديث المنطقة بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Zone $zone)
    {
        try {
            $zone->delete();
            return redirect()->route('admin.zones.index')->with(['success' => 'تم الحذف بنجاح']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }

    public function show(Zone $zone)
    {
        $zone->load(['province', 'salesRepresentative', 'medicalRepresentative', 'centers', 'warehouse']);
        return view('admin.zones.show', compact('zone'));
    }
}
