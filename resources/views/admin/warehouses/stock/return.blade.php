@extends('layouts.admin')

@section('title', 'تسجيل مرتجع من المخزن')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-minus-circle"></i> تسجيل مرتجع / صرف يدوي </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">المخازن</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.show', $warehouse->id) }}">{{ $warehouse->name }}</a></li>
                                <li class="breadcrumb-item active">مرتجع</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card border-top-danger border-top-3">
                    <div class="card-content">
                        <div class="card-body">
                            <form action="{{ route('admin.warehouses.stock.return.process', $warehouse->id) }}" method="POST">
                                @csrf


                                <h4 class="form-section"><i class="ft-info"></i> بيانات الصنف والمرتجع</h4>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="drug_id">اختر الصنف (من الرصيد المتوفر) <span class="text-danger">*</span></label>
                                            <select name="drug_id" id="drug_id" class="form-control select2" required>
                                                <option value="">-- اختر الدواء --</option>
                                                @foreach($warehouse->drugs as $drug)
                                                    {{-- نعرض فقط الأدوية التي لها رصيد --}}
                                                    @if($drug->pivot->quantity > 0)
                                                        <option value="{{ $drug->id }}" data-max="{{ $drug->pivot->quantity }}">
                                                            {{ $drug->name }} (الرصيد الحالي: {{ $drug->pivot->quantity }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('drug_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quantity">الكمية المرتجعة <span class="text-danger">*</span></label>
                                            <input type="number" id="quantity" name="quantity" class="form-control" min="1" placeholder="مثال: 5" required>
                                            <small id="max_qty_hint" class="text-muted d-none">أقصى كمية متاحة: <span id="max_val" class="font-weight-bold">0</span></small>
                                            @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>


                                <div class="form-actions text-left">
                                    <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="btn btn-secondary mr-1">
                                        <i class="ft-x"></i> إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="la la-save"></i> تأكيد خصم الرصيد
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const drugSelect = document.getElementById('drug_id');
            const maxHint = document.getElementById('max_qty_hint');
            const maxValSpan = document.getElementById('max_val');
            const qtyInput = document.getElementById('quantity');

            drugSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const maxQty = selectedOption.getAttribute('data-max');

                if (maxQty) {
                    maxHint.classList.remove('d-none');
                    maxValSpan.textContent = maxQty;
                    qtyInput.setAttribute('max', maxQty);
                } else {
                    maxHint.classList.add('d-none');
                    qtyInput.removeAttribute('max');
                }
            });
        });
    </script>
@endsection
