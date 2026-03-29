<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorDeal;
use App\Models\Doctor;
use App\Models\Pharmacist;
use App\Models\Drug;
use App\Models\Zone;
use App\Models\ZoneExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorDealController extends Controller
{
    private function getFilteredDealsQuery(Request $request)
    {
        $query = DoctorDeal::with(['doctor', 'pharmacists', 'drugs']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('doctor', function ($d) use ($searchTerm) {
                    $d->where('name', 'like', '%' . $searchTerm . '%');
                })
                    ->orWhereHas('pharmacists', function ($p) use ($searchTerm) {
                        $p->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('zone_id')) {
            $query->whereHas('pharmacists.center.zones', function ($q) use ($request) {
                $q->where('zones.id', $request->zone_id);
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->getFilteredDealsQuery($request);

        $query->where('is_archived', false);

        $zones = Zone::where('line', 1)->get();
        $deals = $query->latest()->paginate(12);
        $deals->appends($request->all());

        return view('admin.deals.index', compact('deals', 'zones'));
    }

    public function archived(Request $request)
    {
        $query = $this->getFilteredDealsQuery($request);

        $query->where('is_archived', true);

        $zones = Zone::where('line', 1)->get();
        $deals = $query->latest()->paginate(12);
        $deals->appends($request->all());

        return view('admin.deals.index', compact('deals', 'zones'));
    }

    public function create()
    {
        $doctors = Doctor::select('id', 'name', 'center_id')->get();
        $pharmacists = Pharmacist::select('id', 'name', 'center_id')->with('center')->get();
        $drugs = Drug::select('id', 'name', 'line', 'price')->get();

        return view('admin.deals.create', compact('doctors', 'pharmacists', 'drugs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'target_amount' => 'nullable|numeric|min:1',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'status' => 'required|in:1,2,3',
            'paid_amount' => 'nullable|numeric|min:0',
            'pharmacists' => 'required|array|min:1',
            'pharmacists.*' => 'exists:pharmacists,id',
            'drugs' => 'nullable|array',
            'drugs.*' => 'exists:drugs,id',
        ]);

        try {
            DB::beginTransaction();

            $paidAmount = 0;
            if ($request->status == 1) {
                $paidAmount = $request->commission_amount;
            } elseif ($request->status == 3) {
                $paidAmount = $request->paid_amount ?? 0;
            }

            $deal = DoctorDeal::create([
                'doctor_id' => $request->doctor_id,
                'target_amount' => $request->target_amount,
                'commission_percentage' => $request->commission_percentage,
                'commission_amount' => $request->commission_amount,
                'start_date' => $request->start_date,
                'status' => $request->status,
                'paid_amount' => $paidAmount,
                'is_paid' => ($request->status == 1),
                'achieved_amount' => 0,
            ]);

            $deal->pharmacists()->sync($request->pharmacists);

            if ($request->has('drugs')) {
                $deal->drugs()->sync($request->drugs);
            }

            // تسجيل الدفعة كمصروف للمنطقة
            if ($paidAmount > 0) {
                $this->recordDealExpense($deal, $paidAmount);
            }

            DB::commit();
            return redirect()->route('admin.deals.index')->with(['success' => 'تم تسجيل الاتفاق المالي بنجاح']);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(DoctorDeal $deal)
    {
        $doctors = Doctor::select('id', 'name', 'center_id')->get();

        $pharmacists = Pharmacist::select('id', 'name', 'center_id')->with('center')->get();
        $drugs = Drug::select('id', 'name', 'line')->get();
        $deal->load(['pharmacists', 'drugs', 'doctor']); // تحميل الطبيب أيضاً

        return view('admin.deals.edit', compact('deal', 'doctors', 'pharmacists', 'drugs'));
    }

    public function update(Request $request, DoctorDeal $deal)
    {
        $request->validate([
            'status' => 'required|in:1,2,3',
            'paid_amount' => 'nullable|numeric',
            'pharmacists' => 'required|array|min:1',
            'commission_percentage'=>'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $oldPaidAmount = $deal->paid_amount;
            $paidAmount = $deal->paid_amount;
            $target_amount = $request->target_amount;

            if ($request->status == 1) $paidAmount = $request->commission_amount;
            elseif ($request->status == 3) $paidAmount = $request->paid_amount;
            elseif ($request->status == 2) $paidAmount = 0;

            // حساب الفرق لضمان تسجيل المصروف الجديد فقط
            $diff = $paidAmount - $oldPaidAmount;

            $deal->update([
                'status' => $request->status,
                'target_amount' => $target_amount,
                'commission_amount' => $request->commission_amount,
                'commission_percentage' => $request->commission_percentage,
                'paid_amount' => $paidAmount,
                'is_paid' => ($request->status == 1),
                'start_date' => $request->start_date,
            ]);

            $deal->pharmacists()->sync($request->pharmacists);

            if ($request->has('drugs')) {
                $deal->drugs()->sync($request->drugs);
            } else {
                $deal->drugs()->detach();
            }

            // تسجيل الزيادة في الدفع كمصروف
            if ($diff > 0) {
                $this->recordDealExpense($deal, $diff);
            }

            DB::commit();
            return redirect()->route('admin.deals.index')->with(['success' => 'تم تحديث الاتفاق']);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }

    public function destroy(DoctorDeal $deal)
    {
        $deal->delete();
        return redirect()->route('admin.deals.index')->with(['success' => 'تم حذف الاتفاق']);
    }

    public function markAsPaid(DoctorDeal $deal)
    {
        $diff = $deal->commission_amount - $deal->paid_amount;

        $deal->update([
            'is_paid' => true,
            'status' => 1,
            'paid_amount' => $deal->commission_amount
        ]);

        // تسجيل المبلغ المتبقي كمصروف منطقة
        if ($diff > 0) {
            $this->recordDealExpense($deal, $diff);
        }

        return redirect()->back()->with(['success' => 'تم تسوية الاتفاق ودفع المبلغ المتبقي للدكتور.']);
    }

    public function showInvoices(DoctorDeal $deal)
    {
        // 1. الفواتير المحصلة (المدفوعة) التي تم احتسابها بالفعل ضمن التارجت
        $paidInvoices = $deal->invoices()
            ->with(['representative', 'medicalRepresentative', 'pharmacist', 'details'])
            ->latest()
            ->paginate(15);

        $dealDrugIds = $deal->drugs->pluck('id')->toArray();
        $isGeneralDeal = empty($dealDrugIds);

        // 2. الفواتير الآجلة أو المدفوعة جزئياً المرتبطة بهذا الطبيب وهذه الصيدليات
        $pharmacistIds = $deal->pharmacists->pluck('id')->toArray();

        $unpaidQuery = \App\Models\Invoice::with(['representative', 'pharmacist', 'details'])
            ->whereHas('doctors', function($q) use ($deal) {
                $q->where('doctors.id', $deal->doctor_id);
            })
            ->whereIn('pharmacist_id', $pharmacistIds)
            ->whereIn('status', [2, 3]) // 2: آجل, 3: جزئي
            ->whereDate('invoice_date', '>=', $deal->start_date)
            ->latest()
            ->get();

        $unpaidTotalContribution = 0;
        $unpaidInvoicesList = collect();

        // حساب القيمة المتوقعة من هذه الفواتير بناءً على أدوية الاتفاق فقط
        foreach ($unpaidQuery as $inv) {
            $invContribution = 0;
            foreach ($inv->details as $detail) {
                if ($isGeneralDeal || in_array($detail->drug_id, $dealDrugIds)) {
                    $invContribution += $detail->row_total;
                }
            }

            // إذا كانت الفاتورة تحتوي على أدوية ضمن الاتفاق
            if ($invContribution > 0) {
                $inv->potential_contribution = $invContribution;
                $unpaidTotalContribution += $invContribution;
                $unpaidInvoicesList->push($inv);
            }
        }

        // حساب عمولة الطبيب المتوقعة من هذه الفواتير عند تحصيلها
        $potentialCommission = $unpaidTotalContribution * ($deal->commission_percentage / 100);

        return view('admin.deals.invoices', compact(
            'deal',
            'paidInvoices',
            'unpaidInvoicesList',
            'unpaidTotalContribution',
            'potentialCommission',
            'dealDrugIds',
            'isGeneralDeal'
        ));
    }

    public function toggleActive(DoctorDeal $deal)
    {
        $deal->update(['is_active' => !$deal->is_active]);

        $statusMsg = $deal->is_active ? 'استئناف الاتفاق' : 'إيقاف الاتفاق';
        return redirect()->back()->with(['success' => "تم $statusMsg بنجاح"]);
    }

    public function toggleArchive(DoctorDeal $deal)
    {
        $newArchiveStatus = !$deal->is_archived;

        $updates = ['is_archived' => $newArchiveStatus];

        if ($newArchiveStatus == true) {
            $updates['is_active'] = false;
        } else {
            $updates['is_active'] = true;
        }

        $deal->update($updates);

        $statusMsg = $newArchiveStatus ? 'أرشفة الاتفاق' : 'إلغاء أرشفة الاتفاق';
        return redirect()->back()->with(['success' => "تم $statusMsg بنجاح"]);
    }

    /**
     * دالة مساعدة لتسجيل الدفعات كمصروف في المنطقة التابع لها الطبيب تلقائياً
     */
    private function recordDealExpense(DoctorDeal $deal, $amount)
    {
        if ($amount <= 0) return;

        $doctor = Doctor::find($deal->doctor_id);
        if (!$doctor || !$doctor->center_id) return;

        // تحديد خط السير بناءً على أدوية الاتفاق (أو الخط الأول افتراضياً)
        $line = 1;
        $firstDrug = $deal->drugs()->first();
        if ($firstDrug) {
            $line = $firstDrug->line;
        }

        // إيجاد المنطقة التابعة لمركز الطبيب والتي توافق خط السير
        $zone = Zone::where('line', $line)->whereHas('centers', function($q) use ($doctor) {
            $q->where('centers.id', $doctor->center_id);
        })->first();

        // في حال لم نجد منطقة مطابقة للخط، نجلب أي منطقة مربوطة بمركز الطبيب
        if (!$zone) {
            $zone = Zone::whereHas('centers', function($q) use ($doctor) {
                $q->where('centers.id', $doctor->center_id);
            })->first();
        }

        // إذا تم العثور على منطقة نقوم بتسجيل المصروف
        if ($zone) {
            ZoneExpense::create([
                'zone_id' => $zone->id,
                'amount' => $amount,
                'description' => 'دفعة من اتفاق مالي (عمولة) للطبيب: ' . $doctor->name,
                'expense_date' => now()->format('Y-m-d'),
            ]);
        }
    }
}
