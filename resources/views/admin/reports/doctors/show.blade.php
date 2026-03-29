@extends('layouts.admin')

@section('title', 'كشف حساب د. ' . $doctor->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> كشف حساب: د. {{ $doctor->name }} </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.doctors.index') }}">تقارير الأطباء</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.doctors.center', $doctor->center->id) }}">{{ $doctor->center->name }}</a></li>
                                <li class="breadcrumb-item active">{{ $doctor->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>

            </div>

            <div class="content-body">
                @include('admin.includes.alerts.success')
                @include('admin.includes.alerts.errors')

                {{-- البطاقات العلوية --}}
                <div class="row">
                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="card pull-up bg-gradient-x-primary">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left text-white">
                                            <h3 class="text-white">{{ number_format($totalSales, 2) }}</h3>
                                            <h6>إجمالي قيمة الوصفات</h6>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="la la-stethoscope text-white font-large-2 float-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="card pull-up bg-gradient-x-success">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left text-white">
                                            <h3 class="text-white">{{ number_format($paidCommission, 2) }}</h3>
                                            <h6>إجمالي العمولات المدفوعة</h6>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="la la-check-circle text-white font-large-2 float-right"></i>
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
                                        <div class="media-body text-left text-white">
                                            <h3 class="text-white">{{ number_format($dueCommission, 2) }}</h3>
                                            <h6>عمولات مستحقة (للدفع)</h6>

                                        </div>
                                        <div class="align-self-center">
                                            <i class="la la-money text-white font-large-2 float-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- جدول الاتفاقات (العمولات) الجديد --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card border-top-success border-top-3">
                            <div class="card-header pb-0">
                                <h4 class="card-title"><i class="la la-handshake-o"></i> الاتفاقات المالية (العمولات)</h4>
                                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                            <tr>
                                                <th>تاريخ البدء</th>
                                                <th>التارجت المطلوب</th>
                                                <th>المحقق</th>
                                                <th>النسبة</th>
                                                <th>قيمة العمولة</th>
                                                <th>المدفوع</th>
                                                <th>المتبقي</th>
                                                <th>الحالة</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($doctor->deals as $deal)
                                                <tr>
                                                    <td>{{ $deal->start_date }}</td>
                                                    <td>{{ number_format($deal->target_amount, 2) }}</td>
                                                    <td class="text-success font-weight-bold">{{ number_format($deal->achieved_amount, 2) }}</td>
                                                    <td><span class="badge badge-pill badge-secondary">{{ floatval($deal->commission_percentage) }}%</span></td>
                                                    <td class="font-weight-bold">{{ number_format($deal->commission_amount, 2) }}</td>
                                                    <td class="text-success">{{ number_format($deal->paid_amount, 2) }}</td>
                                                    <td class="text-danger font-weight-bold">{{ number_format(max(0, $deal->commission_amount - $deal->paid_amount), 2) }}</td>
                                                    <td>
                                                        @if($deal->is_paid)
                                                            <span class="badge badge-success">تمت التسوية</span>
                                                        @else
                                                            <span class="badge badge-warning">جاري/مستحقة</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-2">لا توجد اتفاقات مالية مسجلة لهذا الطبيب</td>
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

                {{-- جدول الفواتير الموجهة --}}
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header pb-0">
                                <h4 class="card-title"><i class="ft-file-text"></i> سجل الفواتير الموجهة</h4>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                            <tr>
                                                <th>رقم الفاتورة</th>
                                                <th>التاريخ</th>
                                                <th>الصيدلية</th>
                                                <th>المندوب</th>
                                                <th>قيمة الفاتورة</th>
                                                <th>عرض الفاتورة</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($invoices as $invoice)
                                                <tr>
                                                    <td>
                                                        <span class="text-bold-700 text-primary">{{ $invoice->serial_number ?? $invoice->id }}</span>
                                                    </td>
                                                    <td>{{ $invoice->invoice_date }}</td>
                                                    <td>{{ $invoice->pharmacist->name ?? '-' }}</td>
                                                    <td>{{ $invoice->representative->name ?? '-' }}</td>
                                                    <td class="font-weight-bold">{{ number_format($invoice->final_total, 2) }} ج.م</td>
                                                    <td>
                                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary box-shadow-1">
                                                            <i class="ft-eye"></i> عرض
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">لا توجد فواتير مسجلة لهذا الطبيب</td>
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
