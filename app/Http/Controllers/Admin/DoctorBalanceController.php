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
    public function index(Request $request)
    {
        $zones = Zone::where('line', 1)->get();
        $centers = Center::get();
        $query = Doctor::query()
            ->with('center');

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

        $doctors = $query->paginate(20)->withQueryString();

        return view('admin.reports.doctors_balance', compact('doctors', 'zones','centers'));
    }
}
