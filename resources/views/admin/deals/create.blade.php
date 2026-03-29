@extends('layouts.admin')

@section('title', 'إضافة اتفاق جديد')

@section('style')
    <script src="{{ asset('assets/admin/js/scripts/cdn.min.js') }}" defer></script>
    <style>
        .multi-select-box {
            border: 1px solid #ccd6e6; border-radius: 5px; padding: 10px; max-height: 300px; overflow-y: auto; background: #f9f9f9;
        }
        .checkbox-item { display: flex; align-items: center; margin-bottom: 8px; cursor: pointer; padding: 8px; border-radius: 4px; transition: background 0.2s; border-bottom: 1px solid #eee; }
        .checkbox-item:hover { background-color: #e6f0ff; }
        .checkbox-item input { margin-left: 10px; transform: scale(1.2); }
        .commission-display { font-size: 1.2rem; font-weight: bold; color: #28a745; background: #e8f5e9; padding: 5px 10px; border-radius: 5px; display: block; text-align: center; margin-top: 5px; }
    </style>
@endsection

@section('content')
    <div class="app-content content" x-data="dealApp">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-12">
                    <h3 class="content-header-title"> <i class="la la-handshake-o"></i> تسجيل اتفاق مالي جديد </h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card border-top-primary border-top-3 box-shadow-1">
                    <div class="card-content">
                        <div class="card-body">
                            <form class="form" action="{{ route('admin.deals.store') }}" method="POST">
                                @csrf

                                <h4 class="form-section"><i class="ft-target"></i> الهدف والعمولة</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>الطبيب <span class="text-danger">*</span></label>
                                            <select name="doctor_id" id="doctor_select" class="form-control select2" required>
                                                <option value="">-- اختر الطبيب --</option>
                                                @foreach($doctors as $doc)
                                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>التارجت المطلوب (ج.م) <span class="text-danger">*</span></label>
                                            <input type="number" name="target_amount" class="form-control font-weight-bold"
                                                   x-model="target" @input="calculateCommission" placeholder="0" >
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>النسبة (%) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="commission_percentage" class="form-control"
                                                       x-model="percentage" @input="calculateCommission" placeholder="10" min="0" max="100" step="0.1" required>
                                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>قيمة العمولة المستحقة</label>
                                            <input type="text" class="form-control commission-display" :value="formatNumber(commission_amount) + ' ج.م'" readonly>
                                            <input type="hidden" name="commission_amount" :value="commission_amount">
                                        </div>
                                    </div>
                                </div>

                                <div class="row bg-light p-2 rounded mb-2 border">
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label class="text-bold-600">حالة دفع العمولة (الآن):</label>
                                            <select name="status" class="form-control" x-model="payment_status" @change="updatePaidAmount">
                                                <option value="2">آجل (يصرف بعد تحقيق التارجت)</option>
                                                <option value="1">مدفوع بالكامل (مقدم كامل)</option>
                                                <option value="3">دفع جزء مقدم</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4" x-show="payment_status == 3 || payment_status == 1" x-transition>
                                        <div class="form-group mb-0">
                                            <label class="text-primary text-bold-600">المبلغ المدفوع (ج.م):</label>
                                            <input type="number" name="paid_amount" class="form-control font-weight-bold text-success"
                                                   x-model="paid_amount" :readonly="payment_status == 1">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label>تاريخ بدء الاتفاق</label>
                                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="form-section d-flex justify-content-between">
                                            <span><i class="la la-hospital-o"></i> الصيدليات المشمولة <span class="text-danger">*</span></span>
                                            <span class="badge badge-info" x-show="selectedDoctorId && doctorCenterName" x-text="'فلتر المركز: ' + doctorCenterName"></span>
                                        </h4>
                                        <div class="form-group">
                                            <input type="text" x-model="pharmaSearch" class="form-control mb-2" placeholder="بحث في الصيدليات...">

                                            <div class="multi-select-box">
                                                <template x-for="pharma in filteredPharmacies" :key="pharma.id">
                                                    <label class="checkbox-item">
                                                        <input type="checkbox" name="pharmacists[]" :value="pharma.id">
                                                        <div class="d-inline-block ml-1">
                                                            <span x-text="pharma.name" class="d-block font-weight-bold"></span>
                                                            <small class="text-muted" x-text="pharma.center ? pharma.center.name : '-'"></small>
                                                        </div>
                                                    </label>
                                                </template>
                                                <div x-show="filteredPharmacies.length === 0" class="text-center text-muted mt-2">
                                                    <span x-show="!selectedDoctorId">اختر الطبيب أولاً أو ابحث...</span>
                                                    <span x-show="selectedDoctorId">لا توجد صيدليات مطابقة في هذا المركز</span>
                                                </div>
                                            </div>
                                            @error('pharmacists') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h4 class="form-section"><i class="la la-medkit"></i> الأدوية المشمولة (Scope)</h4>
                                        <div class="form-group">
                                            <input type="text" x-model="drugSearch" class="form-control mb-2" placeholder="بحث في الأدوية...">

                                            <div class="multi-select-box">
                                                <p class="text-muted font-small-3 mb-2 px-1"><i class="ft-info"></i> عدم اختيار أي دواء يعني أن الاتفاق يشمل <strong>جميع الأصناف</strong>.</p>

                                                <template x-for="drug in filteredDrugs" :key="drug.id">
                                                    <label class="checkbox-item">
                                                        <input type="checkbox" name="drugs[]" :value="drug.id">
                                                        <div class="d-inline-block ml-1 w-100">
                                                            <span x-text="drug.name"></span>
                                                            <span class="badge badge-sm float-right" :class="drug.line == 1 ? 'badge-info' : 'badge-warning'" x-text="'Line ' + drug.line"></span>
                                                        </div>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions mt-3 text-left">
                                    <button type="button" class="btn btn-secondary mr-1" onclick="history.back()">تراجع</button>
                                    <button type="submit" class="btn btn-primary"><i class="la la-save"></i> حفظ الاتفاق</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dealApp', () => ({

                doctors: @json($doctors),
                pharmacists: @json($pharmacists),
                drugs: @json($drugs),

                // المتغيرات
                selectedDoctorId: '',
                doctorCenterName: '',
                pharmaSearch: '',
                drugSearch: '',
                target: '',
                percentage: '',
                commission_amount: 0,
                payment_status: '2',
                paid_amount: 0,

                init() {
                    this.calculateCommission();

                    const that = this;
                    $('#doctor_select').on('change', function (e) {
                        that.selectedDoctorId = $(this).val();
                        that.updateDoctorInfo();
                    });
                },

                updateDoctorInfo() {
                    if (this.selectedDoctorId) {
                        const doc = this.doctors.find(d => d.id == this.selectedDoctorId);

                        if (doc && doc.center_id) {
                            const samplePharma = this.pharmacists.find(p => p.center_id == doc.center_id);
                            this.doctorCenterName = samplePharma && samplePharma.center ? samplePharma.center.name : '';
                        } else {
                            this.doctorCenterName = '';
                        }
                    } else {
                        this.doctorCenterName = '';
                    }
                },

                // فلترة الصيدليات
                get filteredPharmacies() {
                    let list = this.pharmacists;

                    // 1. فلتر حسب مركز الطبيب
                    if (this.selectedDoctorId) {
                        const doc = this.doctors.find(d => d.id == this.selectedDoctorId);
                        if (doc && doc.center_id) {
                            list = list.filter(p => p.center_id == doc.center_id);
                        }
                    }

                    // 2. فلتر حسب البحث
                    if (this.pharmaSearch) {
                        list = list.filter(p => p.name.toLowerCase().includes(this.pharmaSearch.toLowerCase()));
                    }

                    return list;
                },

                get filteredDrugs() {
                    if (!this.drugSearch) return this.drugs;
                    return this.drugs.filter(d => d.name.toLowerCase().includes(this.drugSearch.toLowerCase()));
                },

                calculateCommission() {
                    const t = parseFloat(this.target) || 0;
                    const p = parseFloat(this.percentage) || 0;
                    this.commission_amount = (t * (p / 100)).toFixed(2);
                    this.updatePaidAmount();
                },

                updatePaidAmount() {
                    if (this.payment_status === '1') {
                        this.paid_amount = this.commission_amount;
                    } else if (this.payment_status === '2') {
                        this.paid_amount = 0;
                    }
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('en-US').format(num);
                }
            }));
        });
    </script>
@endsection
