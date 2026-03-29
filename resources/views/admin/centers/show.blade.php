@extends('layouts.admin')

@section('title', 'تفاصيل مركز: ' . $center->name)

@section('style')
    <style>
        .profile-header { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%; text-align: center; }
        .avatar-circle { width: 100px; height: 100px; font-size: 3rem; line-height: 100px; margin: 0 auto 15px; display: block; }

        .stat-card {
            border: none; border-radius: 12px; color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100px;
            display: flex; align-items: center;
        }
        .stat-card:hover { transform: translateY(-5px); }

        .bg-gradient-primary { background: linear-gradient(45deg, #666ee8, #764ba2); }
        .bg-gradient-success { background: linear-gradient(45deg, #11998e, #38ef7d); }
        .bg-gradient-info { background: linear-gradient(45deg, #2193b0, #6dd5ed); }
        .bg-gradient-danger { background: linear-gradient(45deg, #ff5f6d, #ffc371); }

        .nav-tabs .nav-link { font-weight: 600; color: #555; border: none; }
        .nav-tabs .nav-link.active { border-bottom: 3px solid #666ee8; color: #666ee8; background: transparent; }
        .tab-content { background: #fff; padding: 20px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-tabs { border-bottom: 1px solid #ddd; background: #fff; border-radius: 10px 10px 0 0; padding-top: 10px; padding-left: 10px; }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-map-marker"></i> تفاصيل المركز </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.centers.index') }}">المراكز</a></li>
                                <li class="breadcrumb-item active">{{ $center->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12 text-right">
                    <a href="{{ route('admin.centers.edit', $center->id) }}" class="btn btn-warning box-shadow-2">
                        <i class="ft-edit"></i> تعديل البيانات
                    </a>
                </div>
            </div>

            <div class="content-body">

                <div class="row match-height">
                    <div class="col-xl-4 col-lg-5 col-12">
                        <div class="profile-header">
                            <div class="avatar-circle bg-gradient-info rounded-circle text-white mb-2 box-shadow-2">
                                <i class="la la-building"></i>
                            </div>
                            <h3 class="text-bold-700 mb-0">{{ $center->name }}</h3>
                            <p class="text-muted">مركز توزيع جغرافي</p>

                            <div class="badge badge-pill badge-light border-info text-info mb-2 px-2 py-1">
                                <i class="la la-map"></i> محافظة {{ $center->province->name ?? 'غير محدد' }}
                            </div>

                            <hr class="my-2">

                            <div class="row text-left mt-2">
                                <div class="col-12 mb-1">
                                    <span class="text-muted"><i class="la la-calendar mr-1"></i> تاريخ التسجيل:</span>
                                    <strong class="float-right text-dark">{{ $center->created_at->format('Y-m-d') }}</strong>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted"><i class="la la-clock-o mr-1"></i> آخر تحديث:</span>
                                    <strong class="float-right text-dark">{{ $center->updated_at->diffForHumans() }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-7 col-12">
                        <div class="row">
                            <div class="col-md-6 col-12 mb-2">
                                <div class="card stat-card bg-gradient-primary">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="text-white mb-0">{{ $center->pharmacists->count() }}</h2>
                                            <span class="font-medium-1">صيدلية مسجلة</span>
                                        </div>
                                        <i class="la la-hospital-o font-large-3 opacity-50"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-12 mb-2">
                                <div class="card stat-card bg-gradient-danger">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="text-white mb-0">{{ $center->doctors->count() }}</h2>
                                            <span class="font-medium-1">طبيب مسجل</span>
                                        </div>
                                        <i class="la la-user-md font-large-3 opacity-50"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-12">
                                <div class="card stat-card bg-gradient-success" style="height: auto; min-height: 100px;">
                                    <div class="card-body w-100 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="text-white mb-0">الأداء العام للمركز</h4>
                                            <span class="font-small-3 text-white opacity-75">متابعة نشاط العملاء والأطباء في نطاق {{ $center->name }}</span>
                                        </div>
                                        <div class="text-right">
                                            <a href="{{ route('admin.reports.center', $center->id) }}" class="btn btn-sm btn-white text-success box-shadow-1">
                                                <i class="la la-bar-chart"></i> عرض التقرير المالي
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab" aria-selected="true">
                                    <i class="la la-hospital-o"></i> الصيدليات
                                    <span class="badge badge-pill badge-primary ml-1">{{ $center->pharmacists->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab" aria-selected="false">
                                    <i class="la la-user-md"></i> الأطباء
                                    <span class="badge badge-pill badge-danger ml-1">{{ $center->doctors->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content px-1 pt-1">
                            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                                <div class="d-flex justify-content-between align-items-center mb-2 mt-1">
                                    <h5 class="text-bold-600">قائمة الصيدليات</h5>
                                    <a href="{{ route('admin.pharmacists.create', ['center_id' => $center->id]) }}" class="btn btn-primary btn-sm box-shadow-2">
                                        <i class="ft-plus"></i> إضافة صيدلية جديدة
                                    </a>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="bg-light">
                                        <tr>
                                            <th>الاسم</th>
                                            <th>العنوان</th>
                                            <th>الهاتف</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($center->pharmacists as $pharmacist)
                                            <tr>
                                                <td class="text-primary font-weight-bold">{{ $pharmacist->name }}</td>
                                                <td>{{ $pharmacist->address }}</td>
                                                <td>{{ $pharmacist->phone }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.pharmacists.show', $pharmacist->id) }}" class="btn btn-outline-info" title="عرض"><i class="ft-eye"></i></a>
                                                        <a href="{{ route('admin.pharmacists.edit', $pharmacist->id) }}" class="btn btn-outline-warning" title="تعديل"><i class="ft-edit"></i></a>
                                                        <form action="{{ route('admin.pharmacists.destroy', $pharmacist->id) }}" method="POST" style="display:inline">
                                                            @csrf @method('DELETE')
                                                            <button type="button" class="btn btn-outline-danger" onclick="if(confirm('حذف الصيدلية؟')){this.form.submit()}" title="حذف"><i class="ft-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center py-3 text-muted">لا توجد صيدليات مسجلة في هذا المركز.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                                <div class="d-flex justify-content-between align-items-center mb-2 mt-1">
                                    <h5 class="text-bold-600">قائمة الأطباء</h5>
                                    <a href="{{ route('admin.doctors.create', ['center_id' => $center->id]) }}" class="btn btn-danger btn-sm box-shadow-2">
                                        <i class="ft-plus"></i> إضافة طبيب جديد
                                    </a>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="bg-light">
                                        <tr>
                                            <th>الاسم</th>
                                            <th>التخصص</th>
                                            <th>النسبة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($center->doctors as $doctor)
                                            <tr>
                                                <td class="text-danger font-weight-bold">{{ $doctor->name }}</td>
                                                <td>{{ $doctor->speciality ?? 'غير محدد' }}</td>
                                                <td><span class="badge badge-pill badge-warning">{{ $doctor->commission_rate }}%</span></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="btn btn-outline-info" title="ملف الطبيب"><i class="ft-user"></i></a>
                                                        <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="btn btn-outline-warning" title="تعديل"><i class="ft-edit"></i></a>
                                                        <form action="{{ route('admin.doctors.destroy', $doctor->id) }}" method="POST" style="display:inline">
                                                            @csrf @method('DELETE')
                                                            <button type="button" class="btn btn-outline-danger" onclick="if(confirm('حذف الطبيب؟')){this.form.submit()}" title="حذف"><i class="ft-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center py-3 text-muted">لا يوجد أطباء مسجلين في هذا المركز.</td></tr>
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
