<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\Province;
use App\Models\Center;
use App\Models\Representative;
use App\Models\Warehouse;
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

    public function show(Request $request, Zone $zone)
    {
        $zone->load(['province', 'salesRepresentative', 'medicalRepresentative', 'centers', 'warehouse', 'expenses']);

        // التحقق من طلب التصدير
        if ($request->has('export') && $request->export == 'excel') {
            $filename = "zone_expenses_" . $zone->id . "_" . date('Y-m-d') . ".csv";

            $callback = function () use ($zone) {
                $file = fopen('php://output', 'w');
                // دعم اللغة العربية
                fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, ['تقرير مصروفات المنطقة']);
                fputcsv($file, ['المنطقة:', $zone->name, 'الخط:', 'Line ' . $zone->line]);
                fputcsv($file, []);

                // ترويسة الجدول
                fputcsv($file, ['التاريخ', 'بيان المصروف (في إيه)', 'المبلغ', 'وقت الإضافة']);

                foreach ($zone->expenses as $expense) {
                    fputcsv($file, [
                        $expense->expense_date,
                        $expense->description,
                        $expense->amount,
                        $expense->created_at->format('Y-m-d H:i')
                    ]);
                }

                fputcsv($file, []);
                fputcsv($file, ['', 'الإجمالي:', $zone->expenses->sum('amount')]);

                fclose($file);
            };

            return response()->stream($callback, 200, [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ]);
        }

        return view('admin.zones.show', compact('zone'));
    }
}
