@extends('layouts.admin')

@section('title', 'ملف المندوب: ' . $representative->name)

@section('style')
    <style>
        .profile-header { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); text-align: center; height: 100%; }
        .avatar-circle {
            width: 110px; height: 110px; font-size: 3.5rem; line-height: 110px;
            margin: 0 auto 20px; display: block; border: 5px solid #f0f2f5;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-list { text-align: left; margin-top: 20px; }
        .info-list li { padding: 12px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .info-list li:last-child { border-bottom: none; }

        .bg-gradient-purple { background: linear-gradient(45deg, #7b4397, #dc2430); }
        .bg-gradient-blue { background: linear-gradient(45deg, #1E9FF2, #00BFA5); }
        .bg-gradient-orange { background: linear-gradient(45deg, #FF9149, #FF5B5C); }

        .zone-card { transition: all 0.3s; border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
        .zone-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .zone-icon { width: 50px; height: 50px; line-height: 50px; text-align: center; border-radius: 50%; color: white; font-size: 1.5rem; }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title"> <i class="la la-briefcase"></i> ملف المندوب </h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.representatives.index') }}">المناديب</a></li>
                                <li class="breadcrumb-item active">{{ $representative->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12 text-right">
                    <a href="{{ route('admin.representatives.edit', $representative->id) }}" class="btn btn-warning box-shadow-2">
                        <i class="ft-edit"></i> تعديل البيانات
                    </a>
                </div>
            </div>

            <div class="content-body">

                <div class="row match-height">
                    <div class="col-xl-4 col-lg-5 col-12">
                        <div class="profile-header">
                            <div class="avatar-circle bg-gradient-purple rounded-circle text-white">
                                {{ substr($representative->name, 0, 1) }}
                            </div>
                            <h3 class="text-bold-700 mb-1">{{ $representative->name }}</h3>
                            <span class="badge badge-pill badge-light border-primary text-primary">مندوب مبيعات ودعاية</span>

                            <ul class="info-list list-unstyled">
                                <li>
                                    <span class="text-muted"><i class="ft-phone mr-2"></i> الهاتف</span>
                                    <span class="text-dark text-bold-600">{{ $representative->phone }}</span>
                                </li>
                                <li>
                                    <span class="text-muted"><i class="ft-calendar mr-2"></i> تاريخ الانضمام</span>
                                    <span class="text-dark">{{ $representative->created_at->format('Y-m-d') }}</span>
                                </li>
                                <li>
                                    <span class="text-muted"><i class="ft-clock mr-2"></i> مدة الخدمة</span>
                                    <span class="text-success">{{ $representative->created_at->diffForHumans(null, true) }}</span>
                                </li>
                            </ul>

                            <div class="mt-3">
                                <a href="{{ route('admin.reports.representatives.show', $representative->id) }}" class="btn btn-block btn-outline-primary">
                                    <i class="la la-line-chart"></i> عرض تقرير الأداء المالي
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-7 col-12">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title text-info"><i class="la la-truck"></i> مناطق التوزيع (Sales Rep)</h4>
                                        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                                    </div>
                                    <div class="card-content collapse show">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover mb-0">
                                                    <thead class="bg-info white">
                                                    <tr>
                                                        <th>المحافظة</th>
                                                        <th>المنطقة</th>
                                                        <th>الخط (Line)</th>
                                                        <th>الحالة</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse($representative->salesZones as $zone)
                                                        <tr>
                                                            <td>{{ $zone->province->name }}</td>
                                                            <td class="text-bold-600">{{ $zone->name }}</td>
                                                            <td>
                                                                @if($zone->line == 1)
                                                                    <span class="badge badge-sm badge-info">Line 1</span>
                                                                @else
                                                                    <span class="badge badge-sm badge-warning">Line 2</span>
                                                                @endif
                                                            </td>
                                                            <td><span class="text-success"><i class="ft-check-circle"></i> نشط</span></td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center py-2 text-muted">لا يوجد مناطق بيع مسندة لهذا المندوب.</td>
                                                        </tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title text-success"><i class="la la-user-md"></i> مناطق الدعاية (Medical Rep)</h4>
                                        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                                    </div>
                                    <div class="card-content collapse show">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover mb-0">
                                                    <thead class="bg-success white">
                                                    <tr>
                                                        <th>المحافظة</th>
                                                        <th>المنطقة</th>
                                                        <th>الخط (Line)</th>
                                                        <th>الحالة</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse($representative->medicalZones as $zone)
                                                        <tr>
                                                            <td>{{ $zone->province->name }}</td>
                                                            <td class="text-bold-600">{{ $zone->name }}</td>
                                                            <td>
                                                                @if($zone->line == 1)
                                                                    <span class="badge badge-sm badge-info">Line 1</span>
                                                                @else
                                                                    <span class="badge badge-sm badge-warning">Line 2</span>
                                                                @endif
                                                            </td>
                                                            <td><span class="text-success"><i class="ft-check-circle"></i> نشط</span></td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center py-2 text-muted">لا يوجد مناطق دعاية مسندة لهذا المندوب.</td>
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

            </div>
        </div>
    </div>
@endsection
