@extends('layouts.admin')

@section('title', 'إدارة المخازن')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-building"></i> إدارة المخازن والمستودعات </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item active">المخازن</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                @include('admin.includes.alerts.success')
                @include('admin.includes.alerts.errors')

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">المخازن</h4>
                        <div class="heading-elements">
                            <a href="{{ route('admin.warehouses.create') }}" class="btn btn-sm btn-primary box-shadow-2" title="إضافة مخزن جديد">
                                <i class="ft-plus"></i> إضافة مخزن جديد
                            </a>
                        </div>
                    </div>

                    <div class="card-content collapse show">
                        <div class="card-body card-dashboard">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0 text-center">
                                    <thead class="bg-primary white">
                                    <tr>
                                        <th>#</th>
                                        <th>اسم المخزن</th>
                                        <th>النوع</th>
                                        <th>تابع لـ (الرئيسي)</th>
                                        <th>عدد الأصناف (R)</th>
                                        <th>مناطق التوزيع</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($warehouses as $index => $warehouse)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="text-bold-600">{{ $warehouse->name }}</td>
                                            <td>
                                                @if($warehouse->type == 'main')
                                                    <span class="badge badge-success">رئيسي</span>
                                                @else
                                                    <span class="badge badge-info">فرعي</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($warehouse->parent)
                                                    <span class="text-muted">{{ $warehouse->parent->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-pill badge-secondary">{{ $warehouse->drugs_count }} صنف</span>
                                            </td>
                                            <td>
                                                @if($warehouse->zones->count() > 0)
                                                    <span class="badge badge-warning">{{ $warehouse->zones->count() }} مناطق</span>
                                                @else
                                                    <span class="text-muted font-small-3">غير مربوط</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.warehouses.show', $warehouse->id) }}"
                                                       class="btn btn-sm btn-outline-info box-shadow-2 mr-1"
                                                       title="جرد المخزن">
                                                        <i class="ft-box"></i> جرد
                                                    </a>

                                                    <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-sm btn-warning mr-1" title="تعديل">
                                                        <i class="la la-edit"></i>
                                                    </a>

                                                    {{-- نموذج حذف المخزن --}}
                                                    <form action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المخزن؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                            <i class="la la-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-3 text-muted">لا توجد مخازن مضافة حالياً.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 d-flex justify-content-center">{{ $warehouses->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
