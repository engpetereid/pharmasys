@extends('layouts.admin')

@section('title', 'تقرير أداء المناديب')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="mb-2 content-header row">
                <div class="col-12">
                    <h3 class="content-header-title"> <i class="la la-users"></i> تقرير أداء المناديب </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item active">تقارير المناديب</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.reports.representatives.index') }}" method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-0 form-group">
                                        <label>فلتر حسب الخط (Line)</label>
                                        <select name="line" class="form-control" onchange="this.form.submit()">
                                            <option value="">-- كل الخطوط --</option>
                                            <option value="1" {{ request('line') == 1 ? 'selected' : '' }}>Line 1</option>
                                            <option value="2" {{ request('line') == 2 ? 'selected' : '' }}>Line 2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">ترتيب المناديب حسب المبيعات</h4>
                                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table mb-0 table-striped table-hover">
                                            <thead class="bg-warning white">
                                            <tr>
                                                <th>#</th>
                                                <th>اسم المندوب</th>
                                                <th>رقم الهاتف</th>
                                                <th>عدد الفواتير</th>
                                                <th>إجمالي المبيعات</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($representatives as $index => $rep)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="text-bold-600">{{ $rep->name }}</td>
                                                    <td>{{ $rep->phone }}</td>
                                                    <td>
                                                        <span class="badge badge-pill badge-dark">
                                                            {{ $rep->invoices_count }} فاتورة
                                                        </span>
                                                    </td>
                                                    <td class="text-warning font-weight-bold font-medium-1">
                                                        {{ number_format($rep->total_sales, 2) }} ج.م
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.reports.representatives.show', $rep->id) }}"
                                                           class="btn btn-sm btn-outline-warning box-shadow-2 round">
                                                            <i class="ft-eye"></i> كشف حساب
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="py-3 text-center text-muted">لا يوجد مناديب مسجلين أو لا توجد مبيعات في هذا الخط.</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-2 d-flex justify-content-center">
                                        {{ $representatives->links() }}
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
