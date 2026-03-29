@extends('layouts.admin')

@section('title', 'قائمة المراكز')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-map-marker"></i> إدارة المراكز </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">الرئيسية</a></li>
                                <li class="breadcrumb-item active">المراكز</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="{{ route('admin.centers.create') }}" class="btn btn-primary btn-min-width box-shadow-2">
                            <i class="ft-plus"></i> إضافة مركز جديد
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section id="dom">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">جميع المراكز المسجلة</h4>
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
                                                    <th>اسم المركز</th>
                                                    <th>المحافظة</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                @forelse($centers as $index => $center)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td class="text-bold-600">{{ $center->name }}</td>
                                                        <td>
                                                            <span class="badge badge-pill badge-secondary">
                                                                {{ $center->province->name ?? 'غير محدد' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group" aria-label="Basic example">

                                                                <a href="{{ route('admin.centers.show', $center->id) }}"
                                                                   class="btn btn-sm btn-outline-info box-shadow-2 mr-1" title="عرض التفاصيل والصيدليات">
                                                                    <i class="ft-eye"></i> عرض
                                                                </a>

                                                                <a href="{{ route('admin.centers.edit', $center->id) }}"
                                                                   class="btn btn-sm btn-outline-primary box-shadow-2 mr-1" title="تعديل">
                                                                    <i class="ft-edit"></i> تعديل
                                                                </a>

                                                                <form action="{{ route('admin.centers.destroy', $center->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                            class="btn btn-sm btn-outline-danger box-shadow-2"
                                                                            onclick="return confirm('هل أنت متأكد من حذف هذا المركز؟ سيتم حذف جميع الصيدليات والأطباء المرتبطين به!');" title="حذف">
                                                                        <i class="ft-trash"></i> حذف
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3">لا توجد مراكز مسجلة حالياً</td>
                                                    </tr>
                                                @endforelse

                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="justify-content-center d-flex mt-2">
                                            {{ $centers->links() }}
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
