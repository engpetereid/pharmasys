<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Representative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepresentativeController extends Controller
{
    public function index(Request $request)
    {
        $query = Representative::query();

        // منطق البحث
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        }

        $representatives = $query->latest()->paginate(10);

        // الحفاظ على كلمة البحث عند الانتقال للصفحة الى بعدها
        $representatives->appends($request->all());

        return view('admin.representatives.index', compact('representatives'));
    }

    public function create()
    {
        return view('admin.representatives.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        Representative::create($validatedData);
        return redirect()->route('admin.representatives.index')->with(['success' => 'تم الإضافة بنجاح']);
    }

    /**
     * عرض تفاصيل المندوب والمناطق المسؤول عنها
     */
    public function show(Representative $representative)
    {
        // تحميل المناطق التي يديرها كمندوب بيع أو دعاية
        $representative->load(['salesZones.province', 'medicalZones.province']);

        return view('admin.representatives.show', compact('representative'));
    }

    public function edit(Representative $representative)
    {
        return view('admin.representatives.edit', compact('representative'));
    }

    public function update(Request $request, Representative $representative)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        $representative->update($validatedData);
        return redirect()->route('admin.representatives.index')->with(['success' => 'تم التعديل بنجاح']);
    }

    public function destroy(Representative $representative)
    {
        try {
            if ($representative->salesZones()->exists()|| $representative->medicalZones()->exists()) {
                return redirect()->back()->withErrors(['error' => 'عفواً، لا يمكن حذف هذا المندوب لأنه مرتبط بمناطق توزيع. يرجى تغيير مندوب تلك المناطق أولاً.']);
            }
            if ($representative->invoices()->exists()) {
                return redirect()->back()->withErrors(['error' => 'عفواً، لا يمكن حذف المندوب لوجود فواتير مبيعات مسجلة باسمه.']);
            }
            $representative->delete();
            return redirect()->route('admin.representatives.index')->with(['success' => 'تم الحذف بنجاح']);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->back()->withErrors(['error' => 'حدث خطأ: لا يمكن حذف المندوب لوجود بيانات مرتبطة به في جداول أخرى.']);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
}
