<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ZoneExpense;
use Illuminate\Http\Request;

class ZoneExpenseController extends Controller
{

    public function create($zoneId)
    {
        $zone = Zone::findOrFail($zoneId);
        return view('admin.zones.expenses.create', compact('zone'));
    }


    public function store(Request $request, $zoneId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'expense_date' => 'required|date',
        ]);

        $zone = Zone::findOrFail($zoneId);

        $zone->expenses()->create([
            'amount' => $request->amount,
            'description' => $request->description,
            'expense_date' => $request->expense_date,
        ]);

        return redirect()->route('admin.zones.show', $zone->id)
            ->with(['success' => 'تم إضافة المصروف بنجاح']);
    }

    public function destroy($id)
    {
        $expense = ZoneExpense::findOrFail($id);
        $zoneId = $expense->zone_id;
        $expense->delete();

        return redirect()->route('admin.zones.show', $zoneId)
            ->with(['success' => 'تم حذف المصروف بنجاح']);
    }
}
