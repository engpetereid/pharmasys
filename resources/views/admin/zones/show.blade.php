@extends('layouts.admin')

@section('title', 'تفاصيل المنطقة: ' . $zone->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">

            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-map-marker"></i> تفاصيل المنطقة </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.zones.index') }}">المناطق</a></li>
                                <li class="breadcrumb-item active">{{ $zone->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12 text-right">
                    <a href="{{ route('admin.zones.edit', $zone->id) }}" class="btn btn-warning box-shadow-2">
                        <i class="ft-edit"></i> تعديل البيانات
                    </a>
                </div>
            </div>

            <div class="content-body">

                <div class="row match-height">

                    <div class="col-xl-5 col-lg-12">
                        <div class="card border-top-info border-top-3">
                            <div class="card-header">
                                <h4 class="card-title">البيانات </h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-muted">اسم المنطقة</span>
                                            <span class="text-bold-600">{{ $zone->name }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-muted">المحافظة</span>
                                            <span class="text-bold-600">{{ $zone->province->name ?? 'غير محدد' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-muted">الخط (Line)</span>
                                            @if($zone->line == 1)
                                                <span class="badge badge-info">Line 1</span>
                                            @else
                                                <span class="badge badge-warning">Line 2</span>
                                            @endif
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                                            <span class="text-muted"><i class="la la-cubes"></i> المخزن المسؤول</span>
                                            @if($zone->warehouse)
                                                <a href="{{ route('admin.warehouses.show', $zone->warehouse_id) }}" class="btn btn_success text-bold-600">
                                                    {{ $zone->warehouse->name }}
                                                </a>
                                            @else
                                                <span class="text-danger">غير مربوط بمخزن!</span>
                                            @endif
                                        </li>
                                    </ul>

                                    <h6 class="my-2 text-muted font-small-3">فريق العمل المسؤول</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-muted">مندوب البيع (Sales)</span>
                                            <span class="text-primary font-weight-bold">{{ $zone->salesRepresentative->name ?? 'غير محدد' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-muted">مندوب الدعاية (Medical)</span>
                                            <span class="text-info font-weight-bold">{{ $zone->medicalRepresentative->name ?? 'غير محدد' }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"> <i class="la la-building"></i> المراكز المغطاة ({{ $zone->centers->count() }})</h4>
                                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>اسم المركز</th>
                                                <th class="text-center">عدد العملاء</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($zone->centers as $index => $center)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="text-bold-600">{{ $center->name }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-pill badge-light border">
                                                            {{ $center->pharmacists()->count() }} صيدلية
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.centers.show', $center->id) }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="ft-eye"></i> عرض المركز
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-2">لا توجد مراكز مربوطة بهذه المنطقة حالياً.</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card border-top-danger border-top-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title"> <i class="la la-money"></i> مصروفات ونثريات المنطقة</h4>
                                <div>
                                    <span class="badge badge-danger mr-1" style="font-size: 1rem;">
                                        الإجمالي: {{ number_format($zone->expenses->sum('amount'), 2) }}
                                    </span>
                                    <a href="{{ route('admin.zones.expenses.create', $zone->id) }}" class="btn btn-primary btn-sm">
                                        <i class="ft-plus"></i> إضافة مصروف جديد
                                    </a>
                                </div>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>التاريخ</th>
                                                <th>بيان المصروف (في إيه)</th>
                                                <th>المبلغ</th>
                                                <th>تم الإضافة</th>
                                                <th>إجراءات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($zone->expenses as $expense)
                                                <tr>
                                                    <td>{{ $expense->expense_date }}</td>
                                                    <td>{{ $expense->description }}</td>
                                                    <td class="font-weight-bold text-danger">{{ number_format($expense->amount, 2) }}</td>
                                                    <td><small class="text-muted">{{ $expense->created_at->diffForHumans() }}</small></td>
                                                    <td class="text-center">
                                                        <a href="{{ route('admin.zones.expenses.edit', $expense->id) }}" class="btn btn-sm btn-info" title="تعديل">
                                                            <i class="la la-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.zones.expenses.destroy', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصروف؟')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف"><i class="ft-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">لا توجد مصروفات مسجلة لهذه المنطقة.</td>
                                                </tr>
                                            @endforelse
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
    </div>
@endsection
