@extends('layouts.admin')

@section('title', 'إدارة المناطق')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="ft-map"></i> إدارة المناطق والخطوط </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item active">المناطق</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="{{ route('admin.zones.create') }}" class="btn btn-primary btn-min-width box-shadow-2">
                            <i class="ft-plus"></i> إضافة منطقة جديدة
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">
                @include('admin.includes.alerts.success')
                @include('admin.includes.alerts.errors')

                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-primary white">
                                    <tr>
                                        <th>المحافظة</th>
                                        <th>المنطقة</th>
                                        <th>الخط (Line)</th>
                                        <th>المخزن المسؤول</th>
                                        <th>المناديب (بيع / دعاية)</th>
                                        <th>المراكز</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($zones as $zone)
                                        <tr>
                                            <td>{{ $zone->province->name ?? 'غير محدد' }}</td>
                                            <td class="text-bold-600">{{ $zone->name }}</td>
                                            <td>
                                                @if($zone->line == 1)
                                                    <span class="badge badge-info">Line 1</span>
                                                @else
                                                    <span class="badge badge-warning">Line 2</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($zone->warehouse)
                                                    <span class="text-success font-small-3"><i class="la la-cube"></i> {{ $zone->warehouse->name }}</span>
                                                @else
                                                    <span class="text-danger font-small-3"><i class="la la-warning"></i> غير محدد</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="d-block">بيع: {{ $zone->salesRepresentative->name ?? '-' }}</small>
                                                <small class="d-block text-muted">دعاية: {{ $zone->medicalRepresentative->name ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-pill badge-secondary">
                                                    {{ $zone->centers->count() }} مركز
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.zones.show', $zone->id) }}" class="btn btn-sm btn-outline-info mr-1" title="التفاصيل"><i class="ft-eye"></i></a>
                                                    <a href="{{ route('admin.zones.edit', $zone->id) }}" class="btn btn-sm btn-outline-primary mr-1" title="تعديل"><i class="ft-edit"></i></a>
                                                    <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST" style="display:inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذه المنطقة؟')" title="حذف"><i class="ft-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center py-3 text-muted">لا توجد مناطق مضافة حالياً</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 d-flex justify-content-center">{{ $zones->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
