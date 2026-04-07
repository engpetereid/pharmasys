<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow sidebar" data-scroll-to-active="true">
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

            <li class="navigation-header">
                <span data-i18n="nav.category.dashboard">لوحات القيادة</span>
                <i class="la la-ellipsis-h ft-minus" data-toggle="tooltip" data-placement="right"
                   data-original-title="Dashboards"></i>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="la la-home"></i>
                    <span class="menu-title">الرئيسية (عام)</span>
                </a>
            </li>

            <li class="nav-item {{ request()->is('admin/dashboard/line/1') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard.line', 1) }}">
                    <i class="la la-bar-chart" style="color: #1E9FF2;"></i>
                    <span class="menu-title">إحصائيات Line 1</span>
                </a>
            </li>

            <li class="nav-item {{ request()->is('admin/dashboard/line/2') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard.line', 2) }}">
                    <i class="la la-bar-chart" style="color: #FF9149;"></i>
                    <span class="menu-title">إحصائيات Line 2</span>
                </a>
            </li>

            {{-- العمليات والمخزون  --}}
            <li class="navigation-header">
                <span data-i18n="nav.category.operations">العمليات والمخزون</span>
                <i class="la la-ellipsis-h ft-minus"></i>
            </li>

            {{-- الفواتير --}}
            <li class="nav-item has-sub {{ request()->routeIs('admin.invoices.*') ? 'open' : '' }}">
                <a href="#">
                    <i class="la la-file-text"></i>
                    <span class="menu-title">فواتير المبيعات</span>
                </a>
                <ul class="menu-content">
                    <li class="{{ request()->routeIs('admin.invoices.index') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.invoices.index') }}">سجل الفواتير</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.invoices.create') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.invoices.create') }}">إضافة فاتورة</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item has-sub {{ request()->routeIs('admin.deals.*') ? 'open' : '' }}">
                <a href="#">
                    <i class="la la-google"></i>
                    <span class="menu-title">اتفاقات الأطباء</span>
                </a>
                <ul class="menu-content">
                    <li class="{{ request()->routeIs('admin.deals.index') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.deals.index') }}">متابعة التارجت</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.deals.create') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.deals.create') }}">اتفاق جديد</a>
                    </li>
                </ul>
            </li>




            {{-- التقارير --}}
            <li class="navigation-header">
                <span data-i18n="nav.category.reports">التقارير والتحليلات</span>
                <i class="la la-ellipsis-h ft-minus"></i>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.reports.zone_risk.index') ? 'active' : '' }}">
                <a href="{{ route('admin.reports.zone_risk.index') }}">
                    <i class="la la-pie-chart"></i>
                    <span class="menu-title">تقرير نسبة الجهاز</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.reports.monthly_financials') ? 'active' : '' }}">
                <a href="{{ route('admin.monthly_financials') }}">
                    <i class="la la-pie-chart"></i>
                    <span class="menu-title">الملخص المالى</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.reports.doctors_balance') ? 'active' : '' }}">
                <a href="{{ route('admin.reports.doctors_balance') }}">
                    <i class="la la-balance-scale"></i>
                    <span class="menu-title">كشف حساب الأطباء</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.zones.*') ? 'active' : '' }}">
                <a href="{{ route('admin.zones.index') }}">
                    <i class="la la-map-marker"></i>
                    <span class="menu-title">المناطق والمناديب</span>
                </a>
            </li>

            <li class="nav-item has-sub {{ request()->is('admin/reports/*') && !request()->routeIs('admin.reports.zone_risk.*') && !request()->routeIs('admin.reports.doctors_balance') ? 'open' : '' }}">
                <a href="#"><i class="la la-line-chart"></i><span class="menu-title">تقارير الأداء</span></a>
                <ul class="menu-content">
                    <li class="{{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.reports.index') }}">أداء الصيدليات</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.reports.doctors.index') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.reports.doctors.index') }}">أداء الأطباء</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.reports.representatives.index') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.reports.representatives.index') }}">أداء المناديب</a>
                    </li>
                </ul>
            </li>

            {{-- ================= البيانات الأساسية ================= --}}
            <li class="navigation-header">
                <span data-i18n="nav.category.settings">البيانات الأساسية</span>
                <i class="la la-ellipsis-h ft-minus"></i>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.doctors.index') || request()->routeIs('admin.doctors.create') || request()->routeIs('admin.doctors.edit') || request()->routeIs('admin.doctors.show') ? 'active' : '' }}">
                <a href="{{ route('admin.doctors.index') }}"><i class="la la-user-md"></i><span class="menu-title">الأطباء</span></a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.pharmacists.*') ? 'active' : '' }}">
                <a href="{{ route('admin.pharmacists.index') }}"><i class="la la-hospital-o"></i><span
                        class="menu-title">الصيدليات</span></a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.representatives.index') || request()->routeIs('admin.representatives.create') || request()->routeIs('admin.representatives.edit') || request()->routeIs('admin.representatives.show') ? 'active' : '' }}">
                <a href="{{ route('admin.representatives.index') }}"><i class="la la-briefcase"></i><span
                        class="menu-title">المناديب</span></a>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('admin.drugs.*') || request()->routeIs('admin.provinces.*') || request()->routeIs('admin.centers.*') ? 'open' : '' }}">
                <a href="#"><i class="la la-cogs"></i><span class="menu-title">إعدادات النظام</span></a>
                <ul class="menu-content">

                    <li class="{{ request()->routeIs('admin.warehouses.index') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.warehouses.index') }}">جرد المخازن</a>
                    </li>


                    <li class="{{ request()->routeIs('admin.drugs.*') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.drugs.index') }}">الأدوية</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.provinces.*') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.provinces.index') }}">المحافظات</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.centers.*') ? 'active' : '' }}">
                        <a class="menu-item" href="{{ route('admin.centers.index') }}">المراكز</a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</div>
