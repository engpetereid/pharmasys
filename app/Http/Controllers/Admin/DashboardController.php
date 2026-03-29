<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pharmacist;
use App\Models\Doctor;
use App\Models\Representative;
use App\Models\Zone;
use App\Models\ZoneExpense;
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

        // 3. المناطق الخطرة – حساب واحد في DB بدلاً من query لكل منطقة
        // جلب إجمالي سعر الجمهور + الخصم لكل منطقة في استعلام واحد
        $invoiceTotals = DB::table('zones')
            ->join('center_zone', 'center_zone.zone_id', '=', 'zones.id')
            ->join('pharmacists', 'pharmacists.center_id', '=', 'center_zone.center_id')
            ->join('invoices', function ($join) use ($startOfSelectedMonth, $endOfSelectedMonth) {
                $join->on('invoices.pharmacist_id', '=', 'pharmacists.id')
                     ->on('invoices.line', '=', 'zones.line')
                     ->whereBetween('invoices.invoice_date', [$startOfSelectedMonth, $endOfSelectedMonth]);
            })
            ->join('invoice_details', 'invoice_details.invoice_id', '=', 'invoices.id')
            ->groupBy('zones.id')
            ->select(
                'zones.id as zone_id',
                DB::raw('SUM(invoice_details.unit_price * invoice_details.quantity) as public_price_total'),
                DB::raw('SUM(invoices.total_discount) as total_discount')
            )
            ->get()
            ->keyBy('zone_id');

        // جلب مصروفات المناطق في استعلام واحد
        $expenseTotals = DB::table('zone_expenses')
            ->whereBetween('expense_date', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->groupBy('zone_id')
            ->select('zone_id', DB::raw('SUM(amount) as total_expenses'))
            ->get()
            ->keyBy('zone_id');

        $zones = Zone::select('id', 'name', 'line')->get();

        $riskyZones = $zones->map(function ($zone) use ($invoiceTotals, $expenseTotals) {
            $totals          = $invoiceTotals->get($zone->id);
            $publicPriceTotal= $totals ? (float) $totals->public_price_total : 0;
            $totalDiscount   = $totals ? (float) $totals->total_discount     : 0;
            $totalExpenses   = $expenseTotals->has($zone->id)
                ? (float) $expenseTotals->get($zone->id)->total_expenses
                : 0;

            $riskRatio = 0;
            if ($publicPriceTotal > 0) {
                $riskRatio = (($totalDiscount + $totalExpenses) / $publicPriceTotal) * 100;
            }

            return [
                'id'          => $zone->id,
                'name'        => $zone->name,
                'line'        => $zone->line,
                'ratio'       => round($riskRatio, 1),
                'risk_amount' => $totalDiscount + $totalExpenses,
            ];
        })->filter(fn($item) => $item['ratio'] > 40)->values();

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
