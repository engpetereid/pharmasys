@extends('layouts.admin')

@section('title', 'قائمة الصيدليات')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-hospital-o"></i> إدارة الصيدليات </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">الرئيسية</a></li>
                                <li class="breadcrumb-item active">الصيدليات</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="{{ route('admin.pharmacists.create') }}" class="btn btn-primary btn-min-width box-shadow-2">
                            <i class="ft-plus"></i> إضافة صيدلية جديدة
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <div class="card mb-2">
                    <div class="card-body">
                        <form action="{{ route('admin.pharmacists.index') }}" method="GET">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-0 position-relative has-icon-left">
                                        <input type="text" class="form-control" name="search"
                                               placeholder="ابحث باسم الصيدلية أو رقم الهاتف..."
                                               value="{{ request('search') }}">
                                        <div class="form-control-position">
                                            <i class="ft-search primary"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-info btn-block box-shadow-1">بحث</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.pharmacists.index') }}" class="btn btn-secondary btn-outline-secondary btn-block">إلغاء</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <section id="dom">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">جميع الصيدليات المسجلة</h4>
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
                                                    <th>اسم الصيدلية</th>
                                                    <th>المركز</th>
                                                    <th>العنوان</th>
                                                    <th>رقم الهاتف</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                @forelse($pharmacists as $index => $pharmacist)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td class="text-bold-600 text-primary">{{ $pharmacist->name }}</td>
                                                        <td>
                                                            <span class="badge badge-pill badge-secondary">
                                                                {{ $pharmacist->center->name ?? 'غير محدد' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ Str::limit($pharmacist->address, 30) }}</td>
                                                        <td>{{ $pharmacist->phone }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">

                                                                <a href="{{ route('admin.pharmacists.show', $pharmacist->id) }}"
                                                                   class="btn btn-sm btn-outline-info box-shadow-2 mr-1" title="كشف حساب الصيدلية">
                                                                    <i class="ft-eye"></i> عرض
                                                                </a>

                                                                <a href="{{ route('admin.pharmacists.edit', $pharmacist->id) }}"
                                                                   class="btn btn-sm btn-outline-primary box-shadow-2 mr-1" title="تعديل البيانات">
                                                                    <i class="ft-edit"></i> تعديل
                                                                </a>

                                                                <form action="{{ route('admin.pharmacists.destroy', $pharmacist->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger box-shadow-2"
                                                                    onclick="return confirm('هل أنت متأكد من حذف هذه الصيدلية؟ سيتم حذف جميع الفواتير المرتبطة بها!');" title="حذف">
                                                                    <i class="ft-trash"></i> حذف
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-3">
                                                            @if(request('search'))
                                                                لا توجد نتائج تطابق بحثك "{{ request('search') }}"
                                                            @else
                                                                لا توجد صيدليات مسجلة حالياً
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforelse

                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="justify-content-center d-flex mt-2">
                                            {{ $pharmacists->links() }}
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
