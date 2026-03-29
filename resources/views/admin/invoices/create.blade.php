@extends('layouts.admin')

@section('title', 'فاتورة مبيعات جديدة')

@section('style')
    <script src="{{ asset('assets/admin/js/scripts/cdn.min.js') }}" defer></script>
    <style>
        *:focus {
            outline: none !important;
        }

        .searchable-dropdown {
            position: relative;
            overflow: visible !important;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 9999;
            background: white;
            border: 1px solid #ddd;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .invoice-table-wrapper {
            overflow: visible !important;
        }

        .invoice-table-wrapper table {
            overflow: visible !important;
        }

        .search-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f9f9f9;
            font-size: 14px;
        }

        .search-item:hover,
        .search-item.active-descendant {
            background-color: #e6f7ff;
            color: #007bff;
            border-left: 4px solid #007bff;
            padding-left: 11px;
        }

        .table th {
            background: #f1f3f5;
            border-top: none;
        }

        .table .form-control {
            height: 40px;
            font-size: 15px;
        }

        tr:focus-within {
            background-color: #f8fbff;
        }

        .totals-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection

@section('content')
    <div class="app-content content" x-data="invoiceApp">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="col-12">
                    <h3 class="text-primary font-weight-bold"><i class="la la-cart-plus"></i> فاتورة مبيعات جديدة</h3>
                </div>
            </div>

            <div class="content-body">
                <section>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">

                            @if(session('success'))
                                <div class="alert alert-success shadow-sm mb-3"><strong>تم
                                        بنجاح!</strong> {{ session('success') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger shadow-sm mb-3">
                                    <ul class="mb-0 pl-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form class="form" action="{{ route('admin.invoices.store') }}" method="POST"
                                  @keydown.enter.prevent>
                                @csrf

                                <div class="row">
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="font-weight-bold">تاريخ الفاتورة</label>
                                        <input type="date" name="invoice_date" class="form-control"
                                               value="{{ old('invoice_date', $today) }}" required>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="font-weight-bold">الخط (Line) <span
                                                class="text-danger">*</span></label>
                                        <select name="line" class="form-control" x-model="selectedLine"
                                                @change="resetItems()">
                                            <option value="">-- اختر الخط --</option>
                                            <option value="1">Line 1</option>
                                            <option value="2">Line 2</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label>المحافظة (فلتر)</label>
                                        <select class="form-control" x-model="filterProvinceId">
                                            <option value="">-- الكل --</option>
                                            <template x-for="prov in provinces" :key="prov.id">
                                                <option :value="prov.id" x-text="prov.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="font-weight-bold">رقم الفاتورة (Serial)</label>
                                        <input type="text" name="serial_number" class="form-control" placeholder="1001"
                                               required>
                                    </div>
                                </div>

                                <hr class="my-2">

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="font-weight-bold">الصيدلية <span class="text-danger">*</span></label>
                                        <div class="searchable-dropdown"
                                             x-data="searchableSelect('pharmacist_id', filteredPharmacists, 'name', 'pharmacy', null, 300)"
                                             x-effect="items = filteredPharmacists"
                                             x-on:selected="updateDoctors($event.detail)">

                                            <div class="position-relative">
                                                <input type="text" x-model="search" @focus="open = true"
                                                       @keydown.down.prevent="highlightNext()"
                                                       @keydown.up.prevent="highlightPrev()"
                                                       @keydown.enter.prevent="selectHighlighted()"
                                                       @click.outside="closeAndValidate()" class="form-control"
                                                       placeholder="ابحث باسم الصيدلية..."
                                                       :class="{'is-invalid': !selectedItem && search !== ''}" required>
                                                <input type="hidden" name="pharmacist_id" :value="selectedItem?.id || ''">
                                            </div>

                                            <div class="search-results" x-show="open && filteredItems.length > 0" x-cloak>
                                                <template x-for="(item, index) in filteredItems" :key="item.id">
                                                    <div class="search-item"
                                                         :class="{'active-descendant': index === activeIndex}"
                                                         @click="selectItem(item)" @mouseenter="activeIndex = index">
                                                        <strong x-text="item.name"></strong>
                                                        <small class="text-muted d-block"
                                                               x-text="item.center ? item.center.name : ''"></small>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="font-weight-bold">الأطباء</label>
                                        <div class="border rounded bg-light p-2" style="height: 100px; overflow-y: auto;">
                                            <div x-show="availableDoctors.length === 0" class="text-muted small mt-1">
                                                اختر صيدلية أولاً لظهور الأطباء أصحاب الاتفاقيات.
                                            </div>
                                            <template x-for="doctor in availableDoctors" :key="doctor.id">
                                                <label class="d-flex align-items-center mb-1 cursor-pointer">
                                                    <input type="checkbox" name="doctor_ids[]" :value="doctor.id" x-model="selectedDoctorIds" class="mr-2" style="transform: scale(1.2);">
                                                    <span x-text="doctor.name" class="font-weight-bold text-dark"></span>
                                                    <span class="badge badge-light ml-auto text-muted font-small-2" x-text="doctor.speciality"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h5 class="font-weight-bold mb-2"><i class="la la-cubes"></i> الأصناف</h5>

                                    <div x-show="!selectedLine" class="alert alert-warning p-2">
                                        <i class="la la-info-circle"></i> أولاً للبدء (Line) اختر الخط .
                                    </div>

                                    <div class="invoice-table-wrapper" x-show="selectedLine">
                                        <table class="table mb-0">
                                            <thead class="bg-light">
                                            <tr>
                                                <th width="35%">اسم الدواء</th>
                                                <th width="15%">السعر</th>
                                                <th width="10%">الكمية</th>
                                                <th width="10%">خصم %</th>
                                                <th width="20%">الإجمالي</th>
                                                <th width="10%"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <template x-for="(row, index) in invoiceItems" :key="index">
                                                <tr>
                                                    <td class="p-1 align-middle">
                                                        <div class="searchable-dropdown position-relative">
                                                            <input type="text"
                                                                   :id="index === 0 ? 'first_drug_input' : 'drug_input_'+index"
                                                                   x-model="row.drug_name_display"
                                                                   @focus="showDrugs(index)" @input="filterDrugs(index)"
                                                                   @keydown.down.prevent="rowHighlightNext(index)"
                                                                   @keydown.up.prevent="rowHighlightPrev(index)"
                                                                   @keydown.enter.prevent="selectHighlightedDrug(index)"
                                                                   @click.outside="row.show_list = false; validateDrug(index)"
                                                                   class="form-control border-0 bg-transparent"
                                                                   style="border-bottom: 1px solid #ced4da !important;"
                                                                   placeholder="ابحث..." required>
                                                            <input type="hidden" :name="'items['+index+'][drug_id]'"
                                                                   x-model="row.drug_id">

                                                            <div class="search-results"
                                                                 x-show="row.show_list && row.filtered_drugs.length > 0"
                                                                 x-cloak>
                                                                <template x-for="(drug, dIndex) in row.filtered_drugs"
                                                                          :key="drug.id">
                                                                    <div class="search-item"
                                                                         :class="{'active-descendant': dIndex === row.active_index}"
                                                                         @click="selectDrug(index, drug)">
                                                                        <span x-text="drug.name"></span>
                                                                        <span class="float-right badge badge-light"
                                                                              x-text="drug.price"></span>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="p-1 align-middle">
                                                        <input type="text"
                                                               class="form-control border-0 bg-light text-center"
                                                               :value="row.unit_price" readonly tabindex="-1">
                                                        <input type="hidden" :name="'items['+index+'][unit_price]'"
                                                               :value="row.unit_price">
                                                    </td>
                                                    <td class="p-1 align-middle">
                                                        <input type="number" :id="'qty_input_'+index" min="0" value=""
                                                               class="form-control text-center font-weight-bold"
                                                               :name="'items['+index+'][quantity]'" x-model="row.quantity"
                                                               @input="calculateRowTotal(index)"
                                                               @keydown.enter.prevent="focusNextField('discount_input_'+index)"
                                                               required>
                                                    </td>
                                                    <td class="p-1 align-middle">
                                                        <input type="number" :id="'discount_input_'+index" min="0"
                                                               max="100" step="0.5" class="form-control text-center"
                                                               :name="'items['+index+'][pharmacist_discount_percentage]'"
                                                               x-model="row.discount" @input="calculateRowTotal(index)"
                                                               @keydown.enter.prevent="handleRowEnter(index)">
                                                    </td>
                                                    <td class="p-1 align-middle">
                                                        <input type="text"
                                                               class="form-control border-0 bg-transparent text-center font-weight-bold text-primary"
                                                               :name="'items['+index+'][row_total]'" x-model="row.total"
                                                               readonly tabindex="-1">
                                                    </td>
                                                    <td class="text-center align-middle p-1">
                                                        <button type="button" class="btn btn-sm text-danger"
                                                                @click="removeRow(index)" tabindex="-1"><i
                                                                class="la la-trash font-medium-3"></i></button>
                                                    </td>
                                                </tr>
                                            </template>
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-light btn-block mt-2 text-muted"
                                                @click="addNewRow()">
                                            <i class="la la-plus"></i> إضافة صنف
                                        </button>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-7">
                                        <label>ملاحظات</label>
                                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="totals-card p-3 bg-light">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>الإجمالي:</span>
                                                <span class="font-weight-bold" x-text="grand_total_before_discount"></span>
                                                <input type="hidden" name="total_amount"
                                                       :value="grand_total_before_discount">
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>الخصم:</span>
                                                <span class="text-danger font-weight-bold">- <span
                                                        x-text="grand_total_discount"></span></span>
                                                <input type="hidden" name="total_discount" :value="grand_total_discount">
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                                <span class="font-large-1 font-weight-bold text-primary">الصافي:</span>
                                                <span class="font-large-1 font-weight-bold text-primary">
                                                    <span x-text="grand_final_total"></span> ج.م
                                                </span>
                                                <input type="hidden" name="final_total" :value="grand_final_total">
                                            </div>

                                            <div class="mt-3">
                                                <label class="font-weight-bold">حالة الدفع</label>
                                                <select name="status" class="form-control mb-2" x-model="payment_status"
                                                        @change="updatePaymentStatus()">
                                                    <option value="2">آجل (Deferred)</option>
                                                    <option value="1">مدفوع بالكامل (Paid)</option>
                                                    <option value="3">دفع جزئي (Partial)</option>
                                                </select>

                                                <div x-show="payment_status == 3 || payment_status == 1" x-transition
                                                     x-cloak>
                                                    <label class="text-muted small">المبلغ المدفوع</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" name="paid_amount"
                                                               class="form-control font-weight-bold"
                                                               :class="{'text-success': payment_status == 1, 'bg-white': payment_status == 3}"
                                                               x-model="paid_amount" @input="validatePaidAmount()"
                                                               :readonly="payment_status == 1" placeholder="0.00">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">ج.م</span>
                                                        </div>
                                                    </div>

                                                    <div class="mt-1 text-right" x-show="payment_status == 3">
                                                        <small class="text-muted">المتبقي: </small>
                                                        <span class="text-danger font-weight-bold"
                                                              x-text="(parseFloat(grand_final_total || 0) - parseFloat(paid_amount || 0)).toFixed(2)"></span>
                                                        ج.م
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions text-right mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg box-shadow-2 px-4">
                                        <i class="la la-check-circle"></i> حفظ الفاتورة
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function normalizeArabic(text) {
            if (!text) return "";
            return String(text).replace(/[أإآ]/g, 'ا').replace(/[ة]/g, 'ه').replace(/[ى]/g, 'ي').toLowerCase();
        }

        function focusNext(id, delay = 50) {
            setTimeout(() => {
                const el = document.getElementById(id);
                if (el) {
                    el.focus();
                    if (el.value) el.select();
                }
            }, delay);
        }

        window.searchableSelect = function (inputName, items, labelKey, listType = 'default', nextFocusId = null, nextDelay = 50) {
            return {
                search: '',
                open: false,
                selectedItem: null,
                items: items,
                activeIndex: -1,

                init() {
                    const hiddenInput = document.querySelector(`input[name="${inputName}"]`);
                    const oldId = (hiddenInput ? hiddenInput.value : null);
                    if (oldId && this.items.length) {
                        this.selectedItem = this.items.find(item => item.id == oldId);
                        if (this.selectedItem) {
                            this.search = this.getItemLabel(this.selectedItem);
                            this.$dispatch('selected', this.selectedItem);
                        }
                    }

                    window.addEventListener(`reset-input-${inputName}`, () => {
                        this.reset();
                    });
                },

                reset() {
                    this.search = '';
                    this.selectedItem = null;
                    this.activeIndex = -1;
                    this.open = false;
                    this.$dispatch('selected', null);
                },

                get filteredItems() {
                    if (this.search === '') return this.items.slice(0, 50);
                    const term = normalizeArabic(this.search);
                    return this.items.filter(item => {
                        let nameMatch = normalizeArabic(item[labelKey]).includes(term);
                        let secondaryMatch = false;
                        if (listType === 'pharmacy' && item.center) secondaryMatch = normalizeArabic(item.center.name).includes(term);
                        return nameMatch || secondaryMatch;
                    }).slice(0, 100);
                },

                getItemLabel(item) {
                    if (!item) return '';
                    if (listType === 'pharmacy') return item.name;
                    return item.name;
                },

                highlightNext() { if (this.activeIndex < this.filteredItems.length - 1) this.activeIndex++; },
                highlightPrev() { if (this.activeIndex > 0) this.activeIndex--; },

                selectHighlighted() {
                    if (this.activeIndex >= 0 && this.filteredItems[this.activeIndex]) {
                        this.selectItem(this.filteredItems[this.activeIndex]);
                    }
                },

                selectItem(item) {
                    this.selectedItem = item;
                    this.search = this.getItemLabel(item);
                    this.open = false;
                    this.activeIndex = -1;
                    this.$dispatch('selected', item);

                    if (nextFocusId) focusNext(nextFocusId, nextDelay);
                },

                closeAndValidate() {
                    setTimeout(() => {
                        if (!this.selectedItem || normalizeArabic(this.search) !== normalizeArabic(this.getItemLabel(this.selectedItem))) {
                            this.reset();
                        }
                        this.open = false;
                    }, 200);
                }
            };
        };

        window.invoiceApp = function () {
            return {
                provinces: @json($provinces),
                allPharmacists: @json($pharmacists),
                allDrugs: @json($drugs),
                selectedLine: '',
                filterProvinceId: '',
                selectedPharmacistId: '',
                selectedDoctorIds: [], // مصفوفة لحفظ الأطباء المختارين
                invoiceItems: [],

                grand_total_before_discount: '0.00',
                grand_total_discount: '0.00',
                grand_final_total: '0.00',

                paid_amount: '',
                payment_status: '2',

                updatePaymentStatus() {
                    const total = parseFloat(this.grand_final_total) || 0;

                    if (this.payment_status == '1') {
                        this.paid_amount = total.toFixed(2);
                    } else if (this.payment_status == '2') {
                        this.paid_amount = '';
                    } else if (this.payment_status == '3') {
                        if (parseFloat(this.paid_amount) >= total) {
                            this.paid_amount = '';
                        }
                    }
                },

                validatePaidAmount() {
                    const total = parseFloat(this.grand_final_total) || 0;
                    let paid = parseFloat(this.paid_amount);

                    if (isNaN(paid)) paid = 0;

                    if (paid >= total && total > 0) {
                        this.paid_amount = total.toFixed(2);
                        this.payment_status = '1';
                    }
                },

                syncPaidAmountWithTotal() {
                    const total = parseFloat(this.grand_final_total) || 0;
                    let paid = parseFloat(this.paid_amount) || 0;

                    if (this.payment_status == '1') {
                        this.paid_amount = total.toFixed(2);
                    } else if (this.payment_status == '3') {
                        if (paid >= total) {
                            this.paid_amount = total.toFixed(2);
                            this.payment_status = '1';
                        }
                    }
                },

                calculateGrandTotals() {
                    let total = 0;
                    let discount = 0;
                    this.invoiceItems.forEach(item => {
                        let price = parseFloat(item.unit_price) || 0;
                        let qty = parseFloat(item.quantity) || 0;
                        let discPercent = parseFloat(item.discount) || 0;

                        let itemTotal = price * qty;
                        total += itemTotal;
                        discount += (itemTotal * (discPercent / 100));
                    });
                    this.grand_total_before_discount = total.toFixed(2);
                    this.grand_total_discount = discount.toFixed(2);
                    this.grand_final_total = (total - discount).toFixed(2);

                    this.syncPaidAmountWithTotal();
                },

                get filteredPharmacists() {
                    if (!this.filterProvinceId) return this.allPharmacists;
                    return this.allPharmacists.filter(ph => ph.center && ph.center.province_id == this.filterProvinceId);
                },

                get availableDoctors() {
                    if (!this.selectedPharmacistId) return [];
                    const pharmacist = this.allPharmacists.find(ph => ph.id == this.selectedPharmacistId);
                    if (!pharmacist || !pharmacist.deals) return [];

                    const doctorsMap = new Map();
                    pharmacist.deals.forEach(deal => {
                        if (deal.doctor && !doctorsMap.has(deal.doctor.id)) {
                            doctorsMap.set(deal.doctor.id, deal.doctor);
                        }
                    });
                    return Array.from(doctorsMap.values());
                },

                // فلترة الأدوية بناءً على الأطباء المختارين واتفاقياتهم
                get availableDrugs() {
                    if (!this.selectedLine) return [];

                    let lineDrugs = this.allDrugs.filter(d => d.line == this.selectedLine);

                    // إذا لم يتم اختيار أي طبيب، تتاح جميع أدوية الخط (بيع حر للصيدلية)
                    if (this.selectedDoctorIds.length === 0) {
                        return lineDrugs;
                    }

                    const pharmacist = this.allPharmacists.find(ph => ph.id == this.selectedPharmacistId);
                    if (!pharmacist || !pharmacist.deals) return [];

                    let allowedDrugIds = new Set();
                    let isGeneral = false;

                    // دمج (Union) أدوية جميع الأطباء المختارين
                    pharmacist.deals.forEach(deal => {
                        if (deal.doctor && this.selectedDoctorIds.includes(deal.doctor.id.toString())) {
                            if (deal.is_general) {
                                isGeneral = true; // اتفاق عام يفتح كل الأدوية
                            } else if (deal.drugs) {
                                deal.drugs.forEach(id => allowedDrugIds.add(id));
                            }
                        }
                    });

                    // إذا كان أحد الأطباء لديه اتفاق عام، نرجع كل أدوية الخط
                    if (isGeneral) {
                        return lineDrugs;
                    }

                    // إرجاع الأدوية التي يقع معرفها ضمن قائمة المسموح بها فقط
                    return lineDrugs.filter(d => allowedDrugIds.has(d.id));
                },

                init() {
                    this.addNewRow();

                    this.$watch('filterProvinceId', (value) => {
                        this.selectedPharmacistId = '';
                        window.dispatchEvent(new CustomEvent('reset-input-pharmacist_id'));
                    });

                    this.$watch('selectedPharmacistId', (value) => {
                        this.selectedDoctorIds = []; // تصفير الأطباء عند تغيير الصيدلية
                        this.validateExistingItemsAgainstDeals();
                    });

                    // مراقبة اختيار الأطباء لتنظيف الفاتورة من الأدوية غير المسموحة
                    this.$watch('selectedDoctorIds', (value) => {
                        this.validateExistingItemsAgainstDeals();
                    });
                },

                updateDoctors(pharmacist) {
                    this.selectedPharmacistId = pharmacist ? pharmacist.id : '';
                },

                // دالة للتأكد أن الأدوية المضافة في الفاتورة ما زالت متاحة بعد تغيير الأطباء
                validateExistingItemsAgainstDeals() {
                    const allowedDrugs = this.availableDrugs;
                    const allowedIds = allowedDrugs.map(d => d.id);

                    this.invoiceItems.forEach(item => {
                        if (item.drug_id && !allowedIds.includes(item.drug_id)) {
                            // مسح الدواء لأنه لم يعد ضمن الاتفاقيات المتاحة
                            item.drug_name_display = '';
                            item.drug_id = '';
                            item.unit_price = '0.00';
                            item.total = '0.00';
                            item.quantity = '';
                            item.discount = '';
                        }
                    });
                    this.calculateGrandTotals();
                },

                resetItems() {
                    if (this.invoiceItems.length > 0 && this.invoiceItems[0].drug_id) {
                        if (confirm('تغيير الخط سيحذف الأصناف الحالية. هل توافق؟')) {
                            this.invoiceItems = [];
                            this.addNewRow();
                            this.calculateGrandTotals();
                        }
                    } else {
                        this.invoiceItems = [];
                        this.addNewRow();
                    }
                },

                addNewRow() {
                    this.invoiceItems.push({
                        drug_id: '',
                        drug_name_display: '',
                        unit_price: '0.00',
                        quantity: '',
                        discount: '',
                        total: '0.00',
                        show_list: false,
                        active_index: -1,
                        filtered_drugs: []
                    });
                    this.$nextTick(() => {
                        const newIndex = this.invoiceItems.length - 1;
                        focusNext('drug_input_' + newIndex);
                    });
                },

                removeRow(index) {
                    if (this.invoiceItems.length > 1) {
                        this.invoiceItems.splice(index, 1);
                        this.calculateGrandTotals();
                    }
                },

                focusNextField(id) { focusNext(id); },

                handleRowEnter(index) {
                    this.calculateRowTotal(index);
                    if (index === this.invoiceItems.length - 1) {
                        this.addNewRow();
                    } else {
                        focusNext('drug_input_' + (index + 1));
                    }
                },

                showDrugs(index) {
                    this.invoiceItems[index].show_list = true;
                    this.filterDrugs(index);
                },

                filterDrugs(index) {
                    const search = normalizeArabic(this.invoiceItems[index].drug_name_display);
                    this.invoiceItems[index].show_list = true;
                    this.invoiceItems[index].active_index = 0;

                    if (search === '') {
                        this.invoiceItems[index].filtered_drugs = this.availableDrugs.slice(0, 100);
                    } else {
                        this.invoiceItems[index].filtered_drugs = this.availableDrugs.filter(drug =>
                            normalizeArabic(drug.name).includes(search)
                        ).slice(0, 100);
                    }
                },

                rowHighlightNext(index) {
                    let item = this.invoiceItems[index];
                    if (item.active_index < item.filtered_drugs.length - 1) item.active_index++;
                },
                rowHighlightPrev(index) {
                    let item = this.invoiceItems[index];
                    if (item.active_index > 0) item.active_index--;
                },
                selectHighlightedDrug(index) {
                    let item = this.invoiceItems[index];
                    if (item.active_index >= 0 && item.filtered_drugs[item.active_index]) {
                        this.selectDrug(index, item.filtered_drugs[item.active_index]);
                    }
                },

                selectDrug(index, drug) {
                    let item = this.invoiceItems[index];
                    item.drug_id = drug.id;
                    item.drug_name_display = drug.name;
                    item.unit_price = parseFloat(drug.price).toFixed(2);
                    item.show_list = false;
                    item.active_index = -1;

                    this.calculateRowTotal(index);
                    focusNext('qty_input_' + index);
                },

                validateDrug(index) {
                    let item = this.invoiceItems[index];
                    setTimeout(() => {
                        if (!item.drug_id || (item.drug_id && item.drug_name_display === '')) {
                            item.drug_name_display = '';
                            item.drug_id = '';
                            item.unit_price = '0.00';
                            item.total = '0.00';
                        }
                    }, 200);
                },

                calculateRowTotal(index) {
                    let item = this.invoiceItems[index];
                    let price = parseFloat(item.unit_price) || 0;
                    let qty = parseFloat(item.quantity) || 0;
                    let discPercent = parseFloat(item.discount) || 0;

                    let totalBeforeDisc = price * qty;
                    let discountValue = totalBeforeDisc * (discPercent / 100);
                    item.total = (totalBeforeDisc - discountValue).toFixed(2);

                    this.calculateGrandTotals();
                }
            };
        };
    </script>
@endsection
