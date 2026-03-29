@extends('layouts.admin')

@section('title', 'إضافة طبيب جديد')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"><i class="la la-user-md"></i> إدارة الأطباء </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{route('admin.doctors.index')}}">الاطباء</a></li>
                                <li class="breadcrumb-item active">إضافة طبيب</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section id="basic-form-layouts">
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card border-top-primary border-top-3">
                                <div class="card-header">
                                    <h4 class="card-title" id="basic-layout-form"> إضافة طبيب جديد </h4>
                                    <a class="heading-elements-toggle"><i
                                            class="la la-ellipsis-v font-medium-3"></i></a>
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

                                        <form class="form" action="{{ route('admin.doctors.store') }}" method="POST"
                                              enctype="multipart/form-data">
                                            @csrf

                                            <div class="form-body">
                                                <h4 class="form-section"><i class="ft-user"></i> البيانات الشخصية
                                                    والمهنية</h4>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="name"> اسم الطبيب <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <input type="text" id="name" class="form-control"
                                                                       placeholder="أدخل اسم الطبيب"
                                                                       value="{{ old('name') }}" name="name">
                                                                <div class="form-control-position">
                                                                    <i class="la la-user"></i>
                                                                </div>
                                                            </div>
                                                            @error("name")
                                                            <span class="text-danger font-small-3">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="phone"> رقم الهاتف <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <input type="text" id="phone" class="form-control"
                                                                       placeholder="01xxxxxxxxx"
                                                                       value="{{ old('phone') }}" name="phone">
                                                                <div class="form-control-position">
                                                                    <i class="la la-phone"></i>
                                                                </div>
                                                            </div>
                                                            @error("phone")
                                                            <span class="text-danger font-small-3">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="speciality"> التخصص <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <input type="text" id="speciality" class="form-control"
                                                                       placeholder="مثال: باطنة، أطفال..."
                                                                       value="{{ old('speciality') }}"
                                                                       name="speciality">
                                                                <div class="form-control-position">
                                                                    <i class="la la-stethoscope"></i>
                                                                </div>
                                                            </div>
                                                            @error("speciality")
                                                            <span class="text-danger font-small-3">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                </div>

                                                <h4 class="form-section mt-2"><i class="ft-map-pin"></i> الموقع والعنوان
                                                </h4>

                                                <div class="row">
                                                    @if(isset($centers) && $centers->count() > 0)
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="center_id"> المركز التابع له <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="position-relative has-icon-left">
                                                                    <select name="center_id" class="select form-control"
                                                                            id="center_id">
                                                                        <option value="">-- اختر المركز --</option>
                                                                        @foreach($centers as $center)
                                                                            <option value="{{ $center->id }}"
                                                                                {{ (old('center_id') == $center->id || (isset($selected_center_id) && $selected_center_id == $center->id)) ? 'selected' : '' }}>
                                                                                {{ $center->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="form-control-position">
                                                                        <i class="la la-hospital-o"></i>
                                                                    </div>
                                                                </div>
                                                                @error("center_id")
                                                                <span
                                                                    class="text-danger font-small-3">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="address"> عنوان العيادة <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="position-relative has-icon-left">
                                                                <input type="text" id="address" class="form-control"
                                                                       placeholder="العنوان بالتفصيل"
                                                                       value="{{ old('address') }}" name="address">
                                                                <div class="form-control-position">
                                                                    <i class="la la-map"></i>
                                                                </div>
                                                            </div>
                                                            @error("address")
                                                            <span class="text-danger font-small-3">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="form-actions">
                                                <button type="button" class="btn btn-warning mr-1"
                                                        onclick="history.back();">
                                                    <i class="ft-x"></i> تراجع
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="la la-check-square-o"></i> حفظ البيانات
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
