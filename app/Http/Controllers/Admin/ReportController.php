<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Center;
use App\Models\Pharmacist;
use App\Models\Doctor;
use App\Models\Representative;
use App\Models\Invoice;
use App\Models\DoctorDeal; // إضافة موديل الاتفاقات
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $provinces = Province::select('provinces.id', 'provinces.name')
            ->leftJoin('centers', 'centers.province_id', '=', 'provinces.id')
            ->leftJoin('pharmacists', 'pharmacists.center_id', '=', 'centers.id')
            ->leftJoin('invoices', function($join) use ($request) {
                $join->on('invoices.pharmacist_id', '=', 'pharmacists.id');
                if ($request->has('line')) {
                    $join->where('invoices.line', $request->line);
                }
            })
            ->selectRaw('COALESCE(SUM(invoices.final_total), 0) as total_sales')
            ->selectRaw('COUNT(DISTINCT centers.id) as centers_count')
            ->groupBy('provinces.id', 'provinces.name')
            ->orderByDesc('total_sales')
            ->get();

        return view('admin.reports.index', compact('provinces'));
    }

    public function showProvince($id)
    {
        $province = Province::findOrFail($id);

        $centers = Center::where('province_id', $id)
            ->select('centers.id', 'centers.name')
            ->leftJoin('pharmacists', 'pharmacists.center_id', '=', 'centers.id')
            ->leftJoin('invoices', 'invoices.pharmacist_id', '=', 'pharmacists.id')
            ->selectRaw('COALESCE(SUM(invoices.final_total), 0) as total_sales')
            ->selectRaw('COUNT(DISTINCT pharmacists.id) as pharmacists_count')
            ->groupBy('centers.id', 'centers.name')
            ->orderByDesc('total_sales')
            ->get();

        $totalSales = $centers->sum('total_sales');

        return view('admin.reports.province', compact('province', 'centers', 'totalSales'));
    }


    public function showCenter($id)
    {
        $center = Center::with('province')->findOrFail($id);

        $pharmacists = Pharmacist::where('center_id', $id)
            ->select('pharmacists.*')
            ->leftJoin('invoices', 'invoices.pharmacist_id', '=', 'pharmacists.id')
            ->selectRaw('COALESCE(SUM(invoices.final_total), 0) as total_sales')
            ->selectRaw('COUNT(invoices.id) as invoices_count')
            ->groupBy('pharmacists.id')
            ->orderByDesc('total_sales')
            ->get();

        $totalSales = $pharmacists->sum('total_sales');

        return view('admin.reports.center', compact('center', 'pharmacists', 'totalSales'));
    }

    public function showPharmacist($id)
    {
        $pharmacist = Pharmacist::with(['center.province'])->findOrFail($id);

        $invoices = $pharmacist->invoices()
            ->with(['doctors', 'representative'])
            ->latest()
            ->paginate(20);

        $totalSales = $pharmacist->invoices()->sum('final_total');
        $totalPaid = $pharmacist->invoices()->sum('paid_amount');
        $totalDue = $pharmacist->invoices()->sum('remaining_amount');

        return view('admin.reports.pharmacist', compact('pharmacist', 'invoices', 'totalSales', 'totalPaid', 'totalDue'));
    }


    public function doctorsIndex(Request $request)
    {
        $provinces = Province::select('provinces.id', 'provinces.name')
            ->leftJoin('centers', 'centers.province_id', '=', 'provinces.id')
            ->leftJoin('doctors', 'doctors.center_id', '=', 'centers.id')
            // تعديل لربط جدول الفواتير مع جدول الأطباء عبر الجدول الوسيط
            ->leftJoin('doctor_invoice', 'doctor_invoice.doctor_id', '=', 'doctors.id')
            ->leftJoin('invoices', function($join) use ($request) {
                $join->on('invoices.id', '=', 'doctor_invoice.invoice_id');
                if ($request->has('line')) {
                    $join->where('invoices.line', $request->line);
                }
            })
            ->selectRaw('COUNT(DISTINCT doctors.id) as doctors_count')
            ->selectRaw('COALESCE(SUM(invoices.final_total), 0) as total_sales')
            ->groupBy('provinces.id', 'provinces.name')
            ->orderByDesc('total_sales')
            ->get();

        return view('admin.reports.doctors.index', compact('provinces'));
    }

    public function showDoctorProvince($id)
    {
        $province = Province::findOrFail($id);

        $centers = Center::where('province_id', $id)
            ->select('centers.id', 'centers.name')
            ->leftJoin('doctors', 'doctors.center_id', '=', 'centers.id')
            // التعديل هنا للربط عبر الجدول الوسيط
            ->leftJoin('doctor_invoice', 'doctor_invoice.doctor_id', '=', 'doctors.id')
            ->leftJoin('invoices', 'invoices.id', '=', 'doctor_invoice.invoice_id')
            ->selectRaw('COUNT(DISTINCT doctors.id) as doctors_count')
            ->selectRaw('COALESCE(SUM(invoices.final_total), 0) as total_sales')
            ->groupBy('centers.id', 'centers.name')
            ->orderByDesc('total_sales')
            ->get();

        return view('admin.reports.doctors.province', compact('province', 'centers'));
    }

    public function showDoctorCenter($id)
    {
        $center = Center::with('province')->findOrFail($id);

        $doctors = Doctor::where('center_id', $id)
            ->withSum('invoices as total_sales', 'final_total')
            ->with('deals') // جلب الاتفاقات لحساب العمولات
            ->get()
            ->map(function ($doctor) {
                // حساب العمولة المستحقة غير المدفوعة من الاتفاقات بدلاً من الفواتير المحذوفة أعمدتها
                $unpaidCommission = $doctor->deals->where('is_paid', false)->sum(function ($deal) {
                    return max(0, $deal->commission_amount - $deal->paid_amount);
                });

                $doctor->unpaid_commission = $unpaidCommission;
                return $doctor;
            });

        return view('admin.reports.doctors.center', compact('center', 'doctors'));
    }


    public function showDoctor($id)
    {
        $doctor = Doctor::with(['center.province', 'deals'])->findOrFail($id);

        $invoices = $doctor->invoices()->latest()->paginate(20);

        $totalSales = $doctor->invoices()->sum('final_total');

        // الاعتماد على جدول الاتفاقات (Deals) لحساب عمولات الطبيب
        $totalCommissionEarned = $doctor->deals->sum('commission_amount');
        $paidCommission = $doctor->deals->sum('paid_amount');
        $dueCommission = max(0, $totalCommissionEarned - $paidCommission);

        return view('admin.reports.doctors.show', compact(
            'doctor', 'invoices', 'totalSales', 'totalCommissionEarned', 'paidCommission', 'dueCommission'
        ));
    }


    public function payDoctorCommission(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        // تسوية جميع الاتفاقات غير المدفوعة للطبيب
        $deals = $doctor->deals()->where('is_paid', false)->get();
        $updatedCount = 0;

        foreach ($deals as $deal) {
            $deal->update([
                'is_paid' => true,
                'status' => 1,
                'paid_amount' => $deal->commission_amount
            ]);
            $updatedCount++;
        }

        if ($updatedCount > 0) {
            return redirect()->back()->with(['success' => "تم تسوية المستحقات بنجاح لـ $updatedCount اتفاق مالي للطبيب."]);
        } else {
            return redirect()->back()->with(['error' => 'لا توجد مستحقات أو اتفاقات غير مسددة لتسويتها.']);
        }
    }


    public function representativesIndex(Request $request)
    {
        $query = Representative::query();
        $query->withSum(['invoices as total_sales' => function($q) use ($request) {
            if ($request->has('line') && ($request->line == 1 || $request->line == 2)) {
                $q->where('line', $request->line);
            }
        }], 'final_total');
        $query->withCount(['invoices' => function($q) use ($request) {
            if ($request->has('line') && ($request->line == 1 || $request->line == 2)) {
                $q->where('line', $request->line);
            }
        }]);

        if ($request->has('line') && ($request->line == 1 || $request->line == 2)) {
            $query->whereHas('invoices', function($q) use ($request) {
                $q->where('line', $request->line);
            });
        }

        $representatives = $query->orderByDesc('total_sales')->paginate(10);

        $representatives->appends($request->all());

        return view('admin.reports.representatives.index', compact('representatives'));
    }


    public function showRepresentative($id)
    {
        $representative = Representative::findOrFail($id);

        $invoices = $representative->invoices()
            ->with(['pharmacist.center', 'doctors']) // تعديل من doctor إلى doctors
            ->latest()
            ->paginate(20);

        $totalSales = $representative->invoices()->sum('final_total');
        $totalCollected = $representative->invoices()->sum('paid_amount');

        return view('admin.reports.representatives.show', compact('representative', 'invoices', 'totalSales', 'totalCollected'));
    }
}
