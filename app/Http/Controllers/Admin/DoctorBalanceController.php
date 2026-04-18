<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Doctor;
use App\Models\Zone;
use App\Models\DoctorDeal;
use Illuminate\Http\Request;

class DoctorBalanceController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = Doctor::query()->with('center');

        if ($request->filled('zone_id')) {
            $query->whereHas('center.zones', function ($q) use ($request) {
                $q->where('zones.id', $request->zone_id);
            });
        }
        if ($request->filled('center_id')) {
            $query->whereHas('center', function ($q) use ($request) {
                $q->where('id', $request->center_id);
            });
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $query->addSelect([
            'total_earned' => DoctorDeal::selectRaw('SUM(achieved_amount * (commission_percentage / 100))')
                ->whereColumn('doctor_id', 'doctors.id')
                ->where('is_archived', false),

            'total_paid' => DoctorDeal::selectRaw('SUM(paid_amount)')
                ->whereColumn('doctor_id', 'doctors.id')
                ->where('is_archived', false)
        ]);

        return $query;
    }

    public function index(Request $request)
    {
        $zones = Zone::where('line', 1)->get();
        $centers = Center::get();

        $query = $this->buildQuery($request);

        // التحقق من طلب التصدير
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportExcel($query->get());
        }

        $doctors = $query->paginate(20)->withQueryString();

        return view('admin.reports.doctors_balance', compact('doctors', 'zones', 'centers'));
    }

    private function exportExcel($doctors)
    {
        $filename = "doctors_balance_" . date('Y-m-d') . ".csv";

        $callback = function () use ($doctors) {
            $file = fopen('php://output', 'w');

            // إضافة BOM لدعم اللغة العربية في Excel
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['تقرير الأرصدة المالية للأطباء']);
            fputcsv($file, []); // سطر فارغ

            // ترويسة الجدول
            fputcsv($file, ['الطبيب', 'المركز', 'إجمالي المبيعات', 'مسحوبات / مقدمات', 'الرصيد الصافي', 'الحالة (دائن / مدين)']);

            foreach ($doctors as $doctor) {
                $credit = $doctor->total_earned ?? 0;
                $debit  = $doctor->total_paid ?? 0;
                $net    = $credit - $debit;

                $status = 'خالص';
                if ($net > 0) {
                    $status = 'له (دائن)';
                } elseif ($net < 0) {
                    $status = 'عليه (مدين)';
                }

                fputcsv($file, [
                    $doctor->name,
                    $doctor->center->name ?? '-',
                    $credit,
                    $debit,
                    $net,
                    $status
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
}
