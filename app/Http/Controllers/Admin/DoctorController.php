<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = Doctor::with('center');

        if ($request->has('search') && $request->search != null) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $doctors = $query->latest()->paginate(10);
        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $centers = Center::all();
        return view('admin.doctors.create', compact('centers'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'speciality'=> 'required|string|max:255',

            'phone' => 'required|string|max:255',
            'center_id' => 'required|exists:centers,id'
        ]);

        Doctor::create($validatedData);
        return redirect()->route('admin.doctors.index')->with(['success' => 'تم الإضافة بنجاح']);
    }

    /**
     * عرض الملف الشامل للطبيب (Profile)
     */
    public function show(Doctor $doctor)
    {

        $doctor->load(['center.province', 'deals.pharmacists']);

        // 2. الفواتير
        $invoices = $doctor->invoices()
            ->with(['pharmacist', 'representative'])
            ->latest()
            ->paginate(10);

        // 3. الحسابات المالية
        $totalSales = $doctor->invoices()->sum('final_total');

        // إجمالي العمولات التاريخية من الفواتير
        $totalCommission = $doctor->invoices()->get()->reduce(function ($carry, $inv) {
            return $carry + ($inv->final_total * ($inv->doctor_commission_percentage / 100));
        }, 0);

        // العمولات المدفوعة من الفواتير
        $paidInvoicesCommission = $doctor->invoices()->where('doctor_commission_paid', true)->get()->reduce(function ($carry, $inv) {
            return $carry + ($inv->final_total * ($inv->doctor_commission_percentage / 100));
        }, 0);

        // العمولات المدفوعة من الاتفاقات (Deals)
        $dealsPaidAmount = $doctor->deals()->sum('paid_amount');

        // الإجمالي المدفوع الكلي
        $paidCommission = $paidInvoicesCommission + $dealsPaidAmount;

        // 4. اتفاقات التارجت
        $activeDeals = $doctor->deals()->where('is_paid', false)->get();
        $completedDealsCount = $doctor->deals()->where('is_paid', true)->count();

        // 5. المخاطرة والمستحق
        $pendingInvoiceCommission = $totalCommission - $paidInvoicesCommission;

        // "مخاطرة المقدم" هي المبالغ التي دُفعت في اتفاقات لم تنتهِ بعد
        $activePrepaidRisk = $doctor->deals()
            ->where('is_paid', false) // اتفاقات ما زالت سارية
            ->sum('paid_amount');     // المبلغ المدفوع فيها يعتبر مخاطرة حتى يكتمل التارجت

        $prepaidRisk = $activePrepaidRisk;
        $pendingCommission = $pendingInvoiceCommission;

        return view('admin.doctors.show', compact(
            'doctor', 'invoices', 'totalSales', 'totalCommission', 'paidCommission',
            'activeDeals', 'completedDealsCount', 'prepaidRisk', 'pendingCommission'
        ));
    }

    public function edit(Doctor $doctor)
    {
        $centers = Center::all();
        return view('admin.doctors.edit', compact('doctor', 'centers'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'speciality'=> 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'center_id' => 'required|exists:centers,id'
        ]);

        $doctor->update($validatedData);
        return redirect()->route('admin.doctors.index')->with(['success' => 'تم التعديل بنجاح']);
    }

    public function destroy(Doctor $doctor)
    {
        try {
            $doctor->delete();
            return redirect()->route('admin.doctors.index')->with(['success' => 'تم الحذف بنجاح']);
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
}
