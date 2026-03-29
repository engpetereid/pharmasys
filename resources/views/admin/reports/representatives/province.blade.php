@extends('layouts.admin')

@section('title', 'أطباء محافظة ' . $province->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="col-12">
                    <h3 class="content-header-title"> تقرير أطباء: {{ $province->name }} </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.doctors.index') }}">تقارير الأطباء</a></li>
                                <li class="breadcrumb-item active">{{ $province->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="row">
                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="card pull-up bg-gradient-x-primary">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left">
                                            <h3 class="white">{{ number_format($centers->sum('total_sales'), 2) }}</h3>
                                            <h6 class="white">إجمالي مبيعات الأطباء بالمحافظة</h6>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="la la-stethoscope white font-large-2 float-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">المراكز التابعة لـ {{ $province->name }}</h4>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-info white">
                                            <tr>
                                                <th>اسم المركز</th>
                                                <th>عدد الأطباء</th>
                                                <th>إجمالي المبيعات</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($centers as $center)
                                                <tr>
                                                    <td class="text-bold-600">{{ $center->name }}</td>
                                                    <td>
                                                        <span class="badge badge-pill badge-secondary">
                                                            {{ $center->doctors_count }} طبيب
                                                        </span>
                                                    </td>
                                                    <td class="text-info font-weight-bold">
                                                        {{ number_format($center->total_sales, 2) }} ج.م
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.reports.doctors.center', $center->id) }}"
                                                           class="btn btn-sm btn-outline-info box-shadow-2">
                                                            <i class="ft-users"></i> قائمة الأطباء
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-2">لا توجد مراكز مسجلة في هذه المحافظة</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
