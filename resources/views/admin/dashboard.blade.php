@extends('layouts.admin')

@section('title', 'لوحة التحكم الرئيسية')

@section('style')
    <style>
        .crypto-card-3 { border: none; border-radius: 10px; transition: transform 0.3s ease, box-shadow 0.3s ease; color: #fff; overflow: hidden; cursor: pointer; text-decoration: none !important; display: block; }
        .crypto-card-3:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); color: #fff; }
        .bg-gradient-success { background: linear-gradient(45deg, #28a745, #20c997); }
        .bg-gradient-info { background: linear-gradient(45deg, #17a2b8, #0dcaf0); }
        .bg-gradient-warning { background: linear-gradient(45deg, #ffc107, #fd7e14); }
        .bg-gradient-danger { background: linear-gradient(45deg, #dc3545, #ff6b6b); }
        .card-icon-bg { background-color: rgba(255, 255, 255, 0.2); border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        @keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } }
        .risk-alert { animation: pulse-red 2s infinite; }

        .filter-bar { background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; }
        .filter-group { display: flex; gap: 10px; align-items: center; }
        .filter-label { font-weight: bold; color: #555; margin-bottom: 0; margin-left: 10px; }
    </style>
@endsection

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="row mb-2">
                <div class="col-12">
                    <div class="filter-bar">
                        <h3 class="content-header-title mb-0">ملخص الأداء العام</h3>

                        <form action="{{ route('admin.dashboard') }}" method="GET" class="form-inline">
                            <div class="filter-group">
                                <label class="filter-label">الفترة:</label>

                                <select name="month" class="form-control border-primary" onchange="this.form.submit()">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }} ({{ $m }})
                                        </option>
                                    @endfor
                                </select>

                                <select name="year" class="form-control border-primary ml-1" onchange="this.form.submit()">
                                    @for($y = date('Y'); $y >= 2024; $y--)
                                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>

                                <button type="submit" class="btn btn-primary btn-icon ml-1" title="تحديث البيانات">
                                    <i class="la la-refresh"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <div class="content-body">


                <div id="crypto-stats-3" class="row">
                    <div class="col-xl-4 col-12">
                        <a href="{{ route('admin.invoices.index', ['type' => 'today']) }}" class="card crypto-card-3 bg-gradient-success pull-up">
                            <div class="card-content">
                                <div class="card-body pb-1">
                                    <div class="row">
                                        <div class="col-4 text-center align-self-center"><div class="card-icon-bg"><i class="la la-cart-plus text-white font-large-2"></i></div></div>
                                        <div class="col-8 pl-2">
                                            <h5 class="text-white mb-1">مبيعات اليوم</h5>
                                            <h3 class="text-white font-weight-bold">{{ number_format($todaySales, 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 col-12">
                        <a href="{{ route('admin.invoices.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="card crypto-card-3 bg-gradient-info pull-up">
                            <div class="card-content">
                                <div class="card-body pb-1">
                                    <div class="row">
                                        <div class="col-4 text-center align-self-center"><div class="card-icon-bg"><i class="la la-calendar-check-o text-white font-large-2"></i></div></div>
                                        <div class="col-8 pl-2">
                                            <h5 class="text-white mb-1">مبيعات شهر {{ $selectedMonth }}</h5>
                                            <h3 class="text-white font-weight-bold">{{ number_format($monthSales, 2) }}</h3>
                                            <small class="text-white opacity-75">سنة {{ $selectedYear }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 col-12">
                        <a href="{{ route('admin.invoices.index', ['type' => 'due']) }}" class="card crypto-card-3 bg-gradient-warning pull-up">
                            <div class="card-content">
                                <div class="card-body pb-1">
                                    <div class="row">
                                        <div class="col-4 text-center align-self-center"><div class="card-icon-bg"><i class="la la-money text-white font-large-2"></i></div></div>
                                        <div class="col-8 pl-2">
                                            <h5 class="text-white mb-1">إجمالي المستحقات</h5>
                                            <h3 class="text-white font-weight-bold">{{ number_format($totalDue, 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>


                <div class="row match-height">
                    <div class="col-xl-4 col-12">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title text-success">أعلى الصيداليات (شهر {{ $selectedMonth }})</h4></div>
                            <div class="card-content">
                                <div class="table-responsive">
                                    <table class="table table-de mb-0">
                                        <thead><tr><th>الصيدلية</th><th>المركز</th><th>الإجمالي</th></tr></thead>
                                        <tbody>
                                        @forelse($topPharmacists as $pharma)
                                            <tr><td>{{ $pharma->name }}</td><td class="text-muted">{{ $pharma->center->name ?? '-' }}</td><td class="text-success font-weight-bold">{{ number_format($pharma->total_sales) }}</td></tr>
                                        @empty <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات لهذا الشهر</td></tr> @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-12">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title text-info">أفضل الأطباء (شهر {{ $selectedMonth }})</h4></div>
                            <div class="card-content">
                                <div class="table-responsive">
                                    <table class="table table-de mb-0">
                                        <thead><tr><th>الطبيب</th><th>التخصص</th><th>المبيعات</th></tr></thead>
                                        <tbody>
                                        @forelse($topDoctors as $doc)
                                            <tr><td>{{ $doc->name }}</td><td class="text-muted">{{ $doc->speciality ?? '-' }}</td><td class="text-info font-weight-bold">{{ number_format($doc->total_sales) }}</td></tr>
                                        @empty <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات لهذا الشهر</td></tr> @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-12">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title text-warning">أفضل المناديب (شهر {{ $selectedMonth }})</h4></div>
                            <div class="card-content">
                                <div class="table-responsive">
                                    <table class="table table-de mb-0">
                                        <thead><tr><th>المندوب</th><th>المبيعات</th></tr></thead>
                                        <tbody>
                                        @forelse($topRepresentatives as $rep)
                                            <tr><td>{{ $rep->name }}</td><td class="text-warning font-weight-bold">{{ number_format($rep->total_sales) }}</td></tr>
                                        @empty <tr><td colspan="2" class="text-center text-muted">لا توجد بيانات لهذا الشهر</td></tr> @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('admin.includes.dashboard_tabs')
            </div>
        </div>
    </div>
@endsection
