@extends('layouts.admin')

@section('title', 'تقرير مركز ' . $center->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="col-12">
                    <h3 class="content-header-title"> تقرير مبيعات مركز: {{ $center->name }} </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير الجغرافية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.province', $center->province->id) }}">{{ $center->province->name }}</a></li>
                                <li class="breadcrumb-item active">{{ $center->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="row">
                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="card pull-up">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left">
                                            <h3 class="info">{{ number_format($totalSales, 2) }}</h3>
                                            <h6 class="text-muted">إجمالي مبيعات المركز</h6>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="la la-money info font-large-2 float-right"></i>
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
                                <h4 class="card-title">الصيدليات في {{ $center->name }}</h4>
                                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead class="bg-success white">
                                            <tr>
                                                <th>الصيدلية</th>
                                                <th>العنوان</th>
                                                <th>عدد الفواتير</th>
                                                <th>إجمالي المسحوبات</th>
                                                <th>التفاصيل</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($pharmacists as $pharma)
                                                <tr>
                                                    <td class="text-bold-600">{{ $pharma->name }}</td>
                                                    <td>{{ $pharma->address }}</td>
                                                    <td>
                                                        <span class="badge badge-pill badge-secondary">
                                                            {{ $pharma->invoices_count }} فاتورة
                                                        </span>
                                                    </td>
                                                    <td class="text-success font-weight-bold">
                                                        {{ number_format($pharma->total_sales, 2) }} ج.م
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.reports.pharmacist', $pharma->id) }}"
                                                           class="btn btn-sm btn-outline-success box-shadow-2">
                                                            <i class="ft-file-text"></i> كشف حساب
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-2">لا توجد صيدليات مسجلة في هذا المركز</td>
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
