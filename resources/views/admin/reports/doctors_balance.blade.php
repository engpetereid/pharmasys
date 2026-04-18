@extends('layouts.admin')

@section('title', 'كشف حساب الأطباء')

@section('style')
    <style>
        .bg-light-success { background-color: #e6fffa; }
        .bg-light-danger { background-color: #fff5f5; }
        .balance-positive { color: #28a745; font-weight: bold; }
        .balance-negative { color: #dc3545; font-weight: bold; }
        .balance-neutral { color: #6c757d; font-weight: bold; }
        .table th { vertical-align: middle; text-align: center; }
        .table td { vertical-align: middle; text-align: center; font-size: 1.05rem; }
        .print-header , .print-logo { display: none ;!important; }
        .print-header { display: none; }
        @media print {
            @page { size: A4; margin: 10mm; }
            body { background-color: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print, .main-menu, .header-navbar, .footer, .customizer, .scroll-top,
            .content-header, .card-header .heading-elements, .card-header .heading-elements-toggle,
            form, .btn, .breadcrumb, .heading-elements-toggle, .pagination {
                display: none !important;
            }
            .content-wrapper { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
            .card { border: none !important; box-shadow: none !important; margin-bottom: 0 !important; }

            .table-responsive { overflow: visible !important; display: block !important; }
            .table { width: 100% !important; border-collapse: collapse !important; border: 1px solid #000 !important; }
            .table th, .table td { border: 1px solid #000 !important; padding: 8px !important; color: #000 !important; }
            .table th:last-child, .table td:last-child { display: none !important; }
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
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="print-header-wrapper ">
                <div class="print-header">
                    <h2>تقرير الأرصدة المالية للأطباء</h2>
                    <p>تاريخ الطباعة: {{ date('Y-m-d H:i') }}</p>
                </div>
                <div class="print-logo">
                    <img src="{{ asset('assets/admin/images/slogan.jpg') }}" alt="Logo">
                </div>
            </div>

            <div class="mb-2 content-header row no-print">
                <div class="col-12">
                    <h3 class="content-header-title"> <i class="la la-balance-scale"></i> أرصدة الأطباء </h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card no-print">
                    <div class="card-body">
                        <form action="{{ route('admin.reports.doctors_balance') }}" method="GET">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-0 form-group position-relative has-icon-left">
                                        <input type="text" name="search" class="form-control" placeholder="ابحث باسم الطبيب..." value="{{ request('search') }}">
                                        <div class="form-control-position"><i class="ft-search"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-0 form-group">
                                        <select name="zone_id" class="form-control select2">
                                            <option value="">-- كل المناطق --</option>
                                            @foreach( $zones as $zone)
                                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                                    {{ $zone->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-1 col-md-3">
                                    <select name="center_id" class="form-control select2">
                                        <option value="">-- كل المراكز --</option>
                                        @foreach($centers as $center)
                                            <option value="{{ $center->id }}" {{ request('center_id') == $center->id ? 'selected' : '' }}>
                                                {{ $center->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="btn-group w-100">
                                        <button class="btn btn-primary" type="submit"> <i class="ft-search"></i> بحث</button>
                                        <button class="btn btn-success" type="submit" name="export" value="excel"> <i class="la la-file-excel-o"></i> إكسيل</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header no-print">
                        <h4 class="card-title">الموقف المالي الحالي </h4>
                        <div class="heading-elements">
                            <button onclick="window.print()" class="btn btn-sm btn-secondary"><i class="ft-printer"></i> طباعة التقرير</button>
                        </div>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table mb-0 table-bordered table-hover">
                                    <thead class="bg-light">
                                    <tr>
                                        <th>الطبيب</th>
                                        <th>المركز</th>
                                        <th>إجمالي المبيعات</th>
                                        <th>مسحوبات / مقدمات</th>
                                        <th class="bg-white">الرصيد الصافي</th>
                                        <th class="no-print">الإجراء</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($doctors as $doctor)
                                        @php
                                            $credit = $doctor->total_earned ?? 0;
                                            $debit  = $doctor->total_paid ?? 0;
                                            $net    = $credit - $debit;
                                        @endphp
                                        <tr class="{{ $net < 0 ? 'bg-light-danger' : '' }}">
                                            <td class="text-left text-bold-600">{{ $doctor->name }}</td>
                                            <td>{{ $doctor->center->name ?? '-' }}</td>

                                            <td class="text-success font-weight-bold">
                                                {{ number_format($credit, 2) }}
                                            </td>

                                            <td class="text-danger">
                                                {{ number_format($debit, 2) }}
                                            </td>

                                            <td class="font-large-1">
                                                @if($net > 0)
                                                    <span class="balance-positive">+{{ number_format($net, 2) }}</span>
                                                    <br><span class="badge badge-success font-small-1">له (دائن)</span>
                                                @elseif($net < 0)
                                                    <span class="balance-negative">{{ number_format($net, 2) }}</span>
                                                    <br><span class="badge badge-danger font-small-1">عليه (مدين)</span>
                                                @else
                                                    <span class="balance-neutral">0.00</span>
                                                    <br><span class="badge badge-secondary font-small-1">خالص</span>
                                                @endif
                                            </td>

                                            <td class="no-print">
                                                <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="btn btn-sm btn-outline-info round box-shadow-1">
                                                    <i class="ft-file-text"></i> التفاصيل
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="py-3 text-center text-muted">لا توجد بيانات.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2 no-print">
                                {{ $doctors->links() }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
