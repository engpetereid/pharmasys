@extends('layouts.admin')

@section('title', 'ملف الطبيب: ' . $doctor->name)

@section('style')
    <style>
        .profile-header {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            font-size: 2.5rem;
            line-height: 100px;
            margin: 0 auto;
            display: block;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            height: 100px;
            display: flex;
            align-items: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #666ee8, #764ba2);
        }

        .bg-gradient-success {
            background: linear-gradient(45deg, #11998e, #38ef7d);
        }

        .bg-gradient-danger {
            background: linear-gradient(45deg, #ff5f6d, #ffc371);
        }

        .bg-gradient-info {
            background: linear-gradient(45deg, #2193b0, #6dd5ed);
        }

        .nav-tabs .nav-link {
            font-weight: 600;
            color: #555;
        }

        .nav-tabs .nav-link.active {
            border-top: 3px solid #666ee8;
            color: #666ee8;
        }

        .deal-card {
            border: 1px solid #eee;
            border-right: 4px solid transparent;
        }

        .deal-card.active {
            border-right-color: #28a745;
            background-color: #fafffb;
        }

        .deal-card.prepaid {
            border-right-color: #dc3545;
            background-color: #fff5f5;
        }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            {{-- Header ... --}}
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"><i class="la la-user-md"></i> ملف الطبيب </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.doctors.index') }}">الأطباء</a>
                                </li>
                                <li class="breadcrumb-item active">{{ $doctor->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12 text-right">
                    <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="btn btn-warning box-shadow-2">
                        <i class="ft-edit"></i> تعديل البيانات
                    </a>
                </div>
            </div>

            <div class="content-body">

                <div class="row match-height">
                    <div class="col-xl-4 col-lg-5 col-12">
                        <div class="profile-header text-center">
                            <div class="avatar-circle bg-primary rounded-circle text-white mb-2 box-shadow-2">
                                {{ substr($doctor->name, 0, 1) }}
                            </div>
                            <h3 class="text-bold-700 mb-0">{{ $doctor->name }}</h3>
                            <p class="text-muted">{{ $doctor->speciality }}</p>
                            <div class="badge badge-pill badge-light border-primary text-primary mb-2 px-2 py-1">
                                <i class="la la-hospital-o"></i> {{ $doctor->center->name ?? 'غير محدد' }}
                            </div>
                            <hr class="my-2">
                            <div class="row text-left mt-2">
                                <div class="col-12 mb-1"><span class="text-muted"><i class="ft-phone mr-1"></i> الهاتف:</span>
                                    <strong class="float-right text-dark">{{ $doctor->phone }}</strong></div>
                                <div class="col-12"><span class="text-muted"><i
                                            class="ft-map-pin mr-1"></i> العنوان:</span>
                                    <strong class="float-right text-dark">{{ $doctor->address }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-7 col-12">
                        <div class="row">
                            <div class="col-md-6 col-12 mb-2">
                                <div class="card stat-card bg-gradient-primary">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div><h2 class="text-white mb-0">{{ number_format($totalSales ?? 0) }}</h2><span
                                                class="font-medium-1">إجمالي المبيعات</span></div>
                                        <i class="la la-line-chart font-large-3 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-2">
                                <div class="card stat-card bg-gradient-success">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div><h2 class="text-white mb-0">{{ number_format($paidCommission ?? 0) }}</h2>
                                            <span class="font-medium-1">عمولات تم صرفها</span></div>
                                        <i class="la la-money font-large-3 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-2">
                                <div class="card stat-card bg-gradient-info">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div><h2 class="text-white mb-0">{{ $activeDeals->count() ?? 0 }}</h2><span
                                                class="font-medium-1">اتفاقات تارجت جارية</span></div>
                                        <i class="la la-handshake-o font-large-3 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-2">
                                <div class="card stat-card bg-gradient-danger">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div><h2
                                                class="text-white mb-0">{{ number_format(($prepaidRisk ?? 0) + ($pendingCommission ?? 0)) }}</h2>
                                            <span class="font-medium-1">إجمالي المستحقات</span><small
                                                class="d-block font-small-2 opacity-75"></small></div>
                                        <i class="la la-warning font-large-3 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                                        <li class="nav-item"><a class="nav-link active" id="base-tab1" data-toggle="tab"
                                                                aria-controls="tab1" href="#tab1" role="tab"
                                                                aria-selected="true"><i class="la la-file-text"></i> سجل
                                                الفواتير</a></li>
                                        <li class="nav-item"><a class="nav-link" id="base-tab2" data-toggle="tab"
                                                                aria-controls="tab2" href="#tab2" role="tab"
                                                                aria-selected="false"><i class="la la-trophy"></i>
                                                الاتفاقات (Targets)</a></li>

                                    </ul>
                                    <div class="tab-content px-1 pt-1">

                                        <div class="tab-pane active" id="tab1" role="tabpanel"
                                             aria-labelledby="base-tab1">
                                            <div class="table-responsive mt-1">
                                                <table class="table table-hover table-striped mb-0">
                                                    <thead class="bg-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>التاريخ</th>
                                                        <th>الصيدلية</th>
                                                        <th>المندوب</th>
                                                        <th>قيمة الفاتورة</th>
                                                        <th>العمولة</th>
                                                        <th>حالة العمولة</th>
                                                        <th>عرض</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse($invoices as $invoice)
                                                        @php $comm = $invoice->final_total * ($invoice->doctor_commission_percentage / 100); @endphp
                                                        <tr>
                                                            <td>{{ $invoice->id }}</td>
                                                            <td>{{ $invoice->invoice_date }}</td>
                                                            <td>{{ $invoice->pharmacist->name ?? '-' }}</td>
                                                            <td>{{ $invoice->representative->name ?? '-' }}</td>
                                                            <td class="font-weight-bold">{{ number_format($invoice->final_total) }}</td>
                                                            <td class="text-info font-weight-bold">{{ number_format($comm) }}</td>
                                                            <td>
                                                                @if($invoice->doctor_commission_paid)
                                                                    <span class="badge badge-success">مدفوعة</span>
                                                                @else
                                                                    <span class="badge badge-warning">مستحقة</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('admin.invoices.show', $invoice->id) }}"
                                                                   target="_blank"
                                                                   class="btn btn-sm btn-outline-primary"><i
                                                                        class="ft-eye"></i></a></td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center py-3 text-muted">لا توجد
                                                                فواتير مسجلة.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div
                                                class="mt-2 d-flex justify-content-center">{{ $invoices->links() }}</div>
                                        </div>

                                        <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                                            <div class="row mt-2">
                                                @forelse($activeDeals as $deal)
                                                    <div class="col-md-6 col-12 mb-2">
                                                        <div
                                                            class="card deal-card {{ $deal->is_prepaid ? 'prepaid' : 'active' }} box-shadow-1">
                                                            <div class="card-body">

                                                                <div
                                                                    class="d-flex justify-content-between align-items-start">
                                                                    <div>

                                                                        <h5 class="text-bold-600 mb-0">
                                                                            @if($deal->pharmacists->count() > 0)
                                                                                {{ $deal->pharmacists->pluck('name')->implode('، ') }}
                                                                            @else
                                                                                <span class="text-muted">غير محدد</span>
                                                                            @endif
                                                                        </h5>
                                                                        <small class="text-muted">تاريخ
                                                                            البدء: {{ $deal->start_date }}</small>
                                                                    </div>
                                                                    @if($deal->is_prepaid)
                                                                        <span
                                                                            class="badge badge-danger">مدفوع مقدم</span>
                                                                    @else
                                                                        <span class="badge badge-info">جاري العمل</span>
                                                                    @endif
                                                                </div>

                                                                <div class="mt-2">
                                                                    @php
                                                                        $percent = $deal->target_amount > 0 ? ($deal->achieved_amount / $deal->target_amount) * 100 : 0;
                                                                        $percent = min($percent, 100);
                                                                    @endphp
                                                                    <div
                                                                        class="d-flex justify-content-between font-small-3 mb-1">
                                                                        <span>المحقق: <strong>{{ number_format($deal->achieved_amount) }}</strong></span>
                                                                        <span>الهدف: <strong>{{ number_format($deal->target_amount) }}</strong></span>
                                                                    </div>
                                                                    <div class="progress" style="height: 8px;">
                                                                        <div
                                                                            class="progress-bar bg-{{ $percent >= 100 ? 'success' : ($deal->is_prepaid ? 'danger' : 'info') }}"
                                                                            role="progressbar"
                                                                            style="width: {{ $percent }}%"></div>
                                                                    </div>
                                                                    <div
                                                                        class="text-center mt-1 font-small-2 text-muted">{{ number_format($percent, 1) }}
                                                                        % مكتمل
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-2 text-center border-top pt-1">
                                                                    <div class="col-6 border-right"><span
                                                                            class="text-muted font-small-2">العمولة</span>
                                                                        <h6 class="text-bold-700 mb-0">{{ number_format($deal->commission_amount) }}</h6>
                                                                    </div>
                                                                    <div class="col-6"><a
                                                                            href="{{ route('admin.deals.edit', $deal->id) }}"
                                                                            class="btn btn-sm btn-outline-secondary mt-1">تعديل
                                                                            / تفاصيل</a></div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-center py-3">
                                                        <div class="alert alert-light border-primary"><i
                                                                class="ft-info mr-1"></i> لا توجد اتفاقات جارية.
                                                        </div>
                                                    </div>
                                                @endforelse
                                            </div>
                                            @if($completedDealsCount > 0)
                                                <hr><h6 class="text-muted text-center">تم
                                                    إنهاء {{ $completedDealsCount }} اتفاقات سابقة بنجاح.</h6>
                                            @endif
                                        </div>
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
