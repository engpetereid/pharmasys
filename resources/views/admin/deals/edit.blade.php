@extends('layouts.admin')

@section('title', 'تعديل الاتفاق #' . $deal->id)

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
        .badge-center { font-size: 0.75rem;  color: black; padding: 1px 6px; border-radius: 4px; margin-right: 5px; }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="mb-2 content-header row">
                <div class="content-header-left col-12">
                    <h3 class="content-header-title"> <i class="la la-edit"></i> تعديل اتفاق تارجت (Doctor Deal) </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.deals.index') }}">الاتفاقيات</a></li>
                                <li class="breadcrumb-item active">تعديل #{{ $deal->id }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card border-top-warning border-top-3 box-shadow-1">
                    <div class="card-content">
                        <div class="card-body">
                            <form class="form" action="{{ route('admin.deals.update', $deal->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="form-body" x-data="dealApp">
                                    <h4 class="form-section"><i class="ft-target"></i> الهدف والعمولة</h4>



                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>الطبيب <span class="text-danger">*</span></label>
                                                <select name="doctor_id" class="form-control select2" disabled>
                                                    <option value="{{ $deal->doctor_id }}" selected>{{ $deal->doctor->name }}</option>
                                                </select>
                                                <small class="text-muted" x-show="doctorCenterName">المركز: <span x-text="doctorCenterName" class="text-bold-600"></span></small>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>التارجت المطلوب (ج.م) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" name="target_amount" class="form-control font-weight-bold"
                                                           x-model="target" @input="calculateCommission" placeholder="0" >
                                                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>النسبة (%) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" name="commission_percentage" class="form-control"
                                                           x-model="percentage" @input="calculateCommission" placeholder="10" min="0" max="100" step="0.1" >
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

                                    <div class="p-2 mb-2 border rounded row bg-light">
                                        <div class="col-md-4">
                                            <div class="mb-0 form-group">
                                                <label class="text-bold-600">حالة الدفع الحالية:</label>
                                                <select name="status" class="form-control" x-model="payment_status" @change="updatePaidAmount">
                                                    <option value="2">آجل / جاري العمل</option>
                                                    <option value="1">مدفوع بالكامل</option>
                                                    <option value="3">دفع جزء مقدم</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4" x-show="payment_status == 3 || payment_status == 1" x-transition>
                                            <div class="mb-0 form-group">
                                                <label class="text-primary text-bold-600">المبلغ المدفوع (ج.م):</label>
                                                <input type="number" name="paid_amount" class="form-control font-weight-bold text-success"
                                                       x-model="paid_amount" :readonly="payment_status == 1">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-0 form-group">
                                                <label>تاريخ البدء</label>
                                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $deal->start_date) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="form-section d-flex justify-content-between align-items-center">
                                                <span><i class="la la-hospital-o"></i> الصيدليات المشمولة <span class="text-danger">*</span></span>
                                                <button type="button" @click="toggleCenterFilter" class="btn btn-sm btn-outline-info" :class="{'active': filterByDoctorCenter}" title="فلترة حسب مركز الطبيب">
                                                    <i class="la la-filter"></i> مركز الطبيب
                                                </button>
                                            </h4>

                                            <div class="form-group">
                                                <input type="text" x-model="pharmaSearch" class="mb-2 form-control" placeholder="بحث في الصيدليات...">

                                                <div class="multi-select-box">
                                                    <template x-for="pharma in filteredPharmacies" :key="pharma.id">
                                                        <label class="checkbox-item">
                                                            <input type="checkbox" name="pharmacists[]" :value="pharma.id"
                                                                   :checked="selectedPharmacies.includes(pharma.id)">
                                                            <div class="ml-1 d-inline-block">
                                                                <span x-text="pharma.name" class="d-block font-weight-bold"></span>
                                                                <span class="badge-center" x-show="pharma.center" x-text="pharma.center ? pharma.center.name : '-'"></span>
                                                            </div>
                                                        </label>
                                                    </template>
                                                    <div x-show="filteredPharmacies.length === 0" class="mt-2 text-center text-muted">لا توجد نتائج</div>
                                                </div>
                                                @error('pharmacists') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        {{-- الأدوية --}}
                                        <div class="col-md-6">
                                            <h4 class="form-section"><i class="la la-medkit"></i> الأدوية المشمولة (Scope)</h4>
                                            <div class="form-group">
                                                <input type="text" x-model="drugSearch" class="mb-2 form-control" placeholder="بحث في الأدوية...">

                                                <div class="multi-select-box">
                                                    <p class="px-1 mb-2 text-muted font-small-3"><i class="ft-info"></i> عدم اختيار أي دواء يعني أن الاتفاق يشمل <strong>جميع الأصناف</strong>.</p>

                                                    <template x-for="drug in filteredDrugs" :key="drug.id">
                                                        <label class="checkbox-item">
                                                            <input type="checkbox" name="drugs[]" :value="drug.id"
                                                                   :checked="selectedDrugs.includes(drug.id)">
                                                            <div class="ml-1 d-inline-block w-100">
                                                                <span x-text="drug.name"></span>
                                                                <span class="float-right badge badge-sm" :class="drug.line == 1 ? 'badge-info' : 'badge-warning'" x-text="'Line ' + drug.line"></span>
                                                            </div>
                                                        </label>
                                                    </template>
                                                    <div x-show="filteredDrugs.length === 0" class="mt-2 text-center text-muted">لا توجد نتائج</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-left form-actions">
                                        <button type="button" class="mr-1 btn btn-secondary" onclick="history.back()">إلغاء</button>
                                        <button type="submit" class="btn btn-warning"><i class="la la-check-square-o"></i> حفظ التعديلات</button>
                                    </div>
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

                // البيانات
                selectedPharmacies: @json($deal->pharmacists->pluck('id')),
                selectedDrugs: @json($deal->drugs->pluck('id')),

                // بيانات الاتفاق
                currentDoctorId: '{{ $deal->doctor_id }}',
                doctorCenterId: '',
                doctorCenterName: '',

                // متغيرات البحث
                pharmaSearch: '',
                drugSearch: '',
                filterByDoctorCenter: true,

                // متغيرات الحساب
                target: '{{ $deal->target_amount }}',
                percentage: '{{ $deal->commission_percentage }}',
                commission_amount: '{{ $deal->commission_amount }}',
                payment_status: '{{ $deal->status }}',
                paid_amount: '{{ $deal->paid_amount }}',

                init() {
                    this.calculateCommission();
                    this.fetchDoctorCenter();
                },

                fetchDoctorCenter() {
                    const doc = this.doctors.find(d => d.id == this.currentDoctorId);
                    if (doc && doc.center_id) {
                        this.doctorCenterId = doc.center_id;
                        const samplePharma = this.pharmacists.find(p => p.center_id == doc.center_id);
                        this.doctorCenterName = samplePharma && samplePharma.center ? samplePharma.center.name : '';
                    }
                },

                calculateCommission() {
                    const t = parseFloat(this.target) || 0;
                    const p = parseFloat(this.percentage) || 0;
                    this.commission_amount = (t * (p / 100)).toFixed(2);

                    if(this.payment_status == '1') {
                        this.updatePaidAmount();
                    }
                },

                updatePaidAmount() {
                    if (this.payment_status == '1') {
                        this.paid_amount = this.commission_amount;
                    } else if (this.payment_status == '2') {
                        this.paid_amount = 0;
                    }
                },

                toggleCenterFilter() {
                    this.filterByDoctorCenter = !this.filterByDoctorCenter;
                },

                //  الفلتره
                get filteredPharmacies() {
                    let list = this.pharmacists;

                    if (this.filterByDoctorCenter && this.doctorCenterId) {
                        list = list.filter(p => p.center_id == this.doctorCenterId);
                    }

                    // 2. الفلتر بالبحث
                    if (this.pharmaSearch) {
                        list = list.filter(p => p.name.toLowerCase().includes(this.pharmaSearch.toLowerCase()));
                    }

                    // ترتيب: المختار فى الاول
                    return list.sort((a, b) => {
                        const aSelected = this.selectedPharmacies.includes(a.id);
                        const bSelected = this.selectedPharmacies.includes(b.id);
                        return (aSelected === bSelected) ? 0 : aSelected ? -1 : 1;
                    });
                },

                get filteredDrugs() {
                    if (!this.drugSearch) return this.drugs;
                    return this.drugs.filter(d => d.name.toLowerCase().includes(this.drugSearch.toLowerCase()));
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('en-US').format(num);
                }
            }));
        });
    </script>
@endsection
