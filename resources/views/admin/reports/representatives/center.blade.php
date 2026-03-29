@extends('layouts.admin')

@section('title', 'أطباء مركز ' . $center->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">

            <div class="content-header row mb-2">
                <div class="col-12">
                    <h3 class="content-header-title"> أطباء مركز: {{ $center->name }} </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('accountant.dashboard')}}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.doctors.index') }}">تقارير الأطباء</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.doctors.province', $center->province->id) }}">{{ $center->province->name }}</a></li>
                                <li class="breadcrumb-item active">{{ $center->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                @include('admin.includes.alerts.success')
                @include('admin.includes.alerts.errors')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">قائمة الأطباء والمستحقات المالية</h4>
                                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead class="bg-info white">
                                            <tr>
                                                <th>اسم الطبيب</th>
                                                <th>التخصص</th>
                                                <th>إجمالي المبيعات (الوصفات)</th>
                                                <th>عمولة مستحقة (للدفع)</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($doctors as $doctor)
                                                <tr>
                                                    <td class="text-bold-600">{{ $doctor->name }}</td>
                                                    <td>{{ $doctor->speciality }}</td>
                                                    <td>
                                                        <span class="font-weight-bold text-primary">
                                                            {{ number_format($doctor->total_sales, 2) }} ج.م
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($doctor->unpaid_commission > 0)
                                                            <span class="text-danger font-weight-bold">
                                                                {{ number_format($doctor->unpaid_commission, 2) }} ج.م
                                                            </span>
                                                        @else
                                                            <span class="text-success">
                                                                <i class="ft-check-circle"></i> خالص
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('admin.reports.doctors.show', $doctor->id) }}"
                                                               class="btn btn-sm btn-outline-info box-shadow-1 mr-1"
                                                               title="كشف حساب تفصيلي">
                                                                <i class="ft-file-text"></i> التفاصيل
                                                            </a>

                                                            @if($doctor->unpaid_commission > 0)
                                                                <form action="{{ route('admin.reports.doctors.pay', $doctor->id) }}" method="POST" style="display:inline">
                                                                    @csrf
                                                                    <button type="submit"
                                                                            class="btn btn-sm btn-success box-shadow-2"
                                                                            onclick="return confirm('هل أنت متأكد من تسوية مبلغ {{ number_format($doctor->unpaid_commission, 2) }} للطبيب {{ $doctor->name }}؟ سيتم تحديث جميع الفواتير المعلقة كمدفوعة العمولة.')">
                                                                        <i class="ft-check-circle"></i> دفع المستحقات
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <button class="btn btn-sm btn-light disabled" disabled>
                                                                    <i class="ft-check"></i> لا يوجد مستحقات
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-2">لا يوجد أطباء مسجلين في هذا المركز</td>
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
