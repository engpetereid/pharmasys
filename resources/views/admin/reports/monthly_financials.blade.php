@extends('layouts.admin')

@section('title', 'الملخص المالي الشهري')

@section('style')
    <style>
        .stat-card { border-radius: 8px; overflow: hidden; transition: transform 0.3s; color: white; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card .card-body { padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .stat-card h3 { color: white; margin-bottom: 0; font-weight: bold; font-size: 2rem; }
        .stat-card i { font-size: 3rem; opacity: 0.4; }

        .table th { background-color: #f4f5fa; border-top: none; }
        .table td { vertical-align: middle; }

        @media print {
            .no-print, .main-menu, .header-navbar, .filter-card, .footer { display: none !important; }
            body { background-color: #fff; }
            .app-content { margin: 0 !important; padding: 0 !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; break-inside: avoid; }
            .stat-card { color: #000 !important; background: #fff !important; border: 2px solid #ccc !important; }
            .stat-card h3, .stat-card span { color: #000 !important; }
            .stat-card i { display: none; }
        }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2 no-print">
                <div class="content-header-left col-md-8 col-12">
                    <h3 class="content-header-title"> <i class="la la-bar-chart"></i> الملخص المالي الشهري (Cash Flow) </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                                <li class="breadcrumb-item active">الماليات الشهرية</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-4 col-12 text-right">
                    <button onclick="window.print()" class="btn btn-secondary box-shadow-2">
                        <i class="ft-printer"></i> طباعة التقرير
                    </button>
                </div>
            </div>

            <div class="content-body">
                {{-- 1. فلتر الشهر والسنة والمنطقة --}}
                <div class="card filter-card box-shadow-1 border-top-primary border-top-3 no-print">
                    <div class="card-body">
                        <form action="{{ route('admin.reports.monthly_financials') }}" method="GET">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="text-bold-600">اختر الشهر</label>
                                        <select name="month" class="form-control">
                                            @foreach($months as $num => $name)
                                                <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="text-bold-600">اختر السنة</label>
                                        <select name="year" class="form-control">
                                            @foreach($years as $yr)
                                                <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>
                                                    {{ $yr }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="text-bold-600">اختر منطقة</label>
                                        <select name="zone" class="form-control">
                                            <option value="">-- عرض الكل --</option>
                                            @foreach($zones as $id => $name)
                                                <option value="{{ $id }}" {{ $selectedZone == $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="btn-group w-100">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ft-filter"></i> عرض التقرير
                                        </button>
                                        <button type="submit" name="export" value="excel" class="btn btn-success">
                                            <i class="la la-file-excel-o"></i> إكسيل
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ترويسة الطباعة --}}
                <div class="d-none d-print-block mb-2 text-center">
                    <h2>التقرير المالي لشهر {{ $months[(int)$selectedMonth] }} {{ $selectedYear }}</h2>
                    <p>تاريخ استخراج التقرير: {{ date('Y-m-d H:i') }}</p>
                    <hr>
                </div>

                {{-- 2. بطاقات الإحصائيات --}}
                <div class="row">
                    <div class="col-xl-4 col-md-6 col-12">
                        <div class="card stat-card bg-gradient-x-success box-shadow-1">
                            <div class="card-body">
                                <div>
                                    <span>إجمالي التحصيلات (الدخل)</span>
                                    <h3>{{ number_format($totalIncome, 2) }}</h3>
                                </div>
                                <i class="la la-arrow-circle-up text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 col-12">
                        <div class="card stat-card bg-gradient-x-danger box-shadow-1">
                            <div class="card-body">
                                <div>
                                    <span>إجمالي المصروفات </span>
                                    <h3>{{ number_format($totalExpenses, 2) }}</h3>
                                </div>
                                <i class="la la-arrow-circle-down text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-12 col-12">
                        <div class="card stat-card {{ $netProfit >= 0 ? 'bg-gradient-x-info' : 'bg-gradient-x-warning' }} box-shadow-1">
                            <div class="card-body">
                                <div>
                                    <span>صافي التدفق النقدي (الرصيد)</span>
                                    <h3>{{ number_format($netProfit, 2) }}</h3>
                                </div>
                                <i class="la la-balance-scale text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. الجداول التفصيلية --}}
                <div class="row match-height">
                    {{-- جدول الدخل (التحصيلات) --}}
                    <div class="col-xl-6 col-lg-12">
                        <div class="card border-top-success border-top-3">
                            <div class="card-header pb-0">
                                <h4 class="card-title text-success"><i class="la la-plus-circle"></i> تفاصيل التحصيلات (الدخل)</h4>
                                <p class="font-small-3 text-muted mt-1">المبالغ التي تم تحصيلها فعلياً خلال هذا الشهر.</p>
                            </div>
                            <div class="card-content">
                                <div class="card-body pt-0">
                                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead class="bg-light position-sticky" style="top: 0; z-index: 10;">
                                            <tr>
                                                <th>التاريخ</th>
                                                <th>رقم الفاتورة</th>
                                                <th>العميل (الصيدلية)</th>
                                                <th>المبلغ</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.invoices.show', $payment->invoice_id) }}" target="_blank" class="text-bold-600">
                                                            #{{ $payment->invoice->serial_number ?? $payment->invoice_id }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $payment->invoice->pharmacist->name ?? '-' }}</td>
                                                    <td class="text-success font-weight-bold">+{{ number_format($payment->amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">لا توجد تحصيلات مسجلة في هذا الشهر.</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                            @if($payments->count() > 0)
                                                <tfoot class="bg-light font-weight-bold">
                                                <tr>
                                                    <td colspan="3" class="text-right">الإجمالي:</td>
                                                    <td class="text-success">{{ number_format($totalIncome, 2) }}</td>
                                                </tr>
                                                </tfoot>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- جدول المصروفات --}}
                    <div class="col-xl-6 col-lg-12">
                        <div class="card border-top-danger border-top-3">
                            <div class="card-header pb-0">
                                <h4 class="card-title text-danger"><i class="la la-minus-circle"></i> تفاصيل المصروفات (النثريات)</h4>
                                <p class="font-small-3 text-muted mt-1">مصروفات وعمولات الأطباء التي تم صرفها خلال هذا الشهر.</p>
                            </div>
                            <div class="card-content">
                                <div class="card-body pt-0">
                                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead class="bg-light position-sticky" style="top: 0; z-index: 10;">
                                            <tr>
                                                <th>التاريخ</th>
                                                <th>المنطقة (Zone)</th>
                                                <th>البيان (الوصف)</th>
                                                <th>المبلغ</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($expenses as $expense)
                                                <tr>
                                                    <td>{{ Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}</td>
                                                    <td>
                                                        <span class="badge badge-secondary">{{ $expense->zone->name ?? '-' }}</span>
                                                    </td>
                                                    <td><span class="font-small-3" title="{{ $expense->description }}">{{ Str::limit($expense->description, 40) }}</span></td>
                                                    <td class="text-danger font-weight-bold">-{{ number_format($expense->amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">لا توجد مصروفات مسجلة في هذا الشهر.</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                            @if($expenses->count() > 0)
                                                <tfoot class="bg-light font-weight-bold">
                                                <tr>
                                                    <td colspan="3" class="text-right">الإجمالي:</td>
                                                    <td class="text-danger">{{ number_format($totalExpenses, 2) }}</td>
                                                </tr>
                                                </tfoot>
                                            @endif
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
