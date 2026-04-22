<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DrugController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // ترتيب الأدوية حسب الأحدث
        $drugs = Drug::latest()->paginate(10);
        return view('admin.drugs.index', compact('drugs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.drugs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. التحقق من البيانات (بما في ذلك Line)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:drugs,name',
            'price' => 'required|numeric|min:0',
            'line' => 'required|in:1,2', // التحقق من أن الخط إما 1 أو 2
        ], [
            'name.unique' => 'اسم الدواء مسجل مسبقاً.',
            'line.required' => 'يرجى اختيار الخط (Line 1 أو Line 2).',
        ]);

        try {
            DB::beginTransaction();

            // 2. إنشاء الدواء
            Drug::create($validatedData);

            DB::commit();
            return redirect()->route('admin.drugs.index')->with(['success' => 'تم إضافة الدواء بنجاح']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Drug $drug)
    {
        // جلب جميع المناطق لقائمة الفلتر
        $zones = \App\Models\Zone::all();

        // 1. بناء الاستعلام مع تطبيق فلاتر (التاريخ، المنطقة، حالة الدفع) على الفاتورة المرتبطة
        $query = \App\Models\InvoiceDetail::where('drug_id', $drug->id)
            ->whereHas('invoice', function ($invoiceQuery) use ($request) {
                if ($request->filled('start_date')) {
                    $invoiceQuery->whereDate('invoice_date', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $invoiceQuery->whereDate('invoice_date', '<=', $request->end_date);
                }
                if ($request->filled('zone_id')) {
                    $invoiceQuery->whereHas('pharmacist.center.zones', function ($zoneQuery) use ($request) {
                        $zoneQuery->where('zones.id', $request->zone_id);
                    });
                }
                if ($request->filled('status')) {
                    $invoiceQuery->where('status', $request->status);
                }
            });

        // 2. حساب الإحصائيات (تحسب بناءً على الفلتر المطبق)
        $totalQuantitySold = (clone $query)->sum('quantity');
        $totalRevenue = (clone $query)->sum('row_total');
        $invoicesCount = (clone $query)->count(); // عدد الفواتير التي ظهر فيها

        // 3. تصدير إكسيل
        if ($request->has('export') && $request->export == 'excel') {
            $exportData = (clone $query)->with(['invoice.pharmacist', 'invoice.representative'])->latest()->get();
            return $this->exportExcel($exportData, $drug, $totalQuantitySold, $totalRevenue);
        }

        // 4. جلب سجل المبيعات (التفاصيل) وعرضها
        $salesHistory = $query->with(['invoice.pharmacist', 'invoice.representative'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.drugs.show', compact(
            'drug',
            'salesHistory',
            'totalQuantitySold',
            'totalRevenue',
            'invoicesCount',
            'zones'
        ));
    }

    /**
     * دالة مساعدة لتصدير التقرير
     */
    private function exportExcel($data, $drug, $totalQty, $totalRev)
    {
        $filename = "drug_report_{$drug->id}_" . date('Y-m-d') . ".csv";

        $callback = function () use ($data, $drug, $totalQty, $totalRev) {
            $file = fopen('php://output', 'w');

            // إضافة BOM لدعم اللغة العربية في Excel
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['تقرير صنف:', $drug->name]);
            fputcsv($file, ['إجمالي الكمية المباعة:', $totalQty]);
            fputcsv($file, ['إجمالي الإيرادات:', number_format($totalRev, 2) . ' ج.م']);
            fputcsv($file, []);

            // ترويسة الجدول
            fputcsv($file, ['رقم الفاتورة', 'التاريخ', 'الصيدلية', 'المندوب', 'الكمية', 'سعر البيع', 'الإجمالي', 'حالة الفاتورة']);

            foreach ($data as $detail) {
                $statusText = match ((int)($detail->invoice->status ?? 0)) {
                    1 => 'مدفوع',
                    2 => 'آجل',
                    3 => 'جزئي',
                    default => '-'
                };

                fputcsv($file, [
                    $detail->invoice->serial_number ?? $detail->invoice_id,
                    $detail->invoice->invoice_date ?? '-',
                    $detail->invoice->pharmacist->name ?? '-',
                    $detail->invoice->representative->name ?? '-',
                    $detail->quantity,
                    $detail->unit_price,
                    $detail->row_total,
                    $statusText
                ]);
            }
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drug $drug)
    {
        return view('admin.drugs.edit', compact('drug'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drug $drug)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:drugs,name,' . $drug->id,
            'price' => 'required|numeric|min:0',
            'line' => 'required|in:1,2',
        ]);

        try {
            DB::beginTransaction();
            $drug->update($validatedData);

            DB::commit();
            return redirect()->route('admin.drugs.index')->with(['success' => 'تم تعديل بيانات الدواء بنجاح']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drug $drug)
    {
        try {
            $drug->delete();
            return redirect()->route('admin.drugs.index')->with(['success' => 'تم حذف الدواء بنجاح']);
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $exception->getMessage()]);
        }
    }
}
