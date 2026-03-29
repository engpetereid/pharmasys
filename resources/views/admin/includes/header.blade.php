<nav
    class="bg-white header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-semi-light navbar-shadow main-header">
    <div class="navbar-wrapper">
        <div class="navbar-header">
            <ul class="flex-row nav navbar-nav">

                <li class="mr-auto nav-item mobile-menu d-md-none">
                    <a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#">
                        <i class="ft-menu font-large-1"></i>
                    </a>
                </li>

                <li class="mr-auto nav-item">
                    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                        <img src="{{ asset('assets/admin/images/logo.jpg') }}" alt="Logo" style="height: 40px; margin-left: 5px; ">
                        <h3 class="ml-1 brand-text d-inline-block"
                            style="vertical-align: middle; font-weight: bold; color: #19502c;">Bio Vera</h3>
                    </a>
                </li>

                <li class="nav-item d-md-none">
                    <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile">
                        <i class="la la-ellipsis-v"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="navbar-container content">
            <div class="collapse navbar-collapse" id="navbar-mobile">

                <ul class="float-left mr-auto nav">
                    <li class="nav-item d-none d-md-block">
                        <a class="nav-link nav-link-expand" href="#" title="ملء الشاشة">
                            <i class="ficon ft-maximize"></i>
                        </a>
                    </li>
                </ul>

                <ul class="float-right nav navbar-nav">


                    <li class="mr-1 dropdown nav-item">
                        <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown" style="display: flex; align-items: center;">
                            <i class="ficon ft-zap text-warning"></i>

                            <span class="ml-1 text-bold-700" style="display: inline-block !important; color: #6b6f82;">أدوات سريعة</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{route('admin.pharmacists.create')}}">
                                <i class="la la-plus-square text-success"></i> إضافة صيدلية
                            </a>
                            <a class="dropdown-item" href="{{route('admin.doctors.create')}}">
                                <i class="la la-user-md text-info"></i> إضافة طبيب
                            </a>
                            <a class="dropdown-item" href="{{route('admin.drugs.create')}}">
                                <i class="la la-flask text-danger"></i> إضافة دواء
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{route('admin.deals.create')}}">
                                <i class="la la-google text-primary"></i> انشاء اتفاقية
                            </a>
                            <a class="dropdown-item" href="{{route('admin.invoices.create')}}">
                                <i class="la la-file text-primary"></i> انشاء فاتورة
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{route('admin.representatives.create')}}">
                                <i class="la la-briefcase text-primary"></i> إضافة مندوب
                            </a>

                            <a class="dropdown-item" href="{{route('admin.warehouses.index')}}">
                                <i class="la la-home text-primary"></i> المخازن
                            </a>
                        </div>
                    </li>


                    <li class="dropdown dropdown-user nav-item">
                        <a class="dropdown-toggle nav-link dropdown-user-link" href="" data-toggle="dropdown" style="display: flex; align-items: center;">

                            <span class="mr-1 user-name text-bold-600" style="display: inline-block !important; color: #6b6f82;">
                                {{ auth()->user()->name }} /مرحباً د
                            </span>
                            <span class="avatar avatar-online">
                                <span class="text-center text-white avatar-content bg-primary rounded-circle"
                                      style="width: 35px; height: 35px; display: inline-block; line-height: 35px; font-weight: bold;">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <i></i>
                            </span>
                            </a>

                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="{{route('profile.edit')}}" class="text-center dropdown-item">
                            <span class="p-1 avatar-content bg-light rounded-circle">
                                <i class="la la-user font-medium-4"></i>
                            </span>
                                <p class="mt-1 mb-0 text-bold-600">{{ auth()->user()->name }}</p>
                                <small class="text-muted">مسؤول النظام</small>
                            </a>
                            <a class="dropdown-item text-danger" href="#"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="ft-power"></i> تسجيل الخروج
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
