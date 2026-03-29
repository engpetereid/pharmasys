@extends('layouts.admin')

@section('title', 'مبيعات المندوب ' . $representative->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="col-12">
                    <h3 class="content-header-title"> ملف المندوب: {{ $representative->name }} </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.reports.representatives.index') }}">المناديب</a></li>
                                <li class="breadcrumb-item active">{{ $representative->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-gradient-x-warning text-white">
                            <div class="card-body text-center">
                                <h6>إجمالي المبيعات المحققة</h6>
                                <h3>{{ number_format($totalSales, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-gradient-x-success text-white">
                            <div class="card-body text-center">
                                <h6>إجمالي التحصيلات (المدفوع)</h6>
                                <h3>{{ number_format($totalCollected, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">سجل الفواتير والزيارات</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>التاريخ</th>
                                                <th>الصيدلية (العميل)</th>
                                                <th>الطبيب</th>
                                                <th>قيمة الفاتورة</th>
                                                <th>الحالة</th>
                                                <th>عرض</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($invoices as $invoice)
                                                <tr>
                                                    <td>{{ $invoice->id }}</td>
                                                    <td>{{ $invoice->invoice_date }}</td>
                                                    <td>
                                                        {{ $invoice->pharmacist->name ?? '-' }}
                                                        <br><small class="text-muted">{{ $invoice->pharmacist->center->name ?? '' }}</small>
                                                    </td>
                                                    <td>
                                                        @if($invoice->doctors && $invoice->doctors->count() > 0)
                                                            {{ $invoice->doctors->pluck('name')->implode('، ') }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="font-weight-bold">{{ number_format($invoice->final_total, 2) }}</td>
                                                    <td>
                                                        @if($invoice->status == 1) <span class="badge badge-success">مدفوع</span>
                                                        @elseif($invoice->status == 2) <span class="badge badge-warning">آجل</span>
                                                        @else <span class="badge badge-info">جزئي</span> @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="ft-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted py-2">لا توجد مبيعات مسجلة لهذا المندوب</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-center">
                                        {{ $invoices->links() }}
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
