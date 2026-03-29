<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with(['parent', 'zones'])
            ->withCount('drugs')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.warehouses.index', compact('warehouses'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name',
            'type' => 'required|in:main,sub',
            'parent_id' => 'nullable|required_if:type,sub|exists:warehouses,id',
        ], [
            'parent_id.required_if' => 'يجب اختيار المخزن الرئيسي التابع له.',
        ]);

        try {
            Warehouse::create($request->all());
            return redirect()->route('admin.warehouses.index')->with(['success' => 'تم إضافة المخزن بنجاح']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Warehouse $warehouse)
    {
        $inventory = $warehouse->drugs()
            ->select('drugs.id', 'drugs.name', 'drugs.price', 'drugs.line')
            ->paginate(15);
        $warehouse->load('distributionAreas');

        return view('admin.warehouses.show', compact('warehouse', 'inventory'));
    }


    public function edit(Warehouse $warehouse)
    {
        $mainWarehouses = Warehouse::where('type', 'main')->where('id', '!=', $warehouse->id)->get();
        return view('admin.warehouses.edit', compact('warehouse', 'mainWarehouses'));
    }


    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name,' . $warehouse->id,
            'type' => 'required|in:main,sub',
            'parent_id' => 'nullable|required_if:type,sub|exists:warehouses,id',
        ]);

        try {
            $warehouse->update($request->all());
            return redirect()->route('admin.warehouses.index')->with(['success' => 'تم تحديث بيانات المخزن بنجاح']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }


    public function destroy(Warehouse $warehouse)
    {
        try {
            if ($warehouse->drugs()->count() > 0) {
                return redirect()->back()->with(['error' => 'لا يمكن حذف المخزن لأنه يحتوي على أرصدة أدوية.']);
            }
            if ($warehouse->zones()->count() > 0) {
                return redirect()->back()->with(['error' => 'لا يمكن حذف المخزن لأنه مسؤول عن توزيع مناطق معينة.']);
            }

            $warehouse->delete();
            return redirect()->route('admin.warehouses.index')->with(['success' => 'تم حذف المخزن بنجاح']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }

    public function addStock(Warehouse $warehouse)
    {
        $warehouse->load('parent');

        if ($warehouse->type == 'sub' && $warehouse->parent) {
            $drugs = $warehouse->parent->drugs()
                ->select('drugs.id', 'drugs.name', 'drugs.line')
                ->get()
                ->map(function ($drug) {
                    $drug->max_quantity = $drug->pivot->quantity;
                    return $drug;
                });
        } else {
            $drugs = \App\Models\Drug::select('id', 'name', 'line')->get()
                ->map(function ($drug) {
                    $drug->max_quantity = null;
                    return $drug;
                });
        }

        return view('admin.warehouses.stock.add', compact('warehouse', 'drugs'));
    }

    public function storeStock(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                $quantityToAdd = $item['quantity'];
                $drugId = $item['drug_id'];

                if ($warehouse->type == 'sub' && $warehouse->parent) {
                    $parentStock = $warehouse->parent->drugs()->where('drug_id', $drugId)->first();

                    if (!$parentStock || $parentStock->pivot->quantity < $quantityToAdd) {
                        throw ValidationException::withMessages([
                            'items' => "الكمية المطلوبة للدواء (ID: $drugId) غير متوفرة في المخزن الرئيسي."
                        ]);
                    }

                    $warehouse->parent->drugs()->updateExistingPivot($drugId, [
                        'quantity' => $parentStock->pivot->quantity - $quantityToAdd
                    ]);
                }

                $exists = $warehouse->drugs()->where('drug_id', $drugId)->exists();

                if ($exists) {
                    $currentQty = $warehouse->drugs()->find($drugId)->pivot->quantity;
                    $warehouse->drugs()->updateExistingPivot($drugId, [
                        'quantity' => $currentQty + $quantityToAdd
                    ]);
                } else {
                    $warehouse->drugs()->attach($drugId, [
                        'quantity' => $quantityToAdd
                    ]);
                }
            }

            DB::commit();

            $message = ($warehouse->type == 'sub') ? 'تم نقل الكميات من المخزن الرئيسي بنجاح' : 'تم توريد الكميات للمخزن بنجاح';
            return redirect()->route('admin.warehouses.show', $warehouse->id)->with(['success' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function returnStock($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        // فقط الأدوية التي لها رصيد في المخزن
        $drugsWithStock = $warehouse->drugs()->wherePivot('quantity', '>', 0)->get();

        return view('admin.warehouses.stock.return', [
            'warehouse' => $warehouse,
            'drugs' => $drugsWithStock
        ]);
    }

    public function processReturnStock(Request $request, $id)
    {
        $request->validate([
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // المخزن مع العلاقة "parent" لمعرفة المخزن الرئيسي
        $warehouse = Warehouse::with('parent')->findOrFail($id);

        // التحقق من وجود الدواء في المخزن الحالي
        $stockRecord = $warehouse->drugs()->where('drug_id', $request->drug_id)->first();

        if (!$stockRecord) {
            return redirect()->back()->with(['error' => 'هذا الصنف غير موجود في المخزن.']);
        }

        $currentQty = $stockRecord->pivot->quantity;

        // التحقق من توفر الكمية
        if ($request->quantity > $currentQty) {
            return redirect()->back()
                ->withInput()
                ->with(['error' => "الكمية المراد إرجاعها ({$request->quantity}) أكبر من الرصيد المتوفر ({$currentQty})."]);
        }

        try {
            DB::beginTransaction();

            //  خصم الكمية من المخزن الحالي ( فرعي أو رئيسي)
            $newQty = $currentQty - $request->quantity;
            $warehouse->drugs()->updateExistingPivot($request->drug_id, ['quantity' => $newQty]);

            // إذا كان مخزن فرعي وليه مخزن رئيسي نعيد الكمية للمخزن الرئيسي
            if ($warehouse->type == 'sub' && $warehouse->parent) {

                $parentWarehouse = $warehouse->parent;
                $parentStock = $parentWarehouse->drugs()->where('drug_id', $request->drug_id)->first();

                if ($parentStock) {
                    // تحديث رصيد الرئيسي
                    $newParentQty = $parentStock->pivot->quantity + $request->quantity;
                    $parentWarehouse->drugs()->updateExistingPivot($request->drug_id, ['quantity' => $newParentQty]);
                } else {
                    // إضافة الدوا للرئيسي لو مكنش موجود
                    $parentWarehouse->drugs()->attach($request->drug_id, ['quantity' => $request->quantity]);
                }
            }

            DB::commit();

            $msg = ($warehouse->type == 'sub')
                ? 'تم تسجيل المرتجع وإعادة الكمية للمخزن الرئيسي بنجاح.'
                : 'تم خصم الكمية من المخزن الرئيسي بنجاح.';

            return redirect()->route('admin.warehouses.show', $warehouse->id)->with(['success' => $msg]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'حدث خطأ أثناء العملية: ' . $e->getMessage()]);
        }
    }
}
