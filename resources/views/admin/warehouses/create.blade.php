@extends('layouts.admin')

@section('title', 'اضافة بيانات المخزن')

@section('style')
    {{-- تأكد من أن هذا الملف يحتوي بالفعل على مكتبة Alpine.js أو قم باستدعائها من رابط CDN --}}
    <script src="{{ asset('assets/admin/js/scripts/cdn.min.js') }}" defer></script>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-edit"></i> إدارة المخازن </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">المخازن</a></li>
                                <li class="breadcrumb-item active">اضافة مخزن</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section id="basic-form-layouts">
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card border-top-warning border-top-3">
                                <div class="card-header">
                                    <h4 class="card-title" id="basic-layout-form"> اضافة المخزن </h4>
                                </div>

                                <div class="card-content collapse show">
                                    <div class="card-body">

                                        @include('admin.includes.alerts.success')
                                        @include('admin.includes.alerts.errors')

                                        {{-- تم إصلاح الوسم وإضافة x-data لعمل Alpine.js بشكل صحيح --}}
                                        <form class="form" action="{{ route('admin.warehouses.store') }}" method="POST" x-data="{ type: 'sub' }">
                                            @csrf

                                            <div class="form-body">
                                                <h4 class="form-section"><i class="ft-info"></i> البيانات الأساسية</h4>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>اسم المخزن <span class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <input type="text" name="name" class="form-control"
                                                                       placeholder="مثال: مخزن المنيا الرئيسي"
                                                                       value="{{ old('name') }}" required>
                                                                <div class="form-control-position"><i class="la la-home"></i></div>
                                                            </div>
                                                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>نوع المخزن <span class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <select name="type" class="form-control" x-model="type" required>
                                                                    <option value="sub">مخزن فرعي (توزيع)</option>
                                                                    <option value="main">مخزن رئيسي (تخزين)</option>
                                                                </select>
                                                                <div class="form-control-position"><i class="la la-sitemap"></i></div>
                                                            </div>
                                                            @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row" x-show="type === 'sub'" x-transition>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>تابع للمخزن الرئيسي <span class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <select name="parent_id" class="form-control">
                                                                    <option value="">-- اختر المخزن الرئيسي --</option>
                                                                    @foreach($mainWarehouses as $main)
                                                                        <option value="{{ $main->id }}" {{ old('parent_id') == $main->id ? 'selected' : '' }}>
                                                                            {{ $main->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="form-control-position"><i class="la la-level-up"></i></div>
                                                            </div>
                                                            <small class="text-muted">المخزن الفرعي يجب أن يتبع مخزناً رئيسياً لتلقي البضاعة منه.</small>
                                                            @error('parent_id') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-actions">
                                                <button type="button" class="btn btn-secondary mr-1" onclick="history.back();">
                                                    <i class="ft-x"></i> إلغاء
                                                </button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="la la-check-square-o"></i> اضافة
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
