<?php

use App\Http\Controllers\Admin\CenterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\DrugController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PharmacistController;
use App\Http\Controllers\Admin\ProvinceController;
use App\Http\Controllers\Admin\RepresentativeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DoctorDealController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\ZoneExpenseController;
use App\Http\Controllers\Admin\ZoneReportController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\me;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('me',[me::class,'index'])->name('me');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::resource('drugs', DrugController::class);
    Route::resource('provinces', ProvinceController::class);
    Route::resource('centers', CenterController::class);
    Route::resource('doctors', DoctorController::class);
    Route::resource('pharmacists', PharmacistController::class);
    Route::resource('representatives', RepresentativeController::class);
    Route::resource('zones', ZoneController::class);
    Route::get('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'printPdf'])->name('invoices.pdf');
    Route::get('/dashboard/line/{id}', [DashboardController::class, 'lineDashboard'])->name('dashboard.line');


    Route::get('zones/{id}/expenses/create', [ZoneExpenseController::class, 'create'])->name('zones.expenses.create');
    Route::post('zones/{id}/expenses', [ZoneExpenseController::class, 'store'])->name('zones.expenses.store');
    Route::delete('expenses/{id}', [ZoneExpenseController::class, 'destroy'])->name('zones.expenses.destroy');

    Route::resource('deals', DoctorDealController::class);
    Route::post('deals/{deal}/pay', [DoctorDealController::class, 'markAsPaid'])->name('deals.pay');
    Route::get('deals/{deal}/invoices', [DoctorDealController::class, 'showInvoices'])->name('deals.invoices');
    Route::post('deals/{deal}/toggle-active', [DoctorDealController::class, 'toggleActive'])->name('deals.toggleActive');
    Route::post('deals/{deal}/toggle-archive', [DoctorDealController::class, 'toggleArchive'])->name('deals.toggleArchive');
    Route::get('admin/deals/archived', [DoctorDealController::class, 'archived'])->name('deals.archived');

    Route::resource('warehouses', WarehouseController::class);
    Route::prefix('warehouses/{warehouse}/stock')->name('warehouses.stock.')->group(function () {

        Route::get('add', [WarehouseController::class, 'addStock'])->name('add');
        Route::post('add', [WarehouseController::class, 'storeStock'])->name('store');

        Route::get('return', [WarehouseController::class, 'returnStock'])->name('return');
        Route::post('return', [WarehouseController::class, 'processReturnStock'])->name('return.process');
    });

    Route::prefix('reports')->name('reports.')->group(function () {

        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/province/{id}', [ReportController::class, 'showProvince'])->name('province');
        Route::get('/center/{id}', [ReportController::class, 'showCenter'])->name('center');
        Route::get('/pharmacist/{id}', [ReportController::class, 'showPharmacist'])->name('pharmacist');


        Route::prefix('representatives')->name('representatives.')->group(function () {
            Route::get('/', [ReportController::class, 'representativesIndex'])->name('index');
            Route::get('/{id}', [ReportController::class, 'showRepresentative'])->name('show');
        });
        Route::get('doctors-balance', [App\Http\Controllers\Admin\DoctorBalanceController::class, 'index'])->name('doctors_balance');
        Route::prefix('doctors')->name('doctors.')->group(function () {
            Route::get('/', [ReportController::class, 'doctorsIndex'])->name('index');
            Route::get('/province/{id}', [ReportController::class, 'showDoctorProvince'])->name('province');
            Route::get('/center/{id}', [ReportController::class, 'showDoctorCenter'])->name('center');
            Route::get('/doctor/{id}', [ReportController::class, 'showDoctor'])->name('show');
            Route::post('/doctor/{id}/pay', [ReportController::class, 'payDoctorCommission'])->name('pay');
        });

        Route::prefix('zone-risk')->name('zone_risk.')->group(function () {
            Route::get('/', [ZoneReportController::class, 'index'])->name('index');
            Route::get('/export', [ZoneReportController::class, 'export'])->name('export');
            Route::get('/{id}', [ZoneReportController::class, 'show'])->name('show');
        });

        Route::get('zone-risk-shortcut', [ZoneReportController::class, 'index'])->name('zone_risk');
    });
});

Route::middleware(['auth', 'role:accountant'])->prefix('accountant')->name('accountant.')->group(function () {


});

require __DIR__ . '/auth.php';
