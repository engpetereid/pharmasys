<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CenterController extends Controller
{

    public function index()
    {
        $centers = Center::with('province')->paginate(10);
        return view('admin.centers.index', compact('centers'));
    }


    public function create()
    {
        //
        $provinces = Province::get();
        return view('admin.centers.create', compact('provinces'));
    }


    public function store(Request $request)
    {
        //
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:centers,name',
            'province_id' => 'required|exists:provinces,id',
        ]);

        try {
            DB::beginTransaction();
            Center::create($validatedData);
            DB::commit();
            return redirect()->route('admin.centers.index')->with(['success' => 'تم الإضافة بنجاح']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Center $center)
    {
        $center->load(['pharmacists', 'doctors']);
        return view('admin.centers.show', compact('center'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Center $center)
    {
        //
        $provinces = Province::get();
        return view('admin.centers.edit', compact('provinces', 'center'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Center $center)
    {
        //
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:centers,name,' . $center->id,
            'province_id' => 'required|exists:provinces,id',
        ]);

        try {
            DB::beginTransaction();
            $center->update($validatedData);
            DB::commit();
            return redirect()->route('admin.centers.index')->with(['success' => 'تم التعديل بنجاح']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Center $center)
    {
        //
        try {
            $center->delete();
            return redirect()->route('admin.centers.index')->with(['success' => 'تم الحذف بنجاح']);

        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
}
