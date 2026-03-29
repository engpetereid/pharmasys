@extends('layouts.admin')

@section('title', 'تقرير الأطباء - المحافظات')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="col-12">
                    <h3 class="content-header-title"> <i class="la la-user-md"></i> تقرير مبيعات الأطباء (المحافظات) </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">الرئيسية</a></li>
                                <li class="breadcrumb-item active">تقارير الأطباء</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">أداء المحافظات (طبيًا)</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped mb-0">
                                            <thead class="bg-primary white">
                                            <tr>
                                                <th>المحافظة</th>
                                                <th>عدد الأطباء المسجلين</th>
                                                <th>إجمالي المبيعات (الوصفات)</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($provinces as $province)
                                                <tr>
                                                    <td class="text-bold-600">{{ $province->name }}</td>
                                                    <td>
                                                        <span class="badge badge-pill badge-primary">
                                                            {{ $province->doctors_count }} طبيب
                                                        </span>
                                                    </td>
                                                    <td class="text-success font-weight-bold">{{ number_format($province->total_sales, 2) }} ج.م</td>
                                                    <td>
                                                        <a href="{{ route('admin.reports.doctors.province', $province->id) }}"
                                                           class="btn btn-sm btn-outline-primary box-shadow-2">
                                                            <i class="ft-eye"></i> عرض المراكز
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-2">لا توجد بيانات متاحة</td>
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
