<?php

use App\Http\Controllers\ReportController;
use App\Livewire\Portal\DashboardComponent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/portal', DashboardComponent::class)->name('portal.dashboard');

    Route::get('/reports/krs/{krs}', [ReportController::class, 'cetakKrs'])->name('reports.krs');
    Route::get('/reports/khs/{mahasiswa}/{semester}', [ReportController::class, 'cetakKhs'])->name('reports.khs');
    Route::get('/reports/transkrip/{mahasiswa}', [ReportController::class, 'cetakTranskrip'])->name('reports.transkrip');
});
