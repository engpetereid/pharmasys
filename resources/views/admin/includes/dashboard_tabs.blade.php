<div class="row match-height">
    <div class="col-12">
        <div class="card border-top-danger border-top-3">
            <div class="card-header">
                <h4 class="card-title"><i class="la la-bell text-danger"></i> تنبيهات المتابعة والتحصيل</h4>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-underline mb-1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-overdue" data-toggle="tab" href="#overdue" aria-controls="overdue" aria-selected="true">
                                <i class="la la-clock-o"></i> فواتير متاخره (> 3 شهور)
                                <span class="badge badge-pill badge-danger ml-1">{{ count($overdueInvoices) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-discount" data-toggle="tab" href="#discount" aria-controls="discount" aria-selected="false">
                                <i class="la la-percent"></i> فواتير (خصم > 51%)
                                <span class="badge badge-pill badge-warning ml-1">{{ count($highDiscountPharmacists) }}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="overdue" role="tabpanel" aria-labelledby="tab-overdue">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                    <tr><th>رقم الفاتورة</th><th>التاريخ</th><th>الصيدلية</th><th>المندوب</th><th>المتبقي (مديونية)</th><th>الإجراء</th></tr>
                                    </thead>
                                    <tbody>
                                    @forelse($overdueInvoices as $inv)
                                        <tr>
                                            <td>#{{ $inv->serial_number ?? $inv->id }}</td>
                                            <td class="text-danger">{{ $inv->invoice_date }}</td>
                                            <td>{{ $inv->pharmacist->name ?? '-' }}</td>
                                            <td>{{ $inv->representative->name ?? '-' }}</td>
                                            <td class="font-weight-bold text-danger">{{ number_format($inv->remaining_amount) }}</td>
                                            <td>
                                                <a href="{{ route('admin.invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-danger" target="_blank">عرض</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-2">ممتاز! لا توجد فواتير متعثرة منذ 3 شهور.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="discount" role="tabpanel" aria-labelledby="tab-discount">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                    <tr><th>الصيدلية</th><th>المركز</th><th>عدد الفواتير (خصم عالٍ)</th><th>الإجراء</th></tr>
                                    </thead>
                                    <tbody>
                                    @forelse($highDiscountPharmacists as $pharma)
                                        <tr>
                                            <td class="text-bold-600">{{ $pharma->name }}</td>
                                            <td>{{ $pharma->center->name ?? '-' }}</td>
                                            <td class="text-center"><span class="badge badge-warning text-white">{{ $pharma->high_discount_invoices_count }} فاتورة</span></td>
                                            <td>
                                                <a href="{{ route('admin.pharmacists.show', $pharma->id) }}" class="btn btn-sm btn-outline-info">فحص الملف</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-2">لا توجد صيدليات تجاوزت حد الخصم المسموح.</td></tr>
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
