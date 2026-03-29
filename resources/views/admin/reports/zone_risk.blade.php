@extends('layouts.admin')

@section('title', 'تقرير نسبة الجهاز (معدل الهدر)')

@section('style')
    <style>
        @media print {
            .no-print { display: none !important; }

            .card { border: none !important; box-shadow: none !important; }
            .table th, .table td { border: 1px solid #000 !important; padding: 5px !important; }
            .badge-danger { color: #000 !important; border: 1px solid #000; background: none !important; }
            .badge-success { color: #000 !important; border: 1px solid #000; background: none !important; }
            .bg-danger-light { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
             .print-header-wrapper {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                width: 100% !important;
                margin-bottom: 10px;
            }

            .print-header {
                display: block !important;
                text-align: right;
                margin-left: 40px
            }

            .print-logo {
                display: block !important;
                text-align: left;
            }

            .print-logo img {
                width: 120px;
                height: auto;
                margin-right: 40px
            }
        }
        .print-header , .print-logo { display: none ;!important; }
        .bg-danger-light { background-color: #fff5f5; }
        .risk-value { font-size: 1.2rem; font-weight: 800; }
        .table th { vertical-align: middle !important; }
    </style>
@endsection

@section('content')

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="mb-2 content-header row no-print">
                <div class="col-12">
                    <h3 class="content-header-title"> <i class="la la-pie-chart"></i> تقرير نسبة الجهاز (Risk Ratio Analysis) </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                                <li class="breadcrumb-item active">نسبة الجهاز</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="print-header-wrapper ">
                    <div class="print-header">
                        <h2> تقرير نسبة الجهاز </h2>
                        <p>تاريخ الطباعة: {{ date('Y-m-d H:i') }} </p>
                    </div>

                    <div class="print-logo">
                        <img src="{{ asset('assets/admin/images/slogan.jpg') }}" alt="Logo">
                    </div>
                </div>
                <div class="card no-print border-top-primary border-top-3">
                    <div class="card-body">
                        <form action="{{ route('admin.reports.zone_risk.index') }}" method="GET" id="filterForm">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="mb-0 form-group">
                                        <label>من تاريخ</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-0 form-group">
                                        <label>إلى تاريخ</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-0 form-group">
                                        <label>المنطقة (Zone)</label>
                                        <select name="zone_id" class="form-control select2">
                                            <option value="">-- عرض الكل --</option>
                                            @foreach($allZones as $zone)
                                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                                    {{ $zone->name }}{{ $zone->line ? ' (Line ' . $zone->line . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="btn-group w-100">
                                        <button type="submit" class="btn btn-primary"><i class="ft-search"></i> عرض التقرير</button>
                                        <button type="submit" form="filterForm" formaction="{{ route('admin.reports.zone_risk.export') }}" class="btn btn-success">
                                            <i class="la la-file-excel-o"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تحليل أداء المناطق (بناءً على المصروفات والخصومات)</h4>
                        <div class="heading-elements no-print">
                            <button onclick="window.print()" class="btn btn-secondary btn-sm"><i class="ft-printer"></i> طباعة</button>
                        </div>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table mb-0 text-center table-bordered">
                                    <thead class="bg-light">
                                    <tr>
                                        <th rowspan="2">المنطقة</th>
                                        <th rowspan="2">إجمالي الجمهوري</th>
                                        <th colspan="2">المصروفات</th>
                                        <th colspan="2">الخصومات (للصيادلة)</th>
                                        <th rowspan="2" class="bg-white">نسبة الجهاز الكلية<br><small>(خطر > 40%)</small></th>
                                        <th rowspan="2" class="no-print">إجراءات</th>
                                    </tr>
                                    <tr>
                                        <th>القيمة</th>
                                        <th>النسبة %</th>
                                        <th>متوسط الخصم %</th>
                                        <th>القيمة التقريبية</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($reportData as $row)
                                        <tr class="{{ $row['status'] == 'danger' ? 'bg-danger-light' : '' }}">
                                            <td class="text-left">
                                                <strong class="text-dark">{{ $row['zone_name'] }}</strong>
                                                <div class="mt-1">
                                                    <span class="badge badge-sm badge-{{ $row['line'] == 1 ? 'info' : 'warning' }}">Line {{ $row['line'] }}</span>
                                                </div>
                                            </td>

                                            <td class="font-weight-bold">{{ number_format($row['public_price'], 2) }}</td>

                                            <td class="text-danger">{{ number_format($row['total_expenses'], 2) }}</td>
                                            <td class="text-danger font-weight-bold">{{ $row['expense_ratio'] }}%</td>

                                            <td class="text-info font-weight-bold">{{ $row['avg_discount_percentage'] }}%</td>
                                            <td class="text-sm text-muted">{{ number_format($row['total_discount_value'], 2) }}</td>

                                            <td>
                                                <span class="risk-value {{ $row['status'] == 'danger' ? 'text-danger' : 'text-success' }}">
                                                    {{ $row['risk_ratio'] }}%
                                                </span>
                                            </td>

                                            <td class="no-print">
                                                <a href="{{ route('admin.zones.show', $row['id']) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="ft-eye"></i> المنطقة
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="py-3 text-center text-muted">لا توجد بيانات متاحة لهذه الفترة.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 no-print">
                                <div class="alert alert-light border-info">
                                    <h5 class="alert-heading text-info"><i class="ft-info"></i> كيفية الحساب الجديدة:</h5>
                                    <ul class="pl-1 mb-0">
                                        <li><strong>نسبة المصروفات:</strong> (إجمالي مصروفات المنطقة ÷ إجمالي المبيعات بالجمهور) × 100</li>
                                        <li><strong>متوسط الخصم:</strong> (إجمالي قيمة الخصم الممنوح للصيادلة ÷ إجمالي المبيعات بالجمهور) × 100</li>
                                        <li><strong>نسبة الجهاز:</strong> مجموع النسبتين (نسبة المصروفات + متوسط الخصم).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
