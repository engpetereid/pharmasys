<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pharmacist;
use App\Models\Doctor;
use App\Models\Representative;
use App\Models\Zone;
use App\Models\ZoneExpense; // إضافة موديل المصروفات
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. تحديد التاريخ المختار (الافتراضي هو الشهر والسنة الحالية)
        $selectedMonth = $request->input('month', Carbon::now()->month);
        $selectedYear = $request->input('year', Carbon::now()->year);

        // تحديد بداية ونهاية الشهر المختار للفلترة
        $startOfSelectedMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endOfSelectedMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();

        // 2. الإحصائيات
        // مبيعات اليوم
        $todaySales = Invoice::whereDate('invoice_date', Carbon::today())->sum('final_total');

        // مبيعات الشهر المختار (المتغير الرئيسي)
        $monthSales = Invoice::whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->sum('final_total');

        // إجمالي المستحقات (تراكمي )
        $totalDue = Invoice::sum('remaining_amount');

        // 3. المناطق الخطره (تطبيق المنطق الجديد: خصومات + مصروفات)
        $zones = Zone::with('centers')->get();

        $riskyZones = $zones->map(function ($zone) use ($startOfSelectedMonth, $endOfSelectedMonth) {
            $centerIds = $zone->centers->pluck('id')->toArray();

            // الفواتير في الشهر المختار (مع التفاصيل لحساب سعر الجمهور)
            $invoices = Invoice::with('details')
                ->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth])
                ->where('line', $zone->line)
                ->whereHas('pharmacist', function ($q) use ($centerIds) {
                    $q->whereIn('center_id', $centerIds);
                })->get();

            // أ) حساب إجمالي السعر الجمهوري
            $publicPriceTotal = $invoices->reduce(function ($carry, $invoice) {
                return $carry + $invoice->details->reduce(function ($subCarry, $detail) {
                        return $subCarry + ($detail->unit_price * $detail->quantity);
                    }, 0);
            }, 0);

            // ب) حساب قيمة خصم الصيدليات
            $totalDiscountValue = $invoices->sum('total_discount');

            // ج) حساب مصروفات المنطقة
            $totalZoneExpenses = $zone->expenses()
                ->whereBetween('expense_date', [$startOfSelectedMonth, $endOfSelectedMonth])
                ->sum('amount');

            // د) حساب النسبة الجديدة
            $riskRatio = 0;
            if ($publicPriceTotal > 0) {
                // المعادلة: (نسبة الخصم) + (نسبة المصروفات)
                $avgDiscountPercentage = ($totalDiscountValue / $publicPriceTotal) * 100;
                $expenseRatio = ($totalZoneExpenses / $publicPriceTotal) * 100;
                $riskRatio = $avgDiscountPercentage + $expenseRatio;
            }

            return [
                'id' => $zone->id,
                'name' => $zone->name,
                'line' => $zone->line,
                'ratio' => round($riskRatio, 1),
                // القيمة هنا تعبر عن (إجمالي الخصم + المصروفات) كقيمة مالية
                'risk_amount' => $totalDiscountValue + $totalZoneExpenses
            ];
        })->filter(function ($item) {
            return $item['ratio'] > 40; // فلتر المناطق التي تتجاوز 40%
        })->values();

        //  بناءً على الشهر المختار
        $topPharmacists = Pharmacist::with('center')
            ->withSum(['invoices as total_sales' => function($q) use ($startOfSelectedMonth, $endOfSelectedMonth) {
                $q->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth]);
            }], 'final_total')
            ->orderByDesc('total_sales')->take(5)->get();

        $topDoctors = Doctor::with('center')
            ->withSum(['invoices as total_sales' => function($q) use ($startOfSelectedMonth, $endOfSelectedMonth) {
                $q->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth]);
            }], 'final_total')
            ->orderByDesc('total_sales')->take(5)->get();

        $topRepresentatives = Representative::withSum(['invoices as total_sales' => function($q) use ($startOfSelectedMonth, $endOfSelectedMonth) {
            $q->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth]);
        }], 'final_total')
            ->orderByDesc('total_sales')->take(5)->get();

        //  للشهر المختار
        $latestInvoices = Invoice::with(['pharmacist', 'doctors'])
            ->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->latest()
            ->take(6)
            ->get();

        // الفواتير المتعثرة والخصومات عامة لأنها تراكمية
        // الفواتير المتعثرة والخصومات
        $overdueInvoices = Invoice::with(['pharmacist', 'representative'])
            ->where('remaining_amount', '>', 0)
            ->whereDate('invoice_date', '<', Carbon::now()->subMonths(3))
            ->orderBy('invoice_date')->take(10)->get();

        $highDiscountPharmacists = Pharmacist::whereHas('invoices', function($q) {
            // حساب نسبة الخصم للفاتورة الكلية: (إجمالي الخصم / إجمالي الفاتورة) * 100 >= 51%
            $q->whereRaw('total_amount > 0 AND (total_discount / total_amount) * 100 >= 51');
        })
            ->with('center')
            ->withCount(['invoices as high_discount_invoices_count' => function($q) {
                $q->whereRaw('total_amount > 0 AND (total_discount / total_amount) * 100 >= 51');
            }])
            ->orderByDesc('high_discount_invoices_count')->take(10)->get();

        return view('admin.dashboard', compact(
            'selectedMonth', 'selectedYear', // نرسل القيم المختارة للفيو
            'todaySales', 'monthSales', 'totalDue',
            'riskyZones', 'topPharmacists', 'topDoctors', 'topRepresentatives',
            'latestInvoices', 'overdueInvoices', 'highDiscountPharmacists'
        ));
    }


    public function lineDashboard(Request $request, $lineId)
    {
        if (!in_array($lineId, [1, 2])) abort(404);

        //  تحديد التاريخ المختار
        $selectedMonth = $request->input('month', Carbon::now()->month);
        $selectedYear = $request->input('year', Carbon::now()->year);

        $startOfSelectedMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endOfSelectedMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();

        // الإحصائيات
        $todaySales = Invoice::where('line', $lineId)
            ->whereDate('invoice_date', Carbon::today())
            ->sum('final_total');

        $monthSales = Invoice::where('line', $lineId)
            ->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->sum('final_total');

        $totalDue = Invoice::where('line', $lineId)->sum('remaining_amount');

        $zonesCount = Zone::where('line', $lineId)->count();
        $representativesCount = Zone::where('line', $lineId)
            ->selectRaw('count(distinct sales_representative_id) + count(distinct medical_representative_id) as total')
            ->value('total');

        //  القوائم مفلترة بالتاريخ
        $topPharmacists = Pharmacist::with('center')
            ->withSum(['invoices as total_sales' => function($query) use ($lineId, $startOfSelectedMonth, $endOfSelectedMonth) {
                $query->where('line', $lineId)->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth]);
            }], 'final_total')
            ->orderByDesc('total_sales')->take(5)->get();

        $topDoctors = Doctor::with('center')
            ->withSum(['invoices as total_sales' => function($query) use ($lineId, $startOfSelectedMonth, $endOfSelectedMonth) {
                $query->where('line', $lineId)->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth]);
            }], 'final_total')
            ->orderByDesc('total_sales')->take(5)->get();

        //  المخاطرة للخط بالكامل (المنطق الجديد)
        $lineInvoices = Invoice::with('details')
            ->where('line', $lineId)
            ->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->get();

        // 1. إجمالي الجمهوري للخط
        $publicPriceTotal = $lineInvoices->reduce(function ($carry, $invoice) {
            return $carry + $invoice->details->reduce(function ($subCarry, $detail) {
                    return $subCarry + ($detail->unit_price * $detail->quantity);
                }, 0);
        }, 0);

        // 2. إجمالي الخصومات للخط
        $totalDiscountValue = $lineInvoices->sum('total_discount');

        // 3. إجمالي مصروفات المناطق التابعة للخط
        $zonesInLineIds = Zone::where('line', $lineId)->pluck('id');
        $totalLineExpenses = ZoneExpense::whereIn('zone_id', $zonesInLineIds)
            ->whereBetween('expense_date', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->sum('amount');

        // 4. حساب النسبه
        $riskRatio = 0;
        if ($publicPriceTotal > 0) {
            $avgDiscountPercentage = ($totalDiscountValue / $publicPriceTotal) * 100;
            $expenseRatio = ($totalLineExpenses / $publicPriceTotal) * 100;
            $riskRatio = $avgDiscountPercentage + $expenseRatio;
        }

        //  آخر الفواتير للشهر المختار
        $latestInvoices = Invoice::where('line', $lineId)
            ->whereBetween('invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->with(['pharmacist', 'doctors']) // تم تصحيح doctor إلى doctors
            ->latest()
            ->take(6)
            ->get();

        return view('admin.line_dashboard', compact(
            'lineId', 'selectedMonth', 'selectedYear',
            'todaySales', 'monthSales', 'totalDue', 'zonesCount',
            'representativesCount', 'topPharmacists', 'topDoctors', 'riskRatio', 'latestInvoices'
        ));
    }
}
