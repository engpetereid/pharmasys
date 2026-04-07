@extends('layouts.admin')

@section('title', request()->routeIs('admin.deals.archived') ? 'أرشيف الاتفاقات' : 'اتفاقات وتارجت الأطباء')

@section('style')
    <style>
        .deal-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid #e0e0e0;
            position: relative;
            background: #fff;
            overflow: hidden;
            height: 95%;
        }
        .deal-card .card-content { height: 100%; }
        .deal-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); z-index: 2; }
        .progress { height: 10px; border-radius: 5px; background-color: #f0f2f5; }
        .deal-card.is-stopped { background-color: #fafafa; border-top: 3px solid #6c757d !important; }
        .deal-card.is-stopped .card-body { opacity: 0.8; }
        .deal-card.is-stopped::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background-image: linear-gradient(45deg,rgba(0,0,0,.03) 25%,transparent 25%,transparent 50%,rgba(0,0,0,.03) 50%,rgba(0,0,0,.03) 75%,transparent 75%,transparent);
            background-size: 10px 10px; pointer-events: none; z-index: 0;
        }
        .badge-pill { padding: 0.4em 0.8em; }
        .btn-group-sm > .btn, .btn-sm { font-size: 0.85rem; }
        .print-header , .print-logo { display: none; }
        .actions-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px dashed #eee; margin-top: 15px; }
        .btn-icon-soft { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; border: none; transition: all 0.2s ease; background-color: #f5f6f9; color: #6c757d; cursor: pointer; text-decoration: none !important; }
        .btn-icon-soft:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-soft-primary { background-color: rgba(0, 123, 255, 0.1); color: #007bff; }
        .btn-soft-primary:hover { background-color: #007bff; color: #fff; }
        .btn-soft-info { background-color: rgba(23, 162, 184, 0.1); color: #17a2b8; }
        .btn-soft-info:hover { background-color: #17a2b8; color: #fff; }
        .btn-soft-success { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
        .btn-soft-success:hover { background-color: #28a745; color: #fff; }
        .btn-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .btn-soft-warning:hover { background-color: #ffc107; color: #fff; }
        .btn-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .btn-soft-danger:hover { background-color: #dc3545; color: #fff; }
        .btn-soft-dark { background-color: rgba(52, 58, 64, 0.1); color: #343a40; }
        .btn-soft-dark:hover { background-color: #343a40; color: #fff; }

        .stat-card { border-radius: 5px; overflow: hidden; transition: all 0.3s; background: #fff; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); }

        @media print {
            @page { margin: 0.5cm; size: auto; }
            body { background-color: #fff; -webkit-print-color-adjust: exact; width: 100% !important; }

            .no-print, .main-menu, .header-navbar, .footer, .customizer, .form-actions, .filter-card { display: none !important; }

            .content-wrapper, .app-content, .content-body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .content-body > .row {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                width: 100% !important;
            }

            .col-xl-4, .col-md-6 {
                max-width: 41% !important;
                margin-left: 75px !important;
            }
             .card {
            border: 1px solid #999 !important;
            break-inside: avoid;
            margin-bottom: 0 !important;
            box-shadow: none !important;
        }
            .deal-card.is-stopped { background-color: #eee !important; }

            /* تنسيقات الإحصائيات للطباعة */
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
                margin-left: 0 !important; /* إعادة ضبط للملخص */
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

            <div class="print-header-wrapper">
                <div class="print-header">
                    <h2>{{ request()->routeIs('admin.deals.archived') ? 'أرشيف الاتفاقات' : 'تقرير اتفاقات وتارجت الأطباء' }}</h2>
                    <p>تاريخ الطباعة: {{ date('Y-m-d H:i') }} | عدد الاتفاقات: {{ $deals->total() }}</p>
                </div>

                <div class="print-logo">
                    <img src="{{ asset('assets/admin/images/slogan.jpg') }}" alt="Logo">
                </div>
            </div>


            <div class="mb-2 content-header row no-print">
                <div class="col-md-6 col-12">
                    @if(request()->routeIs('admin.deals.archived'))
                        <h3 class="content-header-title text-muted"> <i class="la la-archive"></i> أرشيف الاتفاقات (Settle/Old) </h3>
                    @else
                        <h3 class="content-header-title"> <i class="la la-handshake-o"></i> اتفاقات الأطباء (Running Targets) </h3>
                    @endif
                </div>
                <div class="text-right col-md-6 col-12">
                    @if(request()->routeIs('admin.deals.archived'))
                        <a href="{{ route('admin.deals.index') }}" class="btn btn-primary box-shadow-2">
                            <i class="la la-arrow-left"></i> العودة للاتفاقات الجارية
                        </a>
                    @else
                        <a href="{{ route('admin.deals.archived') }}" class="btn btn-secondary">
                            <i class="la la-archive"></i> الأرشيف
                        </a>
                        <a href="{{ route('admin.deals.create') }}" class="btn btn-primary box-shadow-2">
                            <i class="ft-plus"></i> اتفاق جديد
                        </a>
                    @endif
                </div>
            </div>
             <div class="content-body">
                @include('admin.includes.alerts.success')
                @include('admin.includes.alerts.errors')

                {{-- إحصائيات ملخص الاتفاقات المتجاوبة مع الفلتر --}}
                <div class="row card_con mb-2">
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card stat-card border-top-info border-top-3 box-shadow-1 mb-0">
                            <div class="card-body p-2">
                                <h6 class="text-muted mb-1">إجمالي التارجت المطلوب</h6>
                                <h3 class="text-info font-weight-bold mb-0">{{ number_format($stats['total_target']) }} ج.م</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card stat-card border-top-primary border-top-3 box-shadow-1 mb-0">
                            <div class="card-body p-2">
                                <h6 class="text-muted mb-1">إجمالي المبيعات المحققة</h6>
                                <h3 class="text-primary font-weight-bold mb-0">{{ number_format($stats['total_achieved']) }} ج.م</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card stat-card border-top-success border-top-3 box-shadow-1 mb-0">
                            <div class="card-body p-2">
                                <h6 class="text-muted mb-1">إجمالي المدفوع للأطباء</h6>
                                <h3 class="text-success font-weight-bold mb-0">{{ number_format($stats['total_paid']) }} ج.م</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card stat-card border-top-danger border-top-3 box-shadow-1 mb-0">
                            <div class="card-body p-2">
                                <h6 class="text-muted mb-1">إجمالي العمولات المتبقية</h6>
                                <h3 class="text-danger font-weight-bold mb-0">{{ number_format($stats['total_remaining']) }} ج.م</h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الفلتر --}}
                <div class="card no-print">
                    <div class="card-body">
                        <form action="{{ request()->url() }}" method="GET">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="mb-0 form-group position-relative has-icon-left">
                                        <label class="font-small-3 text-muted">بحث</label>
                                        <input type="text" name="search" class="form-control" placeholder="اسم الطبيب أو الصيدلية..." value="{{ request('search') }}">
                                        <div class="form-control-position" style="top: 28px"><i class="ft-search"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-0 form-group">
                                        <label class="font-small-3 text-muted">المنطقة</label>
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
                                <div class="col-md-2">
                                    <div class="mb-0 form-group">
                                        <label class="font-small-3 text-muted">حالة الدفع</label>
                                        <select name="status" class="form-control">
                                            <option value="">-- الكل --</option>
                                            <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>خالص (Paid)</option>
                                            <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>آجل (Unpaid)</option>
                                            <option value="3" {{ request('status') == 3 ? 'selected' : '' }}>جزئي (Partial)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="btn-group w-100">
                                        <button type="submit" class="btn btn-info">
                                            <i class="ft-filter"></i> تصفية
                                        </button>
                                        <button type="submit" name="export" value="excel" class="btn btn-success">
                                            <i class="la la-file-excel-o"></i> إكسيل
                                        </button>
                                        <button type="button" onclick="window.print()" class="btn btn-secondary">
                                            <i class="ft-printer"></i> طباعة
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    @forelse($deals as $deal)
                        <div class="col-xl-4 col-md-6 col-12 d-flex align-items-stretch"> @php
                                $isActive = $deal->is_active;
                                $isOpenDeal = ($deal->target_amount <= 0);

                                $borderClass = 'info';
                                $statusLabel = 'ساري';
                                $badgeClass = 'badge-info';
                                $cardClass = '';

                                if ($deal->status == 1) {
                                    $borderClass = 'success';
                                    $statusLabel = 'خالص';
                                    $badgeClass = 'badge-success';
                                } elseif ($deal->status == 3) {
                                    $borderClass = 'warning';
                                    $statusLabel = 'مدفوع جزئياً';
                                    $badgeClass = 'badge-warning text-white';
                                }

                                if (!$isActive) {
                                    $borderClass = 'secondary';
                                    $statusLabel = 'موقوف مؤقتاً';
                                    $badgeClass = 'badge-secondary';
                                    $cardClass = 'is-stopped';
                                }


                                $currentCommission = $isOpenDeal
                                    ? ($deal->achieved_amount * ($deal->commission_percentage / 100))
                                    : $deal->commission_amount;
                                $remaining = $deal->commission_amount - $deal->paid_amount;
                                $percent = 0;
                                if (!$isOpenDeal && $deal->target_amount > 0) {
                                    $percent = ($deal->achieved_amount / $deal->target_amount) * 100;
                                }
                                $percentDisplay = min($percent, 100);
                                $progressColor = $percent >= 100 ? 'success' : ($isActive ? 'warning' : 'secondary');
                            @endphp

                            <div class="card deal-card border-top-{{ $borderClass }} border-top-3 {{ $cardClass }}">
                                <div class="card-content">
                                    <div class="card-body d-flex flex-column">

                                        <div class="media">
                                            <div class="text-left media-body">
                                                <h4 class="mb-0 text-bold-600">
                                                    <a href="{{ route('admin.doctors.show', $deal->doctor_id) }}" class="text-dark">{{ $deal->doctor->name }}</a>
                                                </h4>
                                                <div class="mt-1">
                                                    <span class="border badge badge-pill badge-light" title="{{ $deal->pharmacists->pluck('name')->implode(', ') }}">
                                                        <i class="la la-hospital-o"></i> {{ $deal->pharmacists->count() }} صيدليات
                                                    </span>
                                                    <span class="badge badge-pill {{ $badgeClass }}">{{ $statusLabel }}</span>
                                                    @if($isOpenDeal)
                                                        <span class="badge badge-pill badge-primary">عمولة مفتوحة</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-center media-right align-self-center">
                                                <h2 class="text-bold-700 {{ !$isActive ? 'text-muted' : 'text-primary' }} mb-0">{{ floatval($deal->commission_percentage) }}%</h2>
                                                <small class="text-muted">نسبة</small>
                                            </div>
                                        </div>

                                        <div class="mt-2" style="position: relative; z-index: 1;">

                                            @if($isOpenDeal)
                                                <div class="d-flex flex-column justify-content-center" style="min-height: 85px;">
                                                    <div class="p-1 mb-0 text-center alert alert-light border-primary" role="alert">
                                                        <div class="mb-0 font-small-3 text-muted">المبيعات المحققة</div>
                                                        <h3 class="mt-0 mb-0 text-bold-700 text-primary">
                                                            {{ number_format($deal->achieved_amount) }}
                                                        </h3>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="d-flex flex-column justify-content-center" style="min-height: 85px;">
                                                    <div class="mb-1 d-flex justify-content-between font-small-3">
                                                        <span class="text-muted">المحقق: <strong class="text-dark">{{ number_format($deal->achieved_amount) }}</strong></span>
                                                         <span class="text-muted">الهدف: <strong>{{ number_format($deal->target_amount) }}</strong></span>
                                                    </div>

                                                    <div class="mb-0 progress box-shadow-1">
                                                        <div class="progress-bar bg-gradient-x-{{ $progressColor }}"
                                                             role="progressbar"
                                                             style="width: {{ $percentDisplay }}%"
                                                             aria-valuenow="{{ $percentDisplay }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <p class="mt-1 mb-0 text-center font-small-2 text-muted">
                                                        {{ number_format($percent, 1) }}% مكتمل
                                                        @if(!$isActive) <span class="text-danger">(الحساب متوقف)</span> @endif
                                                    </p>
                                                </div>
                                            @endif

                                            <hr class="my-1">

                                            <div class="mt-1 text-center row" style="position: relative; z-index: 1;">

                                                @if($isOpenDeal)
                                                    <div class="px-1 col-6 border-right">
                                                        <h6 class="mb-0 text-muted font-small-2">العمولة المستحقة</h6>
                                                        <span class="text-bold-700 text-primary font-medium-1">
                                                            {{ number_format($currentCommission) }}
                                                        </span>
                                                        <div class="font-small-1 text-muted">(حسب المحقق)</div>
                                                    </div>
                                                    <div class="px-1 col-6">
                                                        <h6 class="mb-0 text-muted font-small-2">المدفوع</h6>
                                                        <span class="text-bold-700 text-success font-medium-1">
                                                            {{ number_format($deal->paid_amount) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="px-1 col-4 border-right">
                                                        <h6 class="mb-0 text-muted font-small-2">العمولة</h6>
                                                        <span class="text-bold-700 text-primary font-small-3">{{ number_format($deal->commission_amount) }}</span>
                                                    </div>
                                                    <div class="px-1 col-4 border-right">
                                                        <h6 class="mb-0 text-muted font-small-2">المدفوع</h6>
                                                        <span class="text-bold-700 text-success font-small-3">{{ number_format($deal->paid_amount) }}</span>
                                                    </div>
                                                    <div class="px-1 col-4">
                                                        <h6 class="mb-0 text-muted font-small-2">المتبقي</h6>
                                                        <span class="text-bold-700 text-danger font-small-3">{{ number_format($remaining) }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="pt-2 mt-auto row no-print" style="position: relative; z-index: 1;">
                                                <div class="mb-1 col-12">
                                                    <a href="{{ route('admin.deals.invoices', $deal->id) }}" class="btn btn-sm btn-outline-info btn-block">
                                                        <i class="la la-list"></i> عرض الفواتير المحققة
                                                    </a>
                                                </div>

                                                <div class="mb-1 col-12">
                                                    @if($deal->status != 1)
                                                        @php
                                                            $canSettle = !$isOpenDeal && ($deal->achieved_amount >= $deal->target_amount);

                                                            $hasBalance = $currentCommission > $deal->paid_amount;
                                                        @endphp

                                                        @if($isOpenDeal)

                                                            <div class="mt-1 text-center font-small-2 text-primary">
                                                                <i class="la la-infinity"></i> حساب جاري (مفتوح)
                                                            </div>

                                                        @elseif($canSettle && $hasBalance)

                                                            <form action="{{ route('admin.deals.pay', $deal->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success btn-block box-shadow-2"
                                                                        onclick="return confirm('تأكيد تسوية المبلغ المتبقي؟')">
                                                                    <i class="ft-check-circle"></i> تسوية المتبقي
                                                                </button>
                                                            </form>

                                                        @elseif(!$hasBalance && $canSettle)
                                                            <div class="mt-1 text-center font-small-2 text-success">
                                                                <i class="ft-check"></i> مدفوع بالكامل
                                                            </div>

                                                        @else
                                                            <div class="mt-1 text-center font-small-2 text-muted">
                                                                <i class="ft-clock"></i> التارجت ساري
                                                            </div>
                                                        @endif

                                                    @else
                                                        <div class="btn btn-sm btn-light btn-block disabled text-bold" style="opacity: 1">
                                                            <i class="ft-check"></i> تمت التسوية
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="mt-1 col-12">
                                                    <div class="actions-footer">
                                                        <div class="d-flex">
                                                             @if(!$deal->is_archived)
                                                                <form action="{{ route('admin.deals.toggleActive', $deal->id) }}" method="POST" class="mr-1">
                                                                    @csrf
                                                                    <button type="submit" class="btn-icon-soft {{ $deal->is_active ? 'btn-soft-warning' : 'btn-soft-success' }}"
                                                                            data-toggle="tooltip" title="{{ $deal->is_active ? 'إيقاف مؤقت' : 'تنشيط العمل' }}">
                                                                        <i class="ft-{{ $deal->is_active ? 'pause' : 'play' }}"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <form action="{{ route('admin.deals.toggleArchive', $deal->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn-icon-soft {{ $deal->is_archived ? 'btn-soft-info' : 'btn-soft-dark' }}"
                                                                        data-toggle="tooltip" title="{{ $deal->is_archived ? 'استعادة' : 'أرشفة' }}">
                                                                    <i class="la {{ $deal->is_archived ? 'la-undo' : 'la-archive' }} font-medium-3"></i>
                                                                </button>
                                                            </form>
                                                        </div>

                                                        <div class="d-flex">
                                                            <a href="{{ route('admin.deals.edit', $deal->id) }}" class="mr-1 btn-icon-soft btn-soft-primary" data-toggle="tooltip" title="تعديل">
                                                                <i class="ft-edit"></i>
                                                            </a>
                                                            <form action="{{ route('admin.deals.destroy', $deal->id) }}" method="POST">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn-icon-soft btn-soft-danger" onclick="return confirm('حذف نهائي؟')" data-toggle="tooltip" title="حذف">
                                                                    <i class="ft-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="p-2 text-center alert alert-light box-shadow-1 border-primary">
                                <h4 class="text-muted"><i class="mr-1 ft-info"></i> لا توجد بيانات للعرض</h4>
                                @if(request()->routeIs('admin.deals.archived'))
                                    <a href="{{ route('admin.deals.index') }}" class="btn btn-primary btn-sm">العودة للاتفاقات الجارية</a>
                                @else
                                    <a href="{{ route('admin.deals.create') }}" class="btn btn-primary btn-sm">إضافة اتفاق جديد</a>
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="row no-print">
                    <div class="col-12 d-flex justify-content-center">
                        {{ $deals->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
