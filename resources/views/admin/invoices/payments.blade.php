@extends('layouts.admin')

@section('title', 'سداد الفاتورة #' . $invoice->serial_number)

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-8 col-12">
                    <h3 class="content-header-title">
                        <i class="ft-dollar-sign"></i> إدارة دفعات الفاتورة #{{ $invoice->serial_number ?? $invoice->id }}
                    </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">الفواتير</a></li>
                                <li class="breadcrumb-item active">سداد الدفعات</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-4 col-12 text-right">
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary box-shadow-2">
                        <i class="ft-arrow-right"></i> رجوع للفواتير
                    </a>
                </div>
            </div>

            <div class="content-body">
                @include('admin.includes.alerts.success')
                @include('admin.includes.alerts.errors')

                {{-- 1. ملخص الفاتورة المالي --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-info text-white box-shadow-1">
                            <div class="card-body text-center p-2">
                                <h5 class="text-white mb-1"><i class="la la-file-text"></i> إجمالي الفاتورة</h5>
                                <h3 class="text-white font-weight-bold mb-0">{{ number_format($invoice->final_total, 2) }} ج.م</h3>
                                <small>العميل: {{ $invoice->pharmacist->name ?? '-' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white box-shadow-1">
                            <div class="card-body text-center p-2">
                                <h5 class="text-white mb-1"><i class="la la-check-circle"></i> تم سداده</h5>
                                <h3 class="text-white font-weight-bold mb-0">{{ number_format($invoice->paid_amount, 2) }} ج.م</h3>
                                <small>{{ $invoice->payments->count() }} دفعات مسجلة</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white box-shadow-1">
                            <div class="card-body text-center p-2">
                                <h5 class="text-white mb-1"><i class="la la-warning"></i> المتبقي للدفع</h5>
                                <h3 class="text-white font-weight-bold mb-0">{{ number_format($invoice->remaining_amount, 2) }} ج.م</h3>
                                <small>
                                    @if($invoice->status == 1) خالص @elseif($invoice->status == 3) دفع جزئي @else آجل بالكامل @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- 2. نموذج تسجيل دفعة جديدة --}}
                    @if($invoice->remaining_amount > 0)
                        <div class="col-xl-4 col-lg-12">
                            <div class="card border-top-success border-top-3">
                                <div class="card-header pb-0">
                                    <h4 class="card-title">إضافة دفعة جديدة</h4>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.invoices.payments.store', $invoice->id) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label>المبلغ المراد سداده (ج.م) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="0.01" max="{{ $invoice->remaining_amount }}" name="amount" class="form-control font-weight-bold text-success" value="{{ $invoice->remaining_amount }}" required>
                                            <small class="text-muted">الحد الأقصى: {{ number_format($invoice->remaining_amount, 2) }}</small>
                                        </div>
                                        <div class="form-group">
                                            <label>تاريخ السداد <span class="text-danger">*</span></label>
                                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>ملاحظات (اختياري)</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="رقم إيصال أو أي تفاصيل..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-block"><i class="la la-save"></i> حفظ الدفعة</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 3. سجل الدفعات السابقة --}}
                    <div class="{{ $invoice->remaining_amount > 0 ? 'col-xl-8' : 'col-12' }} col-lg-12">
                        <div class="card">
                            <div class="card-header pb-0">
                                <h4 class="card-title"><i class="la la-history"></i> السجل الزمني للدفعات</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped text-center">
                                        <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>تاريخ الدفعة</th>
                                            <th>المبلغ (ج.م)</th>
                                            <th>ملاحظات</th>
                                            <th>إجراءات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($invoice->payments as $index => $payment)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="font-weight-bold">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                                <td class="text-success font-weight-bold">{{ number_format($payment->amount, 2) }}</td>
                                                <td class="text-muted">{{ $payment->notes ?: '-' }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#editPaymentModal{{ $payment->id }}">
                                                        <i class="ft-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.invoices.payments.destroy', [$invoice->id, $payment->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الدفعة؟ سيتم إعادة المبلغ المتبقي للفاتورة.')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ft-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>

                                            <!-- Modal تعديل الدفعة -->
                                            <div class="modal fade text-left" id="editPaymentModal{{ $payment->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content border-top-warning border-top-3">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">تعديل الدفعة</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="{{ route('admin.invoices.payments.update', [$invoice->id, $payment->id]) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>المبلغ (الحد الأقصى: {{ number_format($payment->amount + $invoice->remaining_amount, 2) }})</label>
                                                                    <input type="number" step="0.01" min="0.01" max="{{ $payment->amount + $invoice->remaining_amount }}" name="amount" class="form-control" value="{{ $payment->amount }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>تاريخ السداد</label>
                                                                    <input type="date" name="payment_date" class="form-control" value="{{ $payment->payment_date->format('Y-m-d') }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>ملاحظات</label>
                                                                    <textarea name="notes" class="form-control">{{ $payment->notes }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                                                                <button type="submit" class="btn btn-warning">حفظ التعديلات</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-muted py-3">لم يتم تسجيل أي دفعات لهذه الفاتورة بعد.</td>
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
@endsection
