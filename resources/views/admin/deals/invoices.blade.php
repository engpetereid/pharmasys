@extends('layouts.admin')

@section('title', 'فواتير التارجت - ' . $deal->doctor->name)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="mb-2 content-header row">
                <div class="col-12">
                    <h3 class="content-header-title">
                        <i class="la la-list"></i> تفاصيل مبيعات التارجت
                    </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.deals.index') }}">الاتفاقات</a></li>
                                <li class="breadcrumb-item active">{{ $deal->doctor->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- الكارت التعريفي للاتفاق --}}
                <div class="card border-left-info border-left-3">
                    <div class="card-body">
                        <div class="text-center row">
                            <div class="col-md-3 border-right">
                                <span class="text-muted">الطبيب</span><br>
                                <strong class="font-medium-2">{{ $deal->doctor->name }}</strong>
                            </div>

                            <div class="col-md-3 border-right">
                                <span class="text-muted">الصيدليات المشمولة</span><br>
                                <strong class="font-medium-2 text-primary">
                                    @if($deal->pharmacists->count() > 0)
                                        {{ $deal->pharmacists->pluck('name')->implode(' | ') }}
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </strong>
                            </div>

                            <div class="col-md-3 border-right">
                                <span class="text-muted">الهدف (Target)</span><br>
                                <strong class="font-medium-2">{{ number_format($deal->target_amount) }}</strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted">المحقق (تم تحصيله)</span><br>
                                <strong class="text-success font-medium-3">{{ number_format($deal->achieved_amount) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الجدول الأول: الفواتير المحصلة (التي أضيفت للتارجت بالفعل) --}}
                <div class="card border-top-success border-top-3">
                    <div class="card-header pb-0">
                        <h4 class="card-title text-success"><i class="la la-check-circle"></i> الفواتير المكتملة والمحصلة (تم احتسابها في التارجت)</h4>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body pt-1">
                            <div class="table-responsive">
                                <table class="table mb-0 table-hover table-striped">
                                    <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>التاريخ</th>
                                        <th>الصيدلية</th>
                                        <th>الخط (Line)</th>
                                        <th>المندوب</th>
                                        <th>القيمة المحتسبة (للاتفاق)</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($paidInvoices as $invoice)
                                        <tr>
                                            <td>{{ $invoice->serial_number }}</td>
                                            <td>{{ $invoice->invoice_date }}</td>
                                            <td>
                                                <span class="text-bold-600">{{ $invoice->pharmacist->name ?? '-' }}</span>
                                                <br>
                                                <small class="text-muted">{{ $invoice->pharmacist->center->name ?? '' }}</small>
                                            </td>
                                            <td>
                                                @if($invoice->line == 1)
                                                    <span class="badge badge-info">Line 1</span>
                                                @else
                                                    <span class="badge badge-warning">Line 2</span>
                                                @endif
                                            </td>
                                            <td>{{ $invoice->representative->name ?? '-' }}</td>
                                            <td class="font-weight-bold text-success">
                                                {{-- القيمة التي شاركت بها الفاتورة في التارجت من جدول الربط --}}
                                                {{ number_format($invoice->pivot->contribution_amount ?? $invoice->final_total, 2) }} ج.م
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary box-shadow-1">
                                                    <i class="ft-eye"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-3 text-center text-muted">لا توجد فواتير محصلة ومكتملة لهذا الاتفاق حتى الآن.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2 d-flex justify-content-center">
                                {{ $paidInvoices->links() }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الجدول الثاني: الفواتير الآجلة أو المدفوعة جزئياً (المنتظرة) --}}
                <div class="card border-top-warning border-top-3 mt-3">
                    <div class="card-header pb-0">
                        <h4 class="card-title text-warning text-darken-2"><i class="la la-clock-o"></i> الفواتير الآجلة والجزئية (لم يتم احتسابها بعد)</h4>
                        <p class="text-muted font-small-3 mt-1">هذه الفواتير تابعة للطبيب ولكنها لم تحصل بالكامل بعد، ولذلك لم تضاف قيمة مبيعاتها إلى التارجت المحقق.</p>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body pt-0">

                            {{-- ملخص الفواتير المنتظرة --}}
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="alert alert-secondary text-center mb-0">
                                        <h6 class="text-muted">إجمالي المبيعات المتوقعة للاتفاق (قيد التحصيل)</h6>
                                        <h3 class="text-bold-700 text-dark mb-0">{{ number_format($unpaidTotalContribution, 2) }} ج.م</h3>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-warning text-center mb-0 border-warning">
                                        <h6 class=" text-darken-3">عمولة الطبيب المنتظرة (بنسبة {{ $deal->commission_percentage }}%)</h6>
                                        <h3 class="text-bold-700  text-darken-3 mb-0">{{ number_format($potentialCommission, 2) }} ج.م</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table mb-0 table-hover table-bordered">
                                    <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>التاريخ</th>
                                        <th>الصيدلية</th>
                                        <th>حالة الفاتورة</th>
                                        <th>صافى الفاتورة</th>
                                        <th class="bg-warning bg-lighten-4">جمهورى الفاتورة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($unpaidInvoicesList as $invoice)
                                        <tr>
                                            <td>{{ $invoice->serial_number }}</td>
                                            <td>{{ $invoice->invoice_date }}</td>
                                            <td>
                                                <span class="text-bold-600">{{ $invoice->pharmacist->name ?? '-' }}</span>
                                            </td>
                                            <td>
                                                @if($invoice->status == 2)
                                                    <span class="badge badge-danger">آجل</span>
                                                @elseif($invoice->status == 3)
                                                    <span class="badge badge-warning">جزئي</span>
                                                @endif
                                            </td>
                                            <td class="text-muted">{{ number_format($invoice->final_total, 2) }} ج.م</td>
                                            <td class="font-weight-bold text-warning text-darken-3 bg-warning bg-lighten-5">
                                                {{ number_format($invoice->potential_contribution, 2) }} ج.م
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary box-shadow-1">
                                                    <i class="ft-eye"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-3 text-center text-muted">لا توجد فواتير آجلة أو جزئية معلقة لهذا الاتفاق.</td>
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
@endsection
