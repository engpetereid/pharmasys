@extends('layouts.admin')

@section('title', 'جرد المخزن: ' . $warehouse->name)

@section('content')

<style>
    @media print {
        .no-print, .main-menu, .header-navbar, .footer, .customizer,
        .breadcrumb-wrapper, .btn, .content-header-right,
        .heading-elements, .pagination {
            display: none !important;
        }

        body, .app-content, .content-wrapper {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 20px !important;
        }
        .card-header {
            padding-left: 50px !important;
            padding-right: 50px !important;
            border-bottom: 2px solid #000 !important;
        }

        .col-xl-4, .col-xl-8, .col-md-6, .col-12 {
            flex: 0 0 99% !important;
            max-width: 99% !important;
            padding: 0 !important;
        }


        .table-responsive {
            overflow: visible !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 12pt;
        }
        th, td {
            border: 1px solid #333 !important;
            padding: 8px !important;
            color: #000 !important;
        }
        thead {
            background-color: #eee !important;
            -webkit-print-color-adjust: exact;
        }

        tr {
            page-break-inside: avoid;
        }

        .badge {
            border: 1px solid #000;
            background: transparent !important;
            color: #000 !important;
            padding: 2px 5px;
        }


         .con-pr {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                width: 100% !important;
                margin-bottom: 10px;
            }

            .print-header {
                display: block !important;
                text-align: center;
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

    .con-pr{
        display: none;
    }
</style>

<div class="app-content content">
    <div class="content-wrapper">
               <div class="con-pr">
                <div class="print-header">
                        <h3>تقرير جرد مخزن</h3>
                        <h2>{{ $warehouse->name }}</h2>
                        <p>تاريخ الطباعة: {{ date('Y-m-d H:i') }}</p>
                    </div>
                    <div class="print-logo">
                        <img src="{{ asset('assets/admin/images/slogan.jpg') }}" alt="Logo">
                    </div>
                </div>


        <div class="mb-2 content-header row no-print">
            <div class="content-header-left col-md-6 col-12">
                <h3 class="content-header-title "> <i class="la la-box"></i> تفاصيل وجرد المخزن </h3>
                <div class="row breadcrumbs-top">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">المخازن</a></li>
                            <li class="breadcrumb-item active">{{ $warehouse->name }}</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="text-right content-header-right col-md-6 col-12">
                <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-warning box-shadow-2">
                    <i class="ft-edit"></i> تعديل البيانات
                </a>
            </div>
        </div>

        <div class="content-body">

            <div class="row">
                <div class="col-xl-4 col-lg-12">
                    <div class="card border-top-info border-top-3">
                        <div class="card-header">
                            <h4 class="card-title">بيانات المخزن</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-muted">الاسم</span>
                                        <span class="text-bold-600">{{ $warehouse->name }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-muted">النوع</span>
                                        @if($warehouse->type == 'main')
                                            <span class="badge badge-success">رئيسي</span>
                                        @else
                                            <span class="badge badge-info">فرعي</span>
                                        @endif
                                    </li>
                                    @if($warehouse->parent)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-muted">تابع لـ</span>
                                            <span class="text-primary">{{ $warehouse->parent->name }}</span>
                                        </li>
                                    @endif
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-muted">عدد الأصناف</span>
                                        <span class="badge badge-pill badge-secondary">{{ $inventory->total() }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"> <i class="ft-list"></i> أرصدة الأدوية الحالية </h4>
                            <div class="heading-elements">

                                <a href="{{ route('admin.warehouses.stock.add', $warehouse->id) }}" class="mr-1 btn btn-sm btn-success box-shadow-2">
                                    <i class="ft-plus"></i> إضافة رصيد
                                </a>

                                <a href="{{ route('admin.warehouses.stock.return', $warehouse->id) }}" class="mr-1 btn btn-sm btn-danger box-shadow-2">
                                    <i class="ft-minus-circle"></i> تسجيل مرتجع
                                </a>

                                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="ft-printer"></i> طباعة الجرد</button>
                            </div>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped table-hover">
                                        <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>اسم الدواء</th>
                                            <th>الخط</th>
                                            <th>سعر الوحدة</th>
                                            <th>الكمية</th>
                                            <th>قيمة المخزون</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($inventory as $index => $drug)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-bold-600">{{ $drug->name }}</td>
                                                <td>
                                                    @if($drug->line == 1)
                                                        <span class="badge badge-sm badge-info">Line 1</span>
                                                    @else
                                                        <span class="badge badge-sm badge-warning">Line 2</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($drug->price, 2) }}</td>
                                                <td>
                                                    @if($drug->pivot->quantity <= 10)
                                                        <span class="badge badge-danger">{{ $drug->pivot->quantity }}</span>
                                                    @else
                                                        <span class="font-weight-bold text-success">{{ $drug->pivot->quantity }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-primary font-weight-bold">
                                                    {{ number_format($drug->price * $drug->pivot->quantity, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-3 text-center text-muted">المخزن فارغ حالياً.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2 d-flex justify-content-center">
                                    {{ $inventory->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="mt-4 card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">مناطق التوزيع التابعة للمخزن</h4>
        </div>

        <div class="card-body">
            @if($warehouse->distributionAreas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المنطقة</th>
                            <th>الخط</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($warehouse->distributionAreas as $index => $area)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $area->name }}</td>
                                <td class="mb-0 text-bold-700"> <span class="line-badge">Line {{ $area->line }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center alert alert-info">
                    لا توجد مناطق توزيع مرتبطة بهذا المخزن حالياً.
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
