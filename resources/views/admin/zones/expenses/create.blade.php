@extends('layouts.admin')

@section('title', 'إضافة مصروف للمنطقة')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-plus-circle"></i> إضافة مصروف جديد </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.zones.index') }}">المناطق</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.zones.show', $zone->id) }}">{{ $zone->name }}</a></li>
                                <li class="breadcrumb-item active">إضافة مصروف</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <form action="{{ route('admin.zones.expenses.store', $zone->id) }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="expense_date">تاريخ الصرف <span class="text-danger">*</span></label>
                                            <input type="date" id="expense_date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount">المبلغ (ج.م) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="0" id="amount" name="amount" class="form-control" placeholder="أدخل قيمة المصروف" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">بيان المصروف <span class="text-danger">*</span></label>
                                            <textarea id="description" name="description" class="form-control" rows="3" placeholder="مثال: نثريات ضيافة للعملاء، مواصلات مندوب..." required></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions text-right">
                                    <a href="{{ route('admin.zones.show', $zone->id) }}" class="btn btn-warning mr-1">
                                        <i class="ft-x"></i> إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="la la-check-square-o"></i> حفظ البيانات
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
