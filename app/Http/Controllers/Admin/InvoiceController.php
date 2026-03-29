<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Pharmacist;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Province;
use App\Models\Representative;
use App\Models\Zone;
use App\Models\DoctorDeal;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use ArPHP\I18N\Arabic;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->getFilteredQuery($request);

        $totalsQuery = clone $query;

        $stats = [
            'total_public_sales' => $totalsQuery->sum('total_amount'),
            'total_net_sales' => $totalsQuery->sum('final_total'),
            'total_collected' => $totalsQuery->sum('paid_amount'),
        ];

        $stats['total_remaining'] = $stats['total_net_sales'] - $stats['total_collected'];

        $invoices = $query->latest('invoice_date')->paginate(20)->withQueryString();

        $centers = Center::all();
        $zones = Zone::where('line', 1)->get();

        $doctors = Doctor::select('id', 'name')->get();
        $representatives = Representative::select('id', 'name')->get();
        $pharmacists = Pharmacist::select('id', 'name')->get();

        return view('admin.invoices.index', compact('invoices', 'centers', 'stats', 'zones','doctors', 'representatives', 'pharmacists'));
    }

    public function export(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $invoices = $query->latest('invoice_date')->get();

        $filename = "invoices_report_" . date('Y-m-d') . ".csv";

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'رقم الفاتورة', 'التاريخ', 'الصيدلية', 'المركز', 'المنطقة',
                'الأطباء', 'المندوب', 'الإجمالي (جمهور)', 'الصافي', 'المدفوع', 'المتبقي', 'الحالة'
            ]);

            foreach ($invoices as $invoice) {
                $statusText = match ($invoice->status) {
                    1 => 'مدفوع',
                    2 => 'آجل',
                    3 => 'جزئي',
                    default => '-'
                };

                // تجميع أسماء الأطباء في نص واحد مفصول بفاصلة
                $doctorsNames = $invoice->doctors->pluck('name')->implode(' - ') ?: '-';

                fputcsv($file, [
                    $invoice->serial_number ?? $invoice->id,
                    $invoice->invoice_date,
                    $invoice->pharmacist->name ?? '-',
                    $invoice->pharmacist->center->name ?? '-',
                    $invoice->pharmacist->center->zone->name ?? '-',
                    $doctorsNames,
                    $invoice->representative->name ?? '-',
                    $invoice->total_amount,
                    $invoice->final_total,
                    $invoice->paid_amount,
                    $invoice->remaining_amount,
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

    public function create()
    {
        $provinces   = Province::select('id', 'name')->get();
        $pharmacists = $this->getPharmacistsForForm();
        $doctors     = [];
        $drugs       = Drug::select('id', 'name', 'price', 'line')->get();
        $today       = Carbon::now()->format('Y-m-d');

        return view('admin.invoices.create', compact('provinces', 'pharmacists', 'doctors', 'drugs', 'today'));
    }

    public function store(Request $request)
    {
        $this->validateInvoice($request);

        try {
            DB::beginTransaction();
            $zoneData = $this->resolveZoneAndWarehouse($request->pharmacist_id, $request->line);
            $warehouse = $zoneData['warehouse'];

            $invoice = Invoice::create([
                'serial_number' => $request->serial_number,
                'invoice_date' => $request->invoice_date,
                'line' => $request->line,
                'pharmacist_id' => $request->pharmacist_id,
                'representative_id' => $zoneData['sales_rep_id'],
                'medical_representative_id' => $zoneData['medical_rep_id'],
                'status' => $request->status,
                'notes' => $request->notes,
                'total_amount' => 0, 'total_discount' => 0, 'final_total' => 0, 'paid_amount' => 0, 'remaining_amount' => 0,
            ]);

            // ربط الأطباء المختارين بالفاتورة
            if ($request->has('doctor_ids') && is_array($request->doctor_ids)) {
                $invoice->doctors()->sync($request->doctor_ids);
            }

            $totals = $this->processInvoiceItems($invoice, $request->items, $warehouse, 'deduct');
            $this->updateInvoiceTotals($invoice, $totals, $request->status, $request->paid_amount);

            if ($invoice->status == 1 && $invoice->doctors()->count() > 0) {
                $invoice->load(['details', 'doctors']);
                $this->processDoctorDealImpact($invoice, 'increment');
            }

            DB::commit();
            return redirect()->route('admin.invoices.index')->with(['success' => "تم حفظ الفاتورة ({$request->serial_number}) بنجاح."]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('doctors');
        $provinces   = Province::select('id', 'name')->get();
        $pharmacists = $this->getPharmacistsForForm();
        $doctors     = [];
        $drugs       = Drug::where('line', $invoice->line)->select('id', 'name', 'price', 'line')->get();

        $invoiceDetails = $invoice->details->map(fn($detail) => [
            'id'                => $detail->id,
            'drug_id'           => $detail->drug_id,
            'drug_name_display' => $detail->drug->name,
            'unit_price'        => $detail->unit_price,
            'quantity'          => $detail->quantity,
            'discount'          => $detail->pharmacist_discount_percentage,
            'total'             => $detail->row_total,
        ]);

        return view('admin.invoices.edit', compact('invoice', 'invoiceDetails', 'provinces', 'pharmacists', 'doctors', 'drugs'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->validateInvoice($request, $invoice->id);

        try {
            DB::beginTransaction();

            $oldZoneData = $this->resolveZoneAndWarehouse($invoice->pharmacist_id, $invoice->line);
            $oldWarehouse = $oldZoneData['warehouse'];
            foreach ($invoice->details as $detail) {
                $this->updateWarehouseStock($oldWarehouse, $detail->drug_id, $detail->quantity, 'add');
            }

            // التراجع عن التارجت المضاف سابقاً للأطباء
            if ($invoice->status == 1 && $invoice->doctors()->count() > 0) {
                $this->processDoctorDealImpact($invoice, 'decrement');
            }

            $newZoneData = $this->resolveZoneAndWarehouse($request->pharmacist_id, $request->line);
            $newWarehouse = $newZoneData['warehouse'];

            $invoice->update([
                'serial_number' => $request->serial_number,
                'invoice_date' => $request->invoice_date,
                'line' => $request->line,
                'pharmacist_id' => $request->pharmacist_id,
                'representative_id' => $newZoneData['sales_rep_id'],
                'medical_representative_id' => $newZoneData['medical_rep_id'],
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // تحديث الأطباء المربوطين
            $invoice->doctors()->sync($request->doctor_ids ?? []);

            $invoice->details()->delete();
            $totals = $this->processInvoiceItems($invoice, $request->items, $newWarehouse, 'deduct');
            $this->updateInvoiceTotals($invoice, $totals, $request->status, $request->paid_amount);

            $invoice->load(['details', 'doctors']);

            // إضافة التارجت الجديد
            if ($invoice->status == 1 && $invoice->doctors()->count() > 0) {
                $this->processDoctorDealImpact($invoice, 'increment');
            }

            DB::commit();
            return redirect()->route('admin.invoices.index')->with(['success' => 'تم تعديل الفاتورة بنجاح']);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            DB::beginTransaction();
            if ($invoice->status == 1 && $invoice->doctors()->count() > 0) {
                $this->processDoctorDealImpact($invoice, 'decrement');
            }
            $zoneData = $this->resolveZoneAndWarehouse($invoice->pharmacist_id, $invoice->line);
            $warehouse = $zoneData['warehouse'];
            foreach ($invoice->details as $detail) {
                $this->updateWarehouseStock($warehouse, $detail->drug_id, $detail->quantity, 'add');
            }
            $invoice->details()->delete();
            $invoice->doctors()->detach();
            $invoice->delete();
            DB::commit();
            return redirect()->route('admin.invoices.index')->with(['success' => 'تم حذف الفاتورة']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['representative', 'medicalRepresentative', 'pharmacist.center', 'doctors', 'details.drug']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function printPdf(Invoice $invoice)
    {
        $invoice->load(['representative', 'medicalRepresentative', 'pharmacist.center', 'doctors', 'details.drug']);
        $html = view('admin.invoices.pdf', compact('invoice'))->render();
        $arabic = new Arabic();
        $p = $arabic->arIdentify($html);
        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $html = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }
        $pdf = Pdf::loadHTML($html);
        $pdf->setOption(['dpi' => 150, 'defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);
        return $pdf->stream('invoice_' . $invoice->id . '.pdf');
    }

    /**
     * Build the shared pharmacists data-structure used by create() and edit().
     * Loads centers + active non-archived deals with their doctor and drugs
     * in a single eager-loaded query, then maps to a plain array for the view.
     */
    private function getPharmacistsForForm(): \Illuminate\Support\Collection
    {
        return Pharmacist::with([
            'center',
            'deals' => fn($q) => $q->where('is_archived', false)->where('is_active', true),
            'deals.doctor',
            'deals.drugs',
        ])->get()->map(fn($ph) => [
            'id'        => $ph->id,
            'name'      => $ph->name,
            'center_id' => $ph->center_id,
            'center'    => $ph->center ? [
                'id'          => $ph->center->id,
                'name'        => $ph->center->name,
                'province_id' => $ph->center->province_id,
            ] : null,
            'deals'     => $ph->deals
                ->map(function ($deal) {
                    if ($deal->is_archived || !$deal->doctor) return null;

                    $isComplete = $deal->target_amount > 0
                        && $deal->achieved_amount >= $deal->target_amount;

                    return [
                        'id'         => $deal->id,
                        'drugs'      => $deal->drugs->pluck('id')->toArray(),
                        'is_general' => $deal->drugs->isEmpty(),
                        'doctor'     => [
                            'id'              => $deal->doctor->id,
                            'name'            => $deal->doctor->name . ($isComplete ? ' (مكتمل)' : ''),
                            'speciality'      => $deal->doctor->speciality,
                            'commission_rate' => $deal->commission_percentage,
                        ],
                    ];
                })
                ->filter()
                ->values(),
        ]);
    }

    private function getFilteredQuery(Request $request)
    {
        $query = Invoice::with(['pharmacist.center', 'doctors', 'representative']);

        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }
        if ($request->filled('center_id')) {
            $query->whereHas('pharmacist', function ($q) use ($request) {
                $q->where('center_id', $request->center_id);
            });
        }
        if ($request->filled('zone_id')) {
            $query->whereHas('pharmacist.center.zones', function ($q) use ($request) {
                $q->where('zones.id', $request->zone_id);
            });
        }
        if ($request->filled('serial_number')) {
            $query->where('serial_number', $request->serial_number);
        }
        if ($request->filled('line')) {
            $query->where('line', 'like', '%' . $request->line . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('pharmacist_id')) {
            $query->where('pharmacist_id', $request->pharmacist_id);
        }
        if ($request->filled('doctor_id')) {
            // الفلترة هنا للبحث داخل العلاقة المتعددة
            $query->whereHas('doctors', function ($q) use ($request) {
                $q->where('doctors.id', $request->doctor_id);
            });
        }
        if ($request->filled('representative_id')) {
            $query->where('representative_id', $request->representative_id);
        }

        return $query;
    }

    private function validateInvoice($request, $id = null)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'invoice_date' => 'required|date',
            'line' => 'required|in:1,2',
            'pharmacist_id' => 'required|exists:pharmacists,id',
            'doctor_ids' => 'nullable|array', // تعديل لاستقبال مصفوفة أطباء
            'doctor_ids.*' => 'exists:doctors,id',
            'status' => 'required|in:1,2,3',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.pharmacist_discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);
    }

    private function resolveZoneAndWarehouse($pharmacistId, $line)
    {
        $pharmacist = Pharmacist::with('center')->find($pharmacistId);
        $zone = Zone::where('line', $line)->whereHas('centers', function ($q) use ($pharmacist) {
            $q->where('centers.id', $pharmacist->center_id);
        })->first();
        if (!$zone) throw ValidationException::withMessages(['line' => "عفواً، لا توجد منطقة مسجلة لـ Line $line تشمل مركز ({$pharmacist->center->name})."]);
        if (!$zone->warehouse_id) throw ValidationException::withMessages(['line' => "المنطقة ({$zone->name}) غير مربوطة بمخزن."]);
        return ['zone' => $zone, 'warehouse' => Warehouse::find($zone->warehouse_id), 'sales_rep_id' => $zone->sales_representative_id, 'medical_rep_id' => $zone->medical_representative_id];
    }

    private function processInvoiceItems(Invoice $invoice, array $items, Warehouse $warehouse, $operation = 'deduct')
    {
        $totalAmount = 0;
        $totalDiscount = 0;
        foreach ($items as $item) {
            $drug = Drug::find($item['drug_id']);
            if ($drug->line != $invoice->line) throw ValidationException::withMessages(['items' => "الدواء ({$drug->name}) لا يتبع Line {$invoice->line}."]);
            if ($operation == 'deduct') $this->updateWarehouseStock($warehouse, $drug->id, $item['quantity'], 'deduct');
            $price = $drug->price;
            $qty = $item['quantity'];
            $discountPerc = $item['pharmacist_discount_percentage'] ?? 0;
            $rowTotalBefore = $price * $qty;
            $rowDiscount = $rowTotalBefore * ($discountPerc / 100);
            $rowTotalFinal = $rowTotalBefore - $rowDiscount;
            InvoiceDetail::create(['invoice_id' => $invoice->id, 'drug_id' => $drug->id, 'quantity' => $qty, 'unit_price' => $price, 'pharmacist_discount_percentage' => $discountPerc, 'row_total' => $rowTotalFinal]);
            $totalAmount += $rowTotalBefore;
            $totalDiscount += $rowDiscount;
        }
        return ['total' => $totalAmount, 'discount' => $totalDiscount];
    }

    private function updateWarehouseStock(Warehouse $warehouse, $drugId, $quantity, $operation)
    {
        $stockRecord = $warehouse->drugs()->where('drug_id', $drugId)->first();
        $currentQty = $stockRecord ? $stockRecord->pivot->quantity : 0;
        if ($operation == 'deduct') {
            if ($currentQty < $quantity) throw ValidationException::withMessages(['items' => "الكمية غير متوفرة للصنف (" . Drug::find($drugId)->name . ")."]);
            $newQty = $currentQty - $quantity;
        } else $newQty = $currentQty + $quantity;
        if ($stockRecord) $warehouse->drugs()->updateExistingPivot($drugId, ['quantity' => $newQty]);
        else if ($operation == 'add') $warehouse->drugs()->attach($drugId, ['quantity' => $newQty]);
    }

    private function processDoctorDealImpact(Invoice $invoice, $operation)
    {
        // ==========================================
        // DECREMENT LOGIC
        // ==========================================
        if ($operation === 'decrement') {
            $attachedDeals = $invoice->deals;

            if ($attachedDeals->isEmpty()) return;

            foreach ($attachedDeals as $deal) {
                $contributionAmount = $deal->pivot->contribution_amount;
                $deal->decrement('achieved_amount', $contributionAmount);
                $deal->invoices()->detach($invoice->id);
            }
            return;
        }

        // ==========================================
        // INCREMENT LOGIC (For Multiple Doctors)
        // ==========================================
        $invoiceDate = $invoice->invoice_date ?? $invoice->created_at;

        // استخراج جميع الأطباء المربوطين بالفاتورة
        $doctorIds = $invoice->doctors->pluck('id')->toArray();
        if (empty($doctorIds)) return;

        // إيجاد الاتفاقيات السارية الخاصة بهؤلاء الأطباء
        $deals = DoctorDeal::with('drugs')
            ->whereIn('doctor_id', $doctorIds)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $invoiceDate)
            ->whereHas('pharmacists', function ($q) use ($invoice) {
                $q->where('pharmacists.id', $invoice->pharmacist_id);
            })
            ->where(function ($query) {
                $query->where('is_paid', false)->orWhere(function ($q) {
                    $q->where('is_paid', true)->where('paid_amount', '>', 0);
                });
            })
            ->get();

        if ($deals->isEmpty()) return;

        // تطبيق التارجت كاملاً لكل دكتور حسب أدويته
        foreach ($deals as $deal) {
            $includedDrugIds = $deal->drugs->pluck('id')->toArray();
            $isGeneralDeal = $deal->drugs->isEmpty();
            $dealContribution = 0;

            foreach ($invoice->details as $detail) {
                // إذا كان الاتفاق عام، أو الدواء موجود ضمن الاتفاق
                if ($isGeneralDeal || in_array($detail->drug_id, $includedDrugIds)) {
                    $dealContribution += $detail->row_total;
                }
            }

            if ($dealContribution > 0) {
                $deal->increment('achieved_amount', $dealContribution);
                $deal->invoices()->syncWithoutDetaching([
                    $invoice->id => ['contribution_amount' => $dealContribution]
                ]);
            }
        }
    }

    private function updateInvoiceTotals(Invoice $invoice, array $totals, $status, $inputPaid)
    {
        $final = $totals['total'] - $totals['discount'];
        $paid = ($status == 1) ? $final : (($status == 3) ? min($inputPaid, $final) : 0);
        $invoice->update(['total_amount' => $totals['total'], 'total_discount' => $totals['discount'], 'final_total' => $final, 'paid_amount' => $paid, 'remaining_amount' => $final - $paid]);
    }
}
