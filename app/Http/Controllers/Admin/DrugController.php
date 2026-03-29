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
    public function show(Drug $drug)
    {
        // 1. جلب سجل المبيعات (التفاصيل) مع بيانات الفاتورة
        $salesHistory = \App\Models\InvoiceDetail::where('drug_id', $drug->id)
            ->with(['invoice.pharmacist', 'invoice.representative']) // بيانات الفاتورة والصيدلي والمندوب
            ->latest()
            ->paginate(15);

        // 2. حساب الإحصائيات
        $totalQuantitySold = \App\Models\InvoiceDetail::where('drug_id', $drug->id)->sum('quantity');
        $totalRevenue = \App\Models\InvoiceDetail::where('drug_id', $drug->id)->sum('row_total');
        $invoicesCount = \App\Models\InvoiceDetail::where('drug_id', $drug->id)->count(); // عدد الفواتير التي ظهر فيها

        return view('admin.drugs.show', compact(
            'drug',
            'salesHistory',
            'totalQuantitySold',
            'totalRevenue',
            'invoicesCount'
        ));
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
