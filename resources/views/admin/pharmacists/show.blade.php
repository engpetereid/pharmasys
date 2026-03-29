@extends('layouts.admin')

@section('title', 'ملف الصيدلية: ' . $pharmacist->name)

@section('style')
    <style>
        .profile-header { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%; text-align: center; }
        .avatar-circle { width: 100px; height: 100px; font-size: 3rem; line-height: 100px; margin: 0 auto 15px; display: block; }

        .stat-card {
            border: none; border-radius: 12px; color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100px;
            display: flex; align-items: center;
        }
        .stat-card:hover { transform: translateY(-5px); }

        .bg-gradient-primary { background: linear-gradient(45deg, #666ee8, #764ba2); }
        .bg-gradient-success { background: linear-gradient(45deg, #11998e, #38ef7d); }
        .bg-gradient-danger { background: linear-gradient(45deg, #ff5f6d, #ffc371); }

        .nav-tabs .nav-link { font-weight: 600; color: #555; }
        .nav-tabs .nav-link.active { border-top: 3px solid #666ee8; color: #666ee8; }
        .alert-beacon {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #ff4b5c;
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 0 0 rgba(255, 75, 92, 0.4);
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(255, 75, 92, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(255, 75, 92, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 75, 92, 0); }
        }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-hospital-o"></i> ملف الصيدلية </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.pharmacists.index') }}">الصيدليات</a></li>
                                <li class="breadcrumb-item active">{{ $pharmacist->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12 text-right">
                    <a href="{{ route('admin.pharmacists.edit', $pharmacist->id) }}" class="btn btn-warning box-shadow-2">
                        <i class="ft-edit"></i> تعديل البيانات
                    </a>
                </div>
            </div>

            <div class="content-body">

                <div class="row match-height">
                    <div class="col-xl-4 col-lg-5 col-12">
                        <div class="profile-header">
                            <div class="avatar-circle bg-gradient-primary rounded-circle text-white mb-2 box-shadow-2">
                                <i class="la la-hospital-o"></i>
                            </div>
                            <h3 class="text-bold-700 mb-0">{{ $pharmacist->name }}</h3>
                            <p class="text-muted">عميل (صيدلية)</p>

                            <div class="badge badge-pill badge-light border-primary text-primary mb-2 px-2 py-1">
                                <i class="la la-map-marker"></i> {{ $pharmacist->center->name ?? 'غير محدد' }}
                            </div>

                            <hr class="my-2">

                            <div class="row text-left mt-2">
                                <div class="col-12 mb-1">
                                    <span class="text-muted"><i class="ft-phone mr-1"></i> الهاتف:</span>
                                    <strong class="float-right text-dark">{{ $pharmacist->phone }}</strong>
                                </div>
                                <div class="col-12 mb-1">
                                    <span class="text-muted"><i class="ft-calendar mr-1"></i> تاريخ التسجيل:</span>
                                    <strong class="float-right text-dark">{{ $pharmacist->created_at->format('Y-m-d') }}</strong>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted"><i class="ft-map mr-1"></i> العنوان:</span>
                                    <p class="text-dark mt-1 mb-0 font-small-3">{{ $pharmacist->address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-7 col-12">
                        <div class="row">
                            <div class="col-md-12 col-12 mb-2">
                                <div class="card stat-card bg-gradient-primary" style="height: 120px;">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="text-white mb-0">{{ number_format($totalSales) }}</h2>
                                            <span class="font-medium-1">إجمالي المسحوبات (المبيعات)</span>
                                        </div>
                                        <i class="la la-cart-plus font-large-3 opacity-50"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-12 mb-2">
                                <div class="card stat-card bg-gradient-success">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="text-white mb-0">{{ number_format($totalPaid) }}</h2>
                                            <span class="font-medium-1">إجمالي المدفوعات</span>
                                        </div>
                                        <i class="la la-check-circle font-large-3 opacity-50"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-12 mb-2">
                                <div class="card stat-card bg-gradient-danger">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="text-white mb-0">{{ number_format($totalDue) }}</h2>
                                            <span class="font-medium-1">المتبقي (مديونية)</span>
                                        </div>
                                        <i class="la la-money font-large-3 opacity-50"></i>
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
                                    <h4 class="card-title mb-2"><i class="ft-file-text"></i> سجل الفواتير والتعاملات</h4>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped mb-0">
                                            <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>التاريخ</th>
                                                <th>الأطباء</th>
                                                <th>المندوب</th>
                                                <th>قيمة الفاتورة</th>
                                                <th>المدفوع</th>
                                                <th>المتبقي</th>
                                                <th>الحالة</th>
                                                <th>عرض</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($invoices as $invoice)
                                                @php
                                                    // حساب النسبة باستخدام PHP المباشر
                                                    $hasHighDiscount = false;
                                                    if ($invoice->total_amount > 0) {
                                                        $discountPercentage = ($invoice->total_discount / $invoice->total_amount) * 100;
                                                        $hasHighDiscount = $discountPercentage >= 51;
                                                    }
                                                @endphp

                                                <tr>
                                                    <td>
                                                        @if($hasHighDiscount)
                                                            <span class="alert-beacon" title="تنبيه: الفاتورة بها نسبة خصم 51% أو أكثر"></span>
                                                        @endif
                                                        {{ $invoice->serial_number ?? $invoice->id }}
                                                    </td>
                                                    <td>{{ $invoice->invoice_date }}</td>
                                                    <td>
                                                        @if($invoice->doctors && $invoice->doctors->count() > 0)
                                                            {{ $invoice->doctors->pluck('name')->implode('، ') }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $invoice->representative->name ?? '-' }}</td>
                                                    <td class="font-weight-bold">{{ number_format($invoice->final_total) }}</td>
                                                    <td class="text-success">{{ number_format($invoice->paid_amount) }}</td>
                                                    <td class="text-danger">{{ number_format($invoice->remaining_amount) }}</td>
                                                    <td>
                                                        @if($invoice->status == 1)
                                                            <span class="badge badge-success">مدفوع</span>
                                                        @elseif($invoice->status == 2)
                                                            <span class="badge badge-warning">آجل</span>
                                                        @else
                                                            <span class="badge badge-info">جزئي</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="ft-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="9" class="text-center py-3 text-muted">لا توجد فواتير مسجلة لهذه الصيدلية.</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-center">{{ $invoices->links() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
