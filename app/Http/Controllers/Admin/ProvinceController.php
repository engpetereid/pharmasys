<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $provinces = Province::paginate(10);
        return view('admin.provinces.index', compact('provinces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.provinces.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:provinces,name'
        ]);

        try {
            DB::beginTransaction();
            Province::create($validatedData);
            DB::commit();
            return redirect()->route('admin.provinces.index')->with(['success' => 'تم الإضافة بنجاح']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */


    public function edit(Province $province)
    {
        return view('admin.provinces.edit', compact('province'));
    }

    public function update(Request $request, Province $province)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:provinces,name,' . $province->id
        ]);

        try {
            DB::beginTransaction();
            $province->update($validatedData);
            DB::commit();
            return redirect()->route('admin.provinces.index')->with(['success' => 'تم التعديل بنجاح']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
    public function destroy(Province $province)
    {
        //
        try {
            $province->delete();
            return redirect()->route('admin.provinces.index')->with(['success' => 'تم الحذف بنجاح']);

        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
}
