<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ZoneReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $zoneId = $request->input('zone_id');
        $line = $request->input('line');

        $zonesQuery = Zone::with(['centers', 'province']);

        if ($zoneId) {
            $zonesQuery->where('id', $zoneId);
        }

        if ($line) {
            $zonesQuery->where('line', $line);
        }

        $zones = $zonesQuery->get();

        $reportData = $this->calculateReportData($zones, $startDate, $endDate);

        $allZonesQuery = Zone::select('id', 'name','line');
        if ($line) {
            $allZonesQuery->where('line', $line);
        }
        $allZones = $allZonesQuery->get();

        return view('admin.reports.zone_risk', compact('reportData', 'allZones', 'startDate', 'endDate'));
    }

    public function show(Request $request, $id)
    {
        return redirect()->route('admin.zones.show', $id);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $line = $request->input('line');

        $zonesQuery = Zone::with(['centers', 'province']);

        if ($line) {
            $zonesQuery->where('line', $line);
        }

        $zones = $zonesQuery->get();
        $data = $this->calculateReportData($zones, $startDate, $endDate);

        $filename = "risk_report_new_" . date('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['المنطقة', 'المحافظة', 'الخط', 'إجمالي المبيعات (جمهوري)', 'إجمالي المصروفات', 'متوسط الخصم %', 'نسبة المصروفات %', 'نسبة الجهاز الكلية %', 'الحالة']);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row['zone_name'],
                    $row['province_name'],
                    'Line ' . $row['line'],
                    $row['public_price'],
                    $row['total_expenses'],
                    $row['avg_discount_percentage'] . '%',
                    $row['expense_ratio'] . '%',
                    $row['risk_ratio'] . '%',
                    $row['status'] == 'danger' ? 'خطر' : 'آمن'
                ]);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }


    private function calculateReportData($zones, $startDate, $endDate)
    {
        return $zones->map(function ($zone) use ($startDate, $endDate) {
            $centerIds = $zone->centers->pluck('id')->toArray();

            //  الفواتير في الفترة المحددة
            $invoices = Invoice::with(['details'])
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->where('line', $zone->line)
                ->whereHas('pharmacist', function ($q) use ($centerIds) {
                    $q->whereIn('center_id', $centerIds);
                })->get();

            // حساب إجمالي السعر الجمهوري قبل أي خصم
            $publicPriceTotal = $invoices->reduce(function ($carry, $invoice) {
                return $carry + $invoice->details->reduce(function ($subCarry, $detail) {
                        return $subCarry + ($detail->unit_price * $detail->quantity);
                    }, 0);
            }, 0);

            // حساب قيمة الخصم الممنوح للصيدليات
            $totalDiscountValue = $invoices->sum('total_discount');

            //  حساب مصروفات المنطقة في نفس الفترة
            $totalZoneExpenses = $zone->expenses()
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount');

            //  الحسابات والنسب

            $avgDiscountPercentage = 0;
            $expenseRatio = 0;
            $riskRatio = 0;

            if ($publicPriceTotal > 0) {
                // المعادلة: (إجمالي قيمة الخصم / إجمالي السعر الجمهوري) * 100
                $avgDiscountPercentage = ($totalDiscountValue / $publicPriceTotal) * 100;

                // المعادلة: (إجمالي المصروفات / إجمالي السعر الجمهوري) * 100
                $expenseRatio = ($totalZoneExpenses / $publicPriceTotal) * 100;

                // 3. نسبة الجهاز النهائية
                $riskRatio = $avgDiscountPercentage + $expenseRatio;
            }

            return [
                'id' => $zone->id,
                'zone_name' => $zone->name,
                'province_name' => $zone->province->name,
                'line' => $zone->line,

                // القيم المالية
                'public_price' => $publicPriceTotal,
                'total_discount_value' => $totalDiscountValue,
                'total_expenses' => $totalZoneExpenses,

                // النسب المئوية
                'avg_discount_percentage' => round($avgDiscountPercentage, 2),
                'expense_ratio' => round($expenseRatio, 2),
                'risk_ratio' => round($riskRatio, 2),

                // الحالة
                'status' => $riskRatio > 40 ? 'danger' : 'safe'
            ];
        });
    }
}
