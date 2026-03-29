@extends('layouts.admin')

@section('title', 'إضافة منطقة جديدة ')

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

        .line-header {
            background-color: #f4f5fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-right: 4px solid #1e9ff2;
            font-weight: bold;
            color: #1e9ff2;
        }

        .line-header.line-2 {
            border-right-color: #ff9149;
            color: #ff9149;
        }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"><i class="la la-map"></i> إضافة منطقة توزيع جديدة </h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card border-top-primary border-top-3">
                    <div class="card-content">
                        <div class="card-body">
                            <form class="form" action="{{ route('admin.zones.store') }}" method="POST"
                                  x-data="{
                                  province_id: '{{ old('province_id') }}',
                                  allCenters: {{ json_encode($centers) }},
                                  filteredCenters: []
                                  }"
                                  x-init="$watch('province_id', value => { filteredCenters = allCenters.filter(c => c.province_id == value); }); if(province_id) { filteredCenters = allCenters.filter(c => c.province_id == province_id); }">
                                @csrf

                                <div class="form-body">
                                    <h4 class="form-section"><i class="ft-info"></i> البيانات الجغرافية </h4>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>المحافظة <span class="text-danger">*</span></label>
                                                <select name="province_id" class="form-control" x-model="province_id"
                                                        required>
                                                    <option value="">-- اختر المحافظة --</option>
                                                    @foreach($provinces as $province)
                                                        <option
                                                            value="{{ $province->id }}">{{ $province->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('province_id') <span
                                                    class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>اسم المنطقة <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control"
                                                       placeholder="مثال: قطاع شمال" value="{{ old('name') }}" required>
                                                <small class="text-muted">سيتم إنشاء هذا الاسم لخط 1 و خط 2 تلقائياً</small>
                                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="text-bold-600">المخزن المسؤول عن التوريد <span
                                                        class="text-danger">*</span></label>
                                                <select name="warehouse_id" class="form-control" required>
                                                    <option value="">-- اختر المخزن --</option>
                                                    @foreach($warehouses as $wh)
                                                        <option
                                                            value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                                            {{ $wh->name }}({{ $wh->type == 'main' ? 'رئيسي' : 'فرعي' }}
                                                            )
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('warehouse_id') <span
                                                    class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="form-section mt-2"><i class="la la-users"></i> مسؤولي الخطوط</h4>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="line-header">بيانات Line 1</div>
                                            <div class="form-group">
                                                <label>مندوب البيع (Line 1)</label>
                                                <select name="line1_sales_representative_id" class="form-control">
                                                    <option value="">-- اختر مندوب بيع 1 --</option>
                                                    @foreach($representatives as $rep)
                                                        <option
                                                            value="{{ $rep->id }}" {{ old('line1_sales_representative_id') == $rep->id ? 'selected' : '' }}>{{ $rep->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('line1_sales_representative_id') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="form-group">
                                                <label>مندوب الدعاية (Line 1)</label>
                                                <select name="line1_medical_representative_id" class="form-control">
                                                    <option value="">-- اختر مندوب دعاية 1 --</option>
                                                    @foreach($representatives as $rep)
                                                        <option
                                                            value="{{ $rep->id }}" {{ old('line1_medical_representative_id') == $rep->id ? 'selected' : '' }}>{{ $rep->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('line1_medical_representative_id') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="line-header line-2">بيانات Line 2</div>
                                            <div class="form-group">
                                                <label>مندوب البيع (Line 2)</label>
                                                <select name="line2_sales_representative_id" class="form-control">
                                                    <option value="">-- اختر مندوب بيع 2 --</option>
                                                    @foreach($representatives as $rep)
                                                        <option
                                                            value="{{ $rep->id }}" {{ old('line2_sales_representative_id') == $rep->id ? 'selected' : '' }}>{{ $rep->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('line2_sales_representative_id') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="form-group">
                                                <label>مندوب الدعاية (Line 2)</label>
                                                <select name="line2_medical_representative_id" class="form-control">
                                                    <option value="">-- اختر مندوب دعاية 2 --</option>
                                                    @foreach($representatives as $rep)
                                                        <option
                                                            value="{{ $rep->id }}" {{ old('line2_medical_representative_id') == $rep->id ? 'selected' : '' }}>{{ $rep->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('line2_medical_representative_id') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="form-section mt-3"><i class="ft-map-pin"></i> المراكز التابعة للمنطقة
                                    </h4>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <div class="centers-container">
                                                    <template x-if="filteredCenters.length === 0 && province_id">
                                                        <p class="text-center text-muted py-2">لا توجد مراكز أخرى
                                                            متاحة.</p>
                                                    </template>
                                                    <template x-if="!province_id">
                                                        <p class="text-center text-muted py-2">يرجى اختيار المحافظة
                                                            أولاً.</p>
                                                    </template>
                                                    <template x-for="center in filteredCenters" :key="center.id">
                                                        <label class="checkbox-item">
                                                            <input type="checkbox" name="centers[]" :value="center.id">
                                                            <span class="ml-1" x-text="center.name"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                                @error('centers') <span
                                                    class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn btn-warning mr-1" onclick="history.back()">
                                        <i class="ft-x"></i> تراجع
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="la la-save"></i> حفظ المنطقتين (Line 1 & 2)
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
