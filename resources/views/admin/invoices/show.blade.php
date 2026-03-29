@extends('layouts.admin')

@section('title', 'تفاصيل الفاتورة: ' . ($invoice->serial_number ?? $invoice->id))

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="mb-2 no-print content-header row">
                <div class="col-md-6 col-12 content-header-left">
                    <h3 class="content-header-title"> <i class="ft-file-text"></i> تفاصيل الفاتورة </h3>
                </div>
                <div class="text-right col-md-6 col-12 content-header-right">
                    <div class="btn-group">
                        <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="mr-1 btn btn-warning box-shadow-2">
                            <i class="ft-edit"></i> تعديل
                        </a>
                        <a href="{{ route('admin.invoices.pdf', $invoice->id) }}" target="_blank" class="btn btn-secondary box-shadow-2">
                            <i class="la la-file-pdf-o"></i> تصدير PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body printable-area">
                <section class="card">
                    <div id="invoice-template" class="p-4 card-body">

                        <div class="mb-3 row">
                            <div class="text-left col-md-6">
                                <h3 class="text-primary text-bold-800">Bio vera</h3>
                                <p class="text-muted">نظام التوزيع الدوائي</p>
                            </div>
                            <div class="text-right col-md-6">
                                <h2 class="mb-1 text-bold-700">فاتورة #{{ $invoice->serial_number ?? $invoice->id }}</h2>
                                <p class="mb-1">التاريخ: {{ $invoice->invoice_date }}</p>
                                <span class="px-2 badge badge-{{ $invoice->line == 1 ? 'info' : 'warning' }} font-medium-1">Line {{ $invoice->line }}</span>
                            </div>
                        </div>

                        <hr>

                        <div class="pt-2 row">
                            <div class="mb-2 col-md-4">
                                <h6 class="pb-1 border-bottom text-bold-700 text-muted">بيانات الصيدالية</h6>
                                <p class="mb-0 font-medium-2 text-bold-800">{{ $invoice->pharmacist->name ?? 'غير محدد' }}</p>
                                <p class="mb-0 text-muted">{{ $invoice->pharmacist->center->name ?? '-' }}</p>
                                <p class="text-muted">{{ $invoice->pharmacist->phone ?? '-' }}</p>
                            </div>

                            <div class="mb-2 col-md-4">
                                <h6 class="pb-1 border-bottom text-bold-700 text-muted">الأطباء </h6>
                                @if($invoice->doctors && $invoice->doctors->count() > 0)
                                    @foreach($invoice->doctors as $doctor)
                                        <div class="mb-1">
                                            <p class="mb-0 font-medium-2 text-bold-800">د. {{ $doctor->name }}</p>
                                            <p class="mb-0 text-muted font-small-3">{{ $doctor->speciality ?? '-' }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="mb-0 font-medium-2 text-bold-800 text-muted">لا يوجد أطباء</p>
                                @endif
                            </div>

                            <div class="mb-2 col-md-4">
                                <h6 class="pb-1 border-bottom text-bold-700 text-muted">فريق التوزيع</h6>
                                <div class="mb-1">
                                    <span class="text-muted">بيع:</span> <strong class="text-primary">{{ $invoice->representative->name ?? '-' }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted">دعاية:</span> <strong class="text-info">{{ $invoice->medicalRepresentative->name ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- جدول الأصناف -->
                        <div class="pt-2 table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                <tr>
                                    <th class="text-center" width="5%">#</th>
                                    <th width="40%">اسم الدواء</th>
                                    <th class="text-center" width="10%">الكمية</th>
                                    <th class="text-right" width="15%">السعر</th>
                                    <th class="text-center" width="10%">الخصم</th>
                                    <th class="text-right" width="20%">الإجمالي</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoice->details as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="font-weight-bold">{{ $item->drug->name ?? 'صنف محذوف' }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-center">{{ $item->pharmacist_discount_percentage }}%</td>
                                        <td class="text-right font-weight-bold">{{ number_format($item->row_total, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- الملخص المالي -->
                        <div class="mt-2 row">
                            <div class="col-md-7">
                                <p class="text-bold-600">ملاحظات:</p>
                                <div class="p-2 mb-2 border alert alert-light text-muted">
                                    {{ $invoice->notes ?? 'لا توجد ملاحظات.' }}
                                </div>
                                <p class="text-bold-600">حالة الدفع:</p>
                                @if($invoice->status == 1)
                                    <span class="p-1 badge badge-success">مدفوع بالكامل</span>
                                @elseif($invoice->status == 2)
                                    <span class="p-1 badge badge-warning">آجل (Deferred)</span>
                                @else
                                    <span class="p-1 badge badge-info">دفع جزئي</span>
                                @endif
                            </div>

                            <div class="col-md-5">
                                <div class="p-3 border rounded bg-light">
                                    <div class="mb-1 d-flex justify-content-between">
                                        <span class="text-muted">الإجمالي:</span>
                                        <span class="font-weight-bold">{{ number_format($invoice->total_amount, 2) }}</span>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-danger">الخصم:</span>
                                        <span class="text-danger font-weight-bold">- {{ number_format($invoice->total_discount, 2) }}</span>
                                    </div>
                                    <div class="pt-2 mb-2 border-top d-flex justify-content-between">
                                        <span class="font-large-1 text-bold-700">الصافي:</span>
                                        <span class="font-large-1 text-primary text-bold-700">{{ number_format($invoice->final_total, 2) }} ج.م</span>
                                    </div>

                                    <div class="mb-1 text-success d-flex justify-content-between">
                                        <span class="text-bold-600">المدفوع:</span>
                                        <span class="text-bold-600">{{ number_format($invoice->paid_amount, 2) }}</span>
                                    </div>
                                    @if($invoice->remaining_amount > 0)
                                        <div class="text-danger d-flex justify-content-between">
                                            <span class="text-bold-600">المتبقي (آجل):</span>
                                            <span class="text-bold-600">{{ number_format($invoice->remaining_amount, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
