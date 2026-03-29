@extends('layouts.admin')

@section('title', 'إضافة رصيد للمخزن')

@section('style')
    <script src="{{ asset('assets/admin/js/scripts/cdn.min.js') }}" defer></script>
    <style>
        .searchable-dropdown { position: relative; }
        .search-results {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 999;
            background: white; border: 1px solid #ddd; max-height: 200px;
            overflow-y: auto; border-radius: 0 0 5px 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .search-item {
            padding: 10px; cursor: pointer; border-bottom: 1px solid #f1f1f1;
            transition: background 0.2s;
        }
        .search-item:hover, .search-item.active { background-color: #e2f0ff; color: #007bff; }
        .table th { background-color: #f5f7fa; }
        .table-responsive { overflow-x: visible; overflow-y: visible; }
        [x-cloak] { display: none !important; }

        .stock-badge { font-size: 0.8rem; font-weight: normal; }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-cubes"></i> إدارة الأرصدة </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">المخازن</a></li>
                                <li class="breadcrumb-item active">{{ $warehouse->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section id="basic-form-layouts">
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card {{ $warehouse->type == 'sub' ? 'border-top-info' : 'border-top-success' }} border-top-3">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        @if($warehouse->type == 'sub')
                                            <i class="ft-refresh-cw"></i> طلب نقل من المخزن الرئيسي ({{ $warehouse->parent->name ?? 'غير محدد' }})
                                        @else
                                            <i class="ft-plus-square"></i> تسجيل وارد جديد (شراء)
                                        @endif
                                    </h4>
                                </div>

                                <div class="card-content collapse show">
                                    <div class="card-body">

                                        @include('admin.includes.alerts.success')
                                        @include('admin.includes.alerts.errors')

                                        @if($errors->any())
                                            <div class="alert alert-danger mb-2">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <form class="form" action="{{ route('admin.warehouses.stock.store', $warehouse->id) }}" method="POST">
                                            @csrf

                                            <div class="form-body">
                                                @if($warehouse->type == 'sub')
                                                    <div class="alert alert-info mb-2 border-info">
                                                        <i class="ft-info mr-1"></i> <strong>تنبيه:</strong> الكميات التي ستسحبها سيتم خصمها فوراً من رصيد المخزن الرئيسي. لا يمكنك طلب أكثر من المتاح.
                                                    </div>
                                                @endif

                                                <div class="stock-manager" x-data="stockManager">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered mb-0">
                                                            <thead>
                                                            <tr>
                                                                <th width="50%">اسم الدواء</th>
                                                                <th width="20%">الرصيد المتاح (الرئيسي)</th>
                                                                <th width="20%">الكمية المطلوبة</th>
                                                                <th width="10%" class="text-center">حذف</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <template x-for="(row, index) in items" :key="index">
                                                                <tr>
                                                                    <td>
                                                                        <div class="searchable-dropdown">
                                                                            <input type="text"
                                                                                   x-model="row.drug_name"
                                                                                   @focus="row.show_list = true"
                                                                                   @input="filterDrugs(index)"
                                                                                   @click.away="row.show_list = false"
                                                                                   class="form-control"
                                                                                   placeholder="ابحث عن الدواء..." required>

                                                                            <input type="hidden" :name="'items['+index+'][drug_id]'" x-model="row.drug_id" required>

                                                                            <div class="search-results" x-show="row.show_list && row.filtered_drugs.length > 0" x-cloak>
                                                                                <template x-for="drug in row.filtered_drugs" :key="drug.id">
                                                                                    <div class="search-item" @click="selectDrug(index, drug)">
                                                                                        <span x-text="drug.name"></span>
                                                                                        <span class="badge badge-sm float-right ml-1"
                                                                                              :class="drug.line == 1 ? 'badge-info' : 'badge-warning'"
                                                                                              x-text="'Line ' + drug.line"></span>

                                                                                        <span x-show="drug.max_quantity !== null" class="badge badge-sm badge-secondary float-right ml-1">
                                                                                            متاح: <span x-text="drug.max_quantity"></span>
                                                                                        </span>
                                                                                    </div>
                                                                                </template>
                                                                            </div>
                                                                        </div>
                                                                    </td>

                                                                    <td>
                                                                        <input type="text" class="form-control bg-light text-center"
                                                                               readonly
                                                                               :value="row.max_quantity !== null ? row.max_quantity : 'غير محدود (شراء)'">
                                                                    </td>

                                                                    <td>
                                                                        <input type="number" min="1"
                                                                               :max="row.max_quantity !== null ? row.max_quantity : ''"
                                                                               class="form-control text-center font-weight-bold"
                                                                               :class="{'border-danger text-danger': row.error}"
                                                                               :name="'items['+index+'][quantity]'"
                                                                               x-model="row.quantity"
                                                                               @input="validateQuantity(index)"
                                                                               required>
                                                                        <small class="text-danger d-block" x-show="row.error" x-text="row.error"></small>
                                                                    </td>

                                                                    <td class="text-center">
                                                                        <button type="button" class="btn btn-danger btn-sm" @click="removeItem(index)">
                                                                            <i class="ft-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                            </tbody>
                                                            <tfoot>
                                                            <tr>
                                                                <td colspan="4" class="text-center p-2">
                                                                    <button type="button" class="btn btn-success btn-min-width box-shadow-2" @click="addNewRow()">
                                                                        <i class="la la-plus"></i> إضافة صنف آخر
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-actions mt-3">
                                                <button type="button" class="btn btn-secondary mr-1" onclick="history.back()">
                                                    <i class="ft-x"></i> إلغاء
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="la la-check-circle"></i> تأكيد العملية
                                                </button>
                                            </div>
                                        </form>
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

@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('stockManager', () => ({
                allDrugs: @json($drugs),
                items: [],

                init() {
                    this.addNewRow();
                },

                addNewRow() {
                    this.items.push({
                        drug_id: '',
                        drug_name: '',
                        quantity: 1,
                        max_quantity: null,
                        error: '',
                        show_list: false,
                        filtered_drugs: this.allDrugs.slice(0, 10)
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        alert('يجب إضافة صنف واحد على الأقل');
                    }
                },

                filterDrugs(index) {
                    const search = this.items[index].drug_name.toLowerCase();
                    this.items[index].show_list = true;
                    this.items[index].filtered_drugs = this.allDrugs.filter(drug => {
                        return drug.name.toLowerCase().includes(search);
                    }).slice(0, 15);
                },

                selectDrug(index, drug) {
                    this.items[index].drug_id = drug.id;
                    this.items[index].drug_name = drug.name;
                    this.items[index].max_quantity = drug.max_quantity;
                    this.items[index].show_list = false;
                    this.validateQuantity(index);
                },

                validateQuantity(index) {
                    let row = this.items[index];
                    if (row.max_quantity !== null && parseInt(row.quantity) > row.max_quantity) {
                        row.error = 'الكمية غير متوفرة في المخزن الرئيسي';
                    } else {
                        row.error = '';
                    }
                }
            }));
        });
    </script>
@endsection
