@extends('layouts.admin')

@section('title', 'تعديل بيانات الدواء')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-edit"></i> إدارة الأدوية </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.drugs.index') }}">الأدوية</a></li>
                                <li class="breadcrumb-item active">تعديل الدواء: {{ $drug->name }}</li>
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
                                    <h4 class="card-title" id="basic-layout-form"> تعديل بيانات الدواء </h4>
                                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card-content collapse show">
                                    <div class="card-body">

                                        @include('admin.includes.alerts.success')
                                        @include('admin.includes.alerts.errors')

                                        <form class="form" action="{{ route('admin.drugs.update', $drug->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            <input type="hidden" name="id" value="{{ $drug->id }}">

                                            <div class="form-body">
                                                <h4 class="form-section"><i class="ft-info"></i> تفاصيل المنتج</h4>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="name"> اسم الدواء <span class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <input type="text" id="name" class="form-control"
                                                                       placeholder="أدخل اسم الدواء"
                                                                       value="{{ old('name', $drug->name) }}" name="name">
                                                                <div class="form-control-position">
                                                                    <i class="la la-medkit"></i>
                                                                </div>
                                                            </div>
                                                            @error("name")
                                                            <span class="text-danger font-small-3">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="price"> سعر الوحدة <span class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <input type="number" id="price" class="form-control"
                                                                       placeholder="0.00" step="0.01" min="0"
                                                                       value="{{ old('price', $drug->price) }}" name="price">
                                                                <div class="form-control-position">
                                                                    <i class="la la-money"></i>
                                                                </div>
                                                            </div>
                                                            @error("price")
                                                            <span class="text-danger font-small-3">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="line"> الخط (Line) <span class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <select name="line" class="form-control">
                                                                    <option value="1" {{ old('line', $drug->line) == 1 ? 'selected' : '' }}>Line 1</option>
                                                                    <option value="2" {{ old('line', $drug->line) == 2 ? 'selected' : '' }}>Line 2</option>
                                                                </select>
                                                                <div class="form-control-position">
                                                                    <i class="la la-random"></i>
                                                                </div>
                                                            </div>
                                                            @error("line")
                                                            <span class="text-danger font-small-3">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="form-actions">
                                                <button type="button" class="btn btn-secondary mr-1" onclick="history.back();">
                                                    <i class="ft-x"></i> إلغاء
                                                </button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="la la-check-square-o"></i> حفظ التعديلات
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
