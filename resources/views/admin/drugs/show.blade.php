@extends('layouts.admin')

@section('title', 'تقرير صنف: ' . $drug->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-flask"></i> تقرير صنف: {{ $drug->name }} </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.drugs.index') }}">الأدوية</a></li>
                                <li class="breadcrumb-item active">{{ $drug->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12 text-right">
                    <a href="{{ route('admin.drugs.edit', $drug->id) }}" class="btn btn-warning box-shadow-2">
                        <i class="ft-edit"></i> تعديل السعر/الاسم
                    </a>
                </div>
            </div>

            <div class="content-body">

                {{-- فلتر البحث --}}
                <div class="card filter-card box-shadow-1 border-top-info border-top-3 mb-2 no-print">
                    <div class="card-body py-2">
                        <form action="{{ route('admin.drugs.show', $drug->id) }}" method="GET" id="searchForm">
                            <div class="row align-items-end">
                                <div class="col-md-3 mb-1">
                                    <label class="text-muted font-small-3">من تاريخ</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-3 mb-1">
                                    <label class="text-muted font-small-3">إلى تاريخ</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-4 mb-1">
                                    <label class="text-muted font-small-3">المنطقة (Zone)</label>
                                    <select name="zone_id" class="form-control select2">
                                        <option value="">-- كل المناطق --</option>
                                        @foreach($zones as $zone)
                                            <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                                {{ $zone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-1 d-flex">
                                    <button type="submit" class="btn btn-primary flex-grow-1 mr-1" title="تصفية"><i class="ft-filter"></i></button>
                                    <a href="{{ route('admin.drugs.show', $drug->id) }}" class="btn btn-outline-secondary" title="إلغاء الفلتر"><i class="ft-x"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card border-top-primary border-top-3 text-center">
                            <div class="card-body">
                                <h6 class="text-muted">سعر الوحدة الحالي</h6>
                                <h3 class="text-primary">{{ number_format($drug->price, 2) }}</h3>
                                <span class="badge badge-{{ $drug->line == 1 ? 'info' : 'warning' }}">Line {{ $drug->line }}</span>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card bg-gradient-x-success text-white">
                            <div class="card-body text-center">
                                <i class="la la-cubes font-large-2 float-right opacity-50"></i>
                                <h6 class="text-white">إجمالي الكمية المباعة</h6>
                                <h3 class="text-white">{{ number_format($totalQuantitySold) }} <small>علبة</small></h3>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card bg-gradient-x-info text-white">
                            <div class="card-body text-center">
                                <i class="la la-money font-large-2 float-right opacity-50"></i>
                                <h6 class="text-white">إجمالي الإيرادات</h6>
                                <h3 class="text-white">{{ number_format($totalRevenue, 2) }} <small>ج.م</small></h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card border-top-warning border-top-3 text-center">
                            <div class="card-body">
                                <h6 class="text-muted">عدد عمليات البيع</h6>
                                <h3 class="text-warning">{{ $invoicesCount }} <small>فاتورة</small></h3>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">سجل حركة المبيعات </h4>
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
                                                <th>الكمية</th>
                                                <th>سعر البيع</th>
                                                <th>الإجمالي</th>
                                                <th>عرض</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($salesHistory as $detail)
                                                <tr>
                                                    <td>#{{ $detail->invoice->id }}</td>
                                                    <td>{{ $detail->invoice->invoice_date }}</td>
                                                    <td>{{ $detail->invoice->pharmacist->name ?? '-' }}</td>
                                                    <td>{{ $detail->invoice->representative->name ?? '-' }}</td>
                                                    <td class="font-weight-bold">{{ $detail->quantity }}</td>
                                                    <td>{{ number_format($detail->unit_price, 2) }}</td>
                                                    <td class="text-success font-weight-bold">{{ number_format($detail->row_total, 2) }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.invoices.show', $detail->invoice_id) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                            <i class="ft-eye"></i> الفاتورة
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-3 text-muted">لم يتم بيع هذا الصنف حتى الآن.</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-center">
                                        {{ $salesHistory->links() }}
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
