@extends('layouts.admin')

@section('title', 'سجل فواتير المبيعات')

@section('style')
    <style>
        .filter-card { border-left: 4px solid #1E9FF2; }

        .stat-card { border-radius: 5px; overflow: hidden; transition: all 0.3s; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: #fff; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); }
        .stat-card .card-body { padding: 1.5rem; }
        .stat-card-info { border-left: 5px solid #00cfe8; }
        .stat-card-primary { border-left: 5px solid #1e9ff2; }
        .stat-card-success { border-left: 5px solid #28d094; }
        .stat-card-danger { border-left: 5px solid #ff5b5c; }
        .stat-icon { font-size: 2.5rem; opacity: 0.2; position: absolute; left: 20px; top: 50%; transform: translateY(-50%); }

        .table th { border-top: none; background-color: #F5F7FA; color: #555; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        .table td { vertical-align: middle; }
        .invoice-serial { font-family: 'Courier New', Courier, monospace; font-weight: bold; letter-spacing: 1px; }

        .print-header { display: none; }

        @media print {
            @page { size: A4 ; margin: 0; }

            body { margin: 10mm; background-color: #fff !important; -webkit-print-color-adjust: exact; }

            .no-print, .content-header, .filter-card, .footer, .main-menu, .header-navbar, .customizer, .scroll-top { display: none !important; }

            .app-content, .content-wrapper, .content-body { margin: 0 !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; margin-bottom: 10px !important; }

            .print-header { display: block; width: 100%; border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
            .print-header h2 { font-size: 24pt; margin: 0; color: #000; }
            .print-header p { margin: 5px 0 0; font-size: 10pt; color: #555; }

            .card_con {
                display: flex !important;
                flex-wrap: nowrap !important;
                gap: 20px;
                justify-content: center !important;
                margin-bottom: 20px;
            }
            .card_con .col-xl-3, .card_con .col-md-6 {
                flex: 0 0 22% !important;
                max-width: 22% !important;
                padding: 0 !important;
            }


            .stat-card {
                border: 3px solid #ccc !important;
                box-shadow: none !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
            .stat-card .card-body { padding: 10px !important; }
            .stat-card h6 { font-size: 10pt !important; color: #000 !important; font-weight: bold; }
            .stat-card h3 { font-size: 14pt !important; color: #000 !important; }
            .stat-icon { display: none; }

            .table-responsive { overflow: visible !important; }
            .table { width: 100% !important; border-collapse: collapse !important; font-size: 10pt; }
            .table th { background-color: #eee !important; color: #000 !important; border: 3px solid #333 !important; padding: 5px 8px; }
            .table td { border: 3px solid #ccc !important; padding: 5px 8px; color: #000 !important; }

            .table th:last-child, .table td:last-child { display: none !important; }

            .badge {  background: transparent !important; color: #000 !important; padding: 2px 5px; }
            .text-primary, .text-info, .text-success, .text-danger { color: #000 !important; }

            tr { page-break-inside: avoid; }

            a { text-decoration: none !important; color: #000 !important; }
        }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">

           <div class="print-header">
                <div class="row align-items-center">
                    <div class="col-4">
                        <h2>سجل فواتير المبيعات</h2>
                        <p>تاريخ الطباعة: {{ date('Y-m-d H:i') }}</p>
                    </div>

                    <div class="text-center col-4">
                        @if(request('start_date') || request('end_date'))
                            <p>الفترة: {{ request('start_date') ?? '...' }} إلى {{ request('end_date') ?? '...' }}</p>
                        @endif
                    </div>

                    <div class="col-4 text-end">
                        <img src="{{ asset('assets/admin/images/slogan.jpg') }}"
                            alt="Logo"
                            style="height: 80px;"  >
                    </div>
                </div>
           </div>


            <div class="mb-2 content-header row no-print">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-file-text"></i> سجل فواتير المبيعات </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item active">جميع الفواتير</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="text-right content-header-right col-md-6 col-12">
                    <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-min-width box-shadow-2">
                        <i class="ft-plus"></i> فاتورة جديدة
                    </a>
                </div>
            </div>

            <div class="content-body">

                <div class="row card_con">
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card stat-card stat-card-info">
                            <div class="card-body position-relative">
                                <h6 class="mb-1 text-uppercase text-muted">إجمالي الجمهور (قبل الخصم)</h6>
                                <h3 class="mb-0 font-weight-bold text-info">{{ number_format($stats['total_public_sales'] ?? 0) }}</h3>
                                <i class="la la-users stat-icon text-info"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card stat-card stat-card-primary">
                            <div class="card-body position-relative">
                                <h6 class="mb-1 text-uppercase text-muted">صافي المبيعات (المطلوب)</h6>
                                <h3 class="mb-0 font-weight-bold text-primary">{{ number_format($stats['total_net_sales'] ?? 0) }}</h3>
                                <i class="la la-file-text stat-icon text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card stat-card stat-card-success">
                            <div class="card-body position-relative">
                                <h6 class="mb-1 text-uppercase text-muted">تم تحصيله (الخزنة)</h6>
                                <h3 class="mb-0 font-weight-bold text-success">{{ number_format($stats['total_collected'] ?? 0) }}</h3>
                                <i class="la la-money stat-icon text-success"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card stat-card stat-card-danger">
                            <div class="card-body position-relative">
                                <h6 class="mb-1 text-uppercase text-muted">آجل (متبقي)</h6>
                                <h3 class="mb-0 font-weight-bold text-danger">{{ number_format($stats['total_remaining'] ?? 0) }}</h3>
                                <i class="la la-warning stat-icon text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card filter-card box-shadow-1 no-print">
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form action="{{ route('admin.invoices.index') }}" method="GET" id="searchForm">
                                <div class="row">
                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">من تاريخ</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    </div>

                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">إلى تاريخ</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>

                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">المركز</label>
                                        <select name="center_id" class="form-control select2">
                                            <option value="">-- كل المراكز --</option>
                                            @foreach($centers as $center)
                                                <option value="{{ $center->id }}" {{ request('center_id') == $center->id ? 'selected' : '' }}>
                                                    {{ $center->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">المناطق</label>
                                        <select name="zone_id" class="form-control select2">
                                            <option value="">-- كل المناطق --</option>
                                            @foreach($zones as $zone)
                                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                                    {{ $zone->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">الصيدلية</label>
                                        <select name="pharmacist_id" class="form-control select2">
                                            <option value="">-- كل العملاء --</option>
                                            @foreach($pharmacists as $ph)
                                                <option value="{{ $ph->id }}" {{ request('pharmacist_id') == $ph->id ? 'selected' : '' }}>
                                                    {{ $ph->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">الدكتور</label>
                                        <select name="doctor_id" class="form-control select2">
                                            <option value="">-- كل الدكاترة --</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">المندوب</label>
                                        <select name="representative_id" class="form-control select2">
                                            <option value="">-- كل المناديب --</option>
                                            @foreach($representatives as $rep)
                                                <option value="{{ $rep->id }}" {{ request('representative_id') == $rep->id ? 'selected' : '' }}>
                                                    {{ $rep->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">رقم الفاتورة</label>
                                        <div class="position-relative has-icon-left">
                                            <input type="text" class="form-control" name="serial_number" placeholder="بحث برقم الفاتورة..." value="{{ request('serial_number') }}">
                                            <div class="form-control-position"><i class="ft-search"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">الخط (Line)</label>
                                        <select name="line" class="form-control">
                                            <option value="">-- الكل --</option>
                                            <option value="1" {{ request('line') == '1' ? 'selected' : '' }}>Line 1</option>
                                            <option value="2" {{ request('line') == '2' ? 'selected' : '' }}>Line 2</option>
                                        </select>
                                    </div>

                                    <div class="mb-1 col-md-3">
                                        <label class="text-muted font-small-3">حالة الدفع</label>
                                        <select name="status" class="form-control">
                                            <option value="">-- الكل --</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>مدفوع</option>
                                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>آجل</option>
                                            <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>جزئي</option>
                                        </select>
                                    </div>

                                    <div class="gap-1 mb-1 col-md-6 d-flex align-items-end">
                                        <button type="submit" class="mr-1 btn btn-primary flex-grow-1">
                                            <i class="ft-filter"></i> تصفية
                                        </button>

                                        <button type="submit" formaction="{{ route('admin.invoices.export') }}" class="mr-1 btn btn-success flex-grow-1">
                                            <i class="la la-file-excel-o"></i> Excel
                                        </button>

                                        <button type="button" onclick="window.print()" class="mr-1 btn btn-secondary flex-grow-1">
                                            <i class="ft-printer"></i> طباعة
                                        </button>

                                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary" title="إلغاء الفلاتر">
                                            <i class="ft-x"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-content collapse show">
                                <div class="card-body card-dashboard">
                                    <div class="table-responsive">
                                        <table class="table table-hover w-100">
                                            <thead>
                                            <tr>
                                                <th>رقم الفاتورة</th>
                                                <th>التاريخ</th>
                                                <th>الصيدلية</th>
                                                <th>فريق التوزيع</th>
                                                <th>الماليات</th>
                                                <th>الحالة</th>
                                                <th class="text-center">الإجراءات</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            @forelse($invoices as $invoice)
                                                <tr>
                                                    <td>
                                                        <span class="text-bold-700 text-primary invoice-serial">{{ $invoice->serial_number ?? '#' . $invoice->id }}</span>
                                                        <div class="mt-1">
                                                            @if($invoice->line == 1)
                                                                <span class="badge badge-sm badge-info">Line 1</span>
                                                            @else
                                                                <span class="badge badge-sm badge-warning">Line 2</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">{{ $invoice->invoice_date }}</span>
                                                    </td>

                                                    <td>
                                                        <span class="text-bold-600 d-block text-dark">{{ $invoice->pharmacist->name ?? 'غير محدد' }}</span>
                                                        <small class="text-muted">
                                                            <i class="ft-map-pin"></i> {{ $invoice->pharmacist->center->name ?? '-' }}
                                                        </small>
                                                        @foreach($invoice->doctors as $doctor)
                                                            <div class="mt-1 font-small-2 text-info">
                                                                <i class="la la-user-md"></i> د. {{ $doctor->name }}
                                                            </div>
                                                        @endforeach
                                                    </td>

                                                    <td>
                                                        <div class="font-small-3">
                                                            <span class="text-muted">بيع:</span> <span class="text-dark">{{ $invoice->representative->name ?? '-' }}</span>
                                                        </div>
                                                        <div class="font-small-3">
                                                            <span class="text-muted">دعاية:</span> <span class="text-dark">{{ $invoice->medicalRepresentative->name ?? '-' }}</span>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="mb-1 font-weight-bold text-primary">
                                                            {{ number_format($invoice->final_total) }}
                                                        </div>
                                                        @if($invoice->remaining_amount > 0)
                                                            <span class="badge badge-sm badge-danger" style="background-color: #FF5B5C;">متبقي: {{ number_format($invoice->remaining_amount) }}</span>
                                                        @else
                                                            <span class="badge badge-sm badge-success">خالص</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if($invoice->status == 1)
                                                            <span class="badge badge-success badge-glow">مدفوع</span>
                                                        @elseif($invoice->status == 2)
                                                            <span class="badge badge-warning">آجل</span>
                                                        @elseif($invoice->status == 3)
                                                            <span class="badge badge-info">جزئي</span>
                                                        @endif
                                                    </td>

                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="la la-cog"></i> خيارات
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item" href="{{ route('admin.invoices.show', $invoice->id) }}">
                                                                    <i class="mr-1 ft-eye text-info"></i> تفاصيل الفاتورة
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item" href="{{ route('admin.invoices.pdf', $invoice->id) }}" target="_blank">
                                                                    <i class="mr-1 la la-file-pdf-o text-secondary"></i> طباعة PDF
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item" href="{{ route('admin.invoices.edit', $invoice->id) }}">
                                                                    <i class="mr-1 ft-edit text-warning"></i> تعديل
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item font-weight-bold" href="{{ route('admin.invoices.payments', $invoice->id) }}">
                                                                    <i class="mr-1 fas fa-dollar-sign"></i> سداد مبلغ
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <form action="{{ route('admin.invoices.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('حذف الفاتورة سيؤدي لإرجاع الكميات للمخزن وعكس تارجت الدكتور. هل أنت متأكد؟');" style="display:inline;">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="mr-1 ft-trash"></i> حذف
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="py-3 text-center text-muted">
                                                        <i class="ft-alert-circle font-medium-1"></i> لا توجد فواتير مسجلة تطابق البحث.
                                                        <br>
                                                        <a href="{{ route('admin.invoices.create') }}" class="mt-1 btn btn-sm btn-primary">إضافة فاتورة جديدة</a>
                                                    </td>
                                                </tr>
                                            @endforelse

                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-2 justify-content-center d-flex no-print">
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
