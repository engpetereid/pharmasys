@extends('layouts.admin')

@section('title', 'لوحة قيادة Line ' . $lineId)

@section('style')
    <style>

        @if($lineId == 1)
        :root { --main-color: #1E9FF2; --gradient-start: #1E9FF2; --gradient-end: #00BFA5; --shadow-color: rgba(30, 159, 242, 0.4); }
        @else
        :root { --main-color: #FF9149; --gradient-start: #FF9149; --gradient-end: #FF5B5C; --shadow-color: rgba(255, 91, 92, 0.4); }
        @endif

    .crypto-card-3 { border: none; border-radius: 12px; color: #fff; background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end)); transition: all 0.3s; overflow: hidden; display: block; text-decoration: none !important; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .crypto-card-3:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 8px 25px var(--shadow-color); color: #fff; }
        .card-icon-bg { background-color: rgba(255, 255, 255, 0.25); border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin: 0 auto; backdrop-filter: blur(5px); }

        .line-header { background-color: #fff; padding: 20px; border-radius: 12px; border-right: 6px solid var(--main-color); box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .line-badge { background-color: var(--main-color); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; vertical-align: middle; }
        .stat-box { text-align: center; padding: 0 15px; border-left: 1px solid #eee; }
        .stat-box h4 { color: var(--main-color); font-weight: bold; margin-bottom: 0; }

        .table-hover tbody tr:hover { background-color: #f0f2f5; cursor: pointer; }
        .text-theme { color: var(--main-color) !important; }

        .risk-card-safe { background: #fff; border: 2px solid #28a745; }
        .risk-card-danger { background: #fff; border: 2px solid #dc3545; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } }


        .filter-controls select { border-color: var(--main-color); color: var(--main-color); font-weight: bold; }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">

            <div class="row">
                <div class="col-12">
                    <div class="line-header d-flex flex-wrap justify-content-between align-items-center">
                        <div class="line-title mb-2 mb-md-0">
                            <h2 class="text-bold-700 mb-0">لوحة قيادة <span class="line-badge">Line {{ $lineId }}</span></h2>
                            <p class="text-muted mb-0">تحليل الأداء للفترة المختارة</p>
                        </div>


                        <div class="filter-section">
                            <form action="{{ route('admin.dashboard.line', $lineId) }}" method="GET" class="form-inline">
                                <div class="filter-controls d-flex align-items-center">
                                    <label class="mr-1 text-bold-600">الفترة:</label>
                                    <select name="month" class="form-control mr-1" onchange="this.form.submit()">
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }} ({{ $m }})
                                            </option>
                                        @endfor
                                    </select>
                                    <select name="year" class="form-control" onchange="this.form.submit()">

                                        @for($y = date('Y'); $y >= 2024; $y--)
                                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>

                        <div class="d-none d-md-flex">
                            <div class="stat-box">
                                <h4>{{ $zonesCount }}</h4>
                                <span><i class="la la-map-marker"></i> منطقة</span>
                            </div>
                            <div class="stat-box" style="border-left: none;">
                                <h4>{{ $representativesCount }}</h4>
                                <span><i class="la la-users"></i> مندوب</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <div class="row match-height">
                    {{-- مبيعات اليوم --}}
                    <div class="col-xl-3 col-md-6 col-12">
                        <a href="{{ route('admin.invoices.index', ['type' => 'today', 'line' => $lineId]) }}" class="card crypto-card-3 pull-up">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4 align-self-center"><div class="card-icon-bg"><i class="la la-cart-plus font-large-2 text-white"></i></div></div>
                                        <div class="col-8 pl-2 align-self-center">
                                            <h6 class="text-white opacity-75 mb-1">مبيعات اليوم</h6>
                                            <h3 class="text-white text-bold-700 mb-0">{{ number_format($todaySales) }} <span class="font-medium-1">ج.م</span></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- مبيعات الشهر المختار --}}
                    <div class="col-xl-3 col-md-6 col-12">
                        <a href="{{ route('admin.invoices.index', ['month' => $selectedMonth, 'year' => $selectedYear, 'line' => $lineId]) }}" class="card crypto-card-3 pull-up">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4 align-self-center"><div class="card-icon-bg"><i class="la la-calendar-check-o font-large-2 text-white"></i></div></div>
                                        <div class="col-8 pl-2 align-self-center">
                                            <h6 class="text-white opacity-75 mb-1">مبيعات شهر {{ $selectedMonth }}</h6>
                                            <h3 class="text-white text-bold-700 mb-0">{{ number_format($monthSales) }} <span class="font-medium-1">ج.م</span></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- إجمالي المستحقات --}}
                    <div class="col-xl-3 col-md-6 col-12">
                        <a href="{{ route('admin.invoices.index', ['type' => 'due', 'line' => $lineId]) }}" class="card crypto-card-3 pull-up" style="background: linear-gradient(45deg, #FF5B5C, #FF9149) !important;">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-4 align-self-center"><div class="card-icon-bg"><i class="la la-money font-large-2 text-white"></i></div></div>
                                        <div class="col-8 pl-2 align-self-center">
                                            <h6 class="text-white opacity-75 mb-1">إجمالي المستحقات</h6>
                                            <h3 class="text-white text-bold-700 mb-0">{{ number_format($totalDue) }} <span class="font-medium-1">ج.م</span></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- نسبة الجهاز --}}
                    <div class="col-xl-3 col-md-6 col-12">
                        <a href="{{ route('admin.reports.zone_risk.index', ['line' => $lineId, 'start_date' => date('Y-m-d', mktime(0,0,0,$selectedMonth,1,$selectedYear)), 'end_date' => date('Y-m-t', mktime(0,0,0,$selectedMonth,1,$selectedYear))]) }}"
                           class="card pull-up {{ $riskRatio > 40 ? 'risk-card-danger' : 'risk-card-safe' }}">
                            <div class="card-content">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">نسبة الجهاز (شهر {{ $selectedMonth }})</h6>
                                    <h2 class="text-bold-700 {{ $riskRatio > 40 ? 'text-danger' : 'text-success' }}" style="font-size: 2.5rem;">{{ number_format($riskRatio, 1) }}%</h2>
                                    @if($riskRatio > 40)
                                        <span class="badge badge-danger">تجاوز الحد (خطر)</span>
                                    @else
                                        <span class="badge badge-success">في النطاق الآمن</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row match-height">
                    {{-- أعلى الصيدليات --}}
                    <div class="col-xl-6 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title text-theme"><i class="la la-hospital-o"></i> أفضل عملاء (شهر {{ $selectedMonth }})</h4>
                            </div>
                            <div class="card-content">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                        <tr><th>الصيدلية</th><th>المركز</th><th>المبيعات</th></tr>
                                        </thead>
                                        <tbody>
                                        @forelse($topPharmacists as $pharma)
                                            <tr onclick="window.location='{{ route('admin.reports.pharmacist', $pharma->id) }}'">
                                                <td><span class="text-bold-600">{{ $pharma->name }}</span></td>
                                                <td class="text-muted">{{ $pharma->center->name ?? '-' }}</td>
                                                <td class="text-success font-weight-bold">{{ number_format($pharma->total_sales) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-2">لا توجد مبيعات مسجلة في هذا الشهر.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- أعلى الأطباء --}}
                    <div class="col-xl-6 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title text-theme"><i class="la la-user-md"></i> أفضل أطباء (شهر {{ $selectedMonth }})</h4>
                            </div>
                            <div class="card-content">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                        <tr><th>الطبيب</th><th>التخصص</th><th>المبيعات</th></tr>
                                        </thead>
                                        <tbody>
                                        @forelse($topDoctors as $doc)
                                            <tr onclick="window.location='{{ route('admin.reports.doctors.show', $doc->id) }}'">
                                                <td><span class="text-bold-600">{{ $doc->name }}</span></td>
                                                <td class="text-muted">{{ $doc->speciality ?? '-' }}</td>
                                                <td class="text-info font-weight-bold">{{ number_format($doc->total_sales) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-2">لا توجد مبيعات مسجلة في هذا الشهر.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--  أحدث الفواتير للشهر المختار --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"> <i class="ft-file-text"></i> فواتير شهر {{ $selectedMonth }}/{{ $selectedYear }} (Line {{ $lineId }})</h4>
                                <a href="{{ route('admin.invoices.index', ['month' => $selectedMonth, 'year' => $selectedYear, 'line' => $lineId]) }}" class="btn btn-sm btn-outline-secondary float-right">عرض الجدول الكامل</a>
                            </div>
                            <div class="card-content">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                        <tr><th>#</th><th>التاريخ</th><th>الصيدلية</th><th>الإجمالي</th><th>المدفوع</th><th>الحالة</th><th>عرض</th></tr>
                                        </thead>
                                        <tbody>
                                        @foreach($latestInvoices as $invoice)
                                            <tr>
                                                <td>{{ $invoice->id }}</td>
                                                <td>{{ $invoice->invoice_date }}</td>
                                                <td>{{ $invoice->pharmacist->name ?? '-' }}</td>
                                                <td class="font-weight-bold">{{ number_format($invoice->final_total) }}</td>
                                                <td class="text-success">{{ number_format($invoice->paid_amount) }}</td>
                                                <td>
                                                    @if($invoice->status == 1) <span class="badge badge-success">مدفوع</span>
                                                    @elseif($invoice->status == 2) <span class="badge badge-warning">آجل</span>

                                                    @else <span class="badge badge-info">جزئي</span> @endif
                                                </td>
                                                <td><a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-sm btn-icon btn-pure btn-default"><i class="ft-eye"></i></a></td>
                                            </tr>
                                        @endforeach
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
@endsection
