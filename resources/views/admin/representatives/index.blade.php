@extends('layouts.admin')

@section('title', 'قائمة المناديب')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-briefcase"></i> إدارة المناديب </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item active">المناديب</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="{{ route('admin.representatives.create') }}" class="btn btn-primary btn-min-width box-shadow-2">
                            <i class="ft-plus"></i> إضافة مندوب جديد
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <div class="card mb-2">
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form action="{{ route('admin.representatives.index') }}" method="GET">
                                <div class="row">
                                    <div class="col-md-8 mb-1">
                                        <fieldset class="form-group position-relative has-icon-left">
                                            <input type="text" name="search" class="form-control" id="iconLeft4"
                                                   placeholder="ابحث باسم المندوب أو رقم الهاتف..."
                                                   value="{{ request('search') }}">
                                            <div class="form-control-position">
                                                <i class="ft-search primary"></i>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-2 mb-1">
                                        <button type="submit" class="btn btn-info btn-block box-shadow-1">
                                            <i class="ft-search"></i> بحث
                                        </button>
                                    </div>
                                    <div class="col-md-2 mb-1">
                                        <a href="{{ route('admin.representatives.index') }}" class="btn btn-secondary btn-outline-secondary btn-block">
                                            <i class="ft-x"></i> إلغاء
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
                            <div class="card-header">
                                <h4 class="card-title">جميع المناديب المسجلين</h4>
                                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                        <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                        <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                    </ul>
                                </div>
                            </div>

                            @include('admin.includes.alerts.success')
                            @include('admin.includes.alerts.errors')

                            <div class="card-content collapse show">
                                <div class="card-body card-dashboard">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover w-100">
                                            <thead class="bg-primary white">
                                            <tr>
                                                <th>#</th>
                                                <th>اسم المندوب</th>
                                                <th>رقم الهاتف</th>
                                                <th>تاريخ التسجيل</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($representatives as $index => $representative)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="text-bold-600 text-primary">{{ $representative->name }}</td>
                                                    <td>{{ $representative->phone }}</td>
                                                    <td>{{ $representative->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('admin.representatives.show', $representative->id) }}"
                                                               class="btn btn-sm btn-outline-info box-shadow-2 mr-1" title="عرض الملف">
                                                                <i class="ft-user"></i> ملف
                                                            </a>

                                                            <a href="{{ route('admin.representatives.edit', $representative->id) }}"
                                                               class="btn btn-sm btn-outline-warning box-shadow-2 mr-1" title="تعديل">
                                                                <i class="ft-edit"></i> تعديل
                                                            </a>

                                                            <form action="{{ route('admin.representatives.destroy', $representative->id) }}" method="POST" style="display:inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger box-shadow-2"
                                                                        onclick="return confirm('هل أنت متأكد من حذف هذا المندوب؟')">
                                                                    <i class="ft-trash"></i> حذف
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">
                                                        @if(request('search'))
                                                            لا توجد نتائج تطابق بحثك "{{ request('search') }}"
                                                        @else
                                                            لا يوجد مناديب مسجلين حالياً
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-2 d-flex justify-content-center">
                                        {{ $representatives->withQueryString()->links() }}
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
