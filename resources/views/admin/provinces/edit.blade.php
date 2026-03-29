@extends('layouts.admin')

@section('title', 'تعديل بيانات المحافظة')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">

            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-edit"></i> إدارة المحافظات </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.provinces.index') }}">المحافظات</a></li>
                                <li class="breadcrumb-item active">تعديل المحافظة: {{ $province->name }}</li>
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
                                    <h4 class="card-title" id="basic-layout-form"> تعديل بيانات المحافظة </h4>
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

                                        <form class="form" action="{{ route('admin.provinces.update', $province->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            <input name="id" value="{{ $province->id }}" type="hidden">

                                            <div class="form-body">
                                                <h4 class="form-section"><i class="ft-map"></i> المعلومات الجغرافية</h4>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="name"> اسم المحافظة <span class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <input type="text" id="name" class="form-control"
                                                                       placeholder="أدخل اسم المحافظة"
                                                                       value="{{ old('name', $province->name) }}" name="name">
                                                                <div class="form-control-position">
                                                                    <i class="la la-map-marker"></i>
                                                                </div>
                                                            </div>
                                                            @error("name")
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
