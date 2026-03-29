@extends('layouts.admin')

@section('title', 'كشف حساب ' . $pharmacist->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="col-12">
                    <h3 class="content-header-title"> كشف حساب: {{ $pharmacist->name }} </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير الجغرافية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.province', $pharmacist->center->province->id) }}">{{ $pharmacist->center->province->name }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.center', $pharmacist->center->id) }}">{{ $pharmacist->center->name }}</a></li>
                                <li class="breadcrumb-item active">{{ $pharmacist->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="row">
                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="card pull-up bg-gradient-x-success">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left">
                                            <h3 class="white">{{ number_format($totalSales, 2) }}</h3>
                                            <h6 class="white">إجمالي المسحوبات</h6>
                                        </div>
                                        <div>
                                            <i class="la la-cart-plus white font-large-2 float-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="card pull-up bg-gradient-x-warning">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left">
                                            <h3 class="white">{{ number_format($totalPaid, 2) }}</h3>
                                            <h6 class="white">إجمالي المدفوع</h6>
                                        </div>
                                        <div>
                                            <i class="la la-check-circle white font-large-2 float-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="card pull-up bg-gradient-x-danger">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left">
                                            <h3 class="white">{{ number_format($totalDue, 2) }}</h3>
                                            <h6 class="white">المتبقي (مديونية)</h6>
                                        </div>
                                        <div>
                                            <i class="la la-money white font-large-2 float-right"></i>
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
                                <h4 class="card-title">سجل الفواتير</h4>
                                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>التاريخ</th>
                                                <th>الطبيب</th>
                                                <th>المندوب</th>
                                                <th>الإجمالي</th>
                                                <th>المدفوع</th>
                                                <th>المتبقي</th>
                                                <th>الحالة</th>
                                                <th>الإجراء</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($invoices as $invoice)
                                                <tr>
                                                    <td>{{ $invoice->id }}</td>
                                                    <td>{{ $invoice->invoice_date }}</td>
                                                    <td>
                                                        @if($invoice->doctors && $invoice->doctors->count() > 0)
                                                            {{ $invoice->doctors->pluck('name')->implode('، ') }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $invoice->representative->name ?? '-' }}</td>
                                                    <td class="font-weight-bold">{{ number_format($invoice->final_total, 2) }}</td>
                                                    <td class="text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                                                    <td class="text-danger font-weight-bold">{{ number_format($invoice->remaining_amount, 2) }}</td>
                                                    <td>
                                                        @if($invoice->status == 1)
                                                            <span class="badge badge-success">مدفوع</span>
                                                        @elseif($invoice->status == 2)
                                                            <span class="badge badge-warning">آجل</span>
                                                        @elseif($invoice->status == 3)
                                                            <span class="badge badge-info">جزئي</span>
                                                        @else
                                                            <span class="badge badge-secondary">غير معروف</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary box-shadow-1">
                                                            <i class="ft-eye"></i> عرض
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-2">لا توجد فواتير مسجلة لهذه الصيدلية</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-2 justify-content-center d-flex">
                                        {{ $invoices->links() }}
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
