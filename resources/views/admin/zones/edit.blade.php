@extends('layouts.admin')

@section('title', 'تعديل المنطقة: ' . $zone->name)

@section('style')
    <script src="{{ asset('assets/admin/js/scripts/cdn.min.js') }}" defer></script>
    <style>
        .centers-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccd6e6;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .checkbox-item {
            margin-bottom: 10px;
            display: block;
            cursor: pointer;
        }

        .checkbox-item input {
            margin-left: 10px;
        }

        .checkbox-item:hover {
            background-color: #e6f0ff;
        }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> تعديل المنطقة: {{ $zone->name }} </h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card border-top-warning border-top-3">
                    <div class="card-content">
                        <div class="card-body">
                            <form class="form" action="{{ route('admin.zones.update', $zone->id) }}" method="POST"
                                  x-data="{
                                  province_id: '{{ old('province_id', $zone->province_id) }}',
                                  allCenters: {{ json_encode($centers) }},
                                  selectedCenters: {{ json_encode($selectedCenters) }},
                                  filteredCenters: []
                                  }"
                                  x-init="$watch('province_id', value => { filteredCenters = allCenters.filter(c => c.province_id == value); }); if(province_id) { filteredCenters = allCenters.filter(c => c.province_id == province_id); }">
                                @csrf
                                @method('PUT')

                                <div class="form-body">
                                    <h4 class="form-section"><i class="ft-info"></i> البيانات الأساسية</h4>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>المحافظة <span class="text-danger">*</span></label>
                                                <select name="province_id" class="form-control" x-model="province_id"
                                                        required>
                                                    <option value="">-- اختر المحافظة --</option>
                                                    @foreach($provinces as $province)
                                                        <option
                                                            value="{{ $province->id }}" {{ $zone->province_id == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>اسم المنطقة <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control"
                                                       value="{{ old('name', $zone->name) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>الخط (Line) <span class="text-danger">*</span></label>
                                                <select name="line" class="form-control" required>
                                                    <option
                                                        value="1" {{ old('line', $zone->line) == 1 ? 'selected' : '' }}>
                                                        Line 1
                                                    </option>
                                                    <option
                                                        value="2" {{ old('line', $zone->line) == 2 ? 'selected' : '' }}>
                                                        Line 2
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">


                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="text-bold-600">المخزن المسؤول <span
                                                        class="text-danger">*</span></label>
                                                <select name="warehouse_id" class="form-control" required>
                                                    <option value="">-- اختر المخزن --</option>
                                                    @foreach($warehouses as $wh)
                                                        <option
                                                            value="{{ $wh->id }}" {{ old('warehouse_id', $zone->warehouse_id) == $wh->id ? 'selected' : '' }}>
                                                            {{ $wh->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>مندوب البيع</label>
                                                <select name="sales_representative_id" class="form-control">
                                                    <option value="">-- اختر المندوب --</option>
                                                    @foreach($representatives as $rep)
                                                        <option
                                                            value="{{ $rep->id }}" {{ old('sales_representative_id', $zone->sales_representative_id) == $rep->id ? 'selected' : '' }}>{{ $rep->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>مندوب الدعاية</label>
                                                <select name="medical_representative_id" class="form-control">
                                                    <option value="">-- اختر المندوب --</option>
                                                    @foreach($representatives as $rep)
                                                        <option
                                                            value="{{ $rep->id }}" {{ old('medical_representative_id', $zone->medical_representative_id) == $rep->id ? 'selected' : '' }}>{{ $rep->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="form-section mt-3"><i class="ft-map-pin"></i> المراكز التابعة للمنطقة
                                    </h4>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <div class="centers-container">
                                                    <template x-for="center in filteredCenters" :key="center.id">
                                                        <label class="checkbox-item">
                                                            <input type="checkbox" name="centers[]" :value="center.id"
                                                                   :checked="selectedCenters.includes(center.id)">
                                                            <span class="ml-1" x-text="center.name"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary mr-1" onclick="history.back()">
                                        إلغاء
                                    </button>
                                    <button type="submit" class="btn btn-warning">حفظ التعديلات</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
