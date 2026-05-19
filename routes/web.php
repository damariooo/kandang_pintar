<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\AuthController;
use App\Http\Controllers\Public\ProfileController;
use App\Http\Controllers\Public\DashboardController;
use App\Http\Controllers\Public\DeviceController;
use App\Http\Controllers\Public\LaporanController;
use App\Http\Controllers\Public\MonitoringController;
use App\Http\Controllers\Public\ActivityLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Services\MqttService;
use App\Models\Device;
use Illuminate\Http\Request;
// use App\Models\Command;
// use Illuminate\Http\Request;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/api/provinsi', [ProfileController::class, 'getProvinsi']);
    Route::get('/api/kota/{provinsi_id}', [ProfileController::class, 'getKota']);
    Route::get('/api/kecamatan/{kota_id}', [ProfileController::class, 'getKecamatan']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/create', [MonitoringController::class, 'create'])->name('monitoring.create');
    Route::get('/monitoring/{id}/edit', [MonitoringController::class, 'edit'])->name('monitoring.edit');
    Route::post('/monitoring/store', [MonitoringController::class, 'store'])->name('monitoring.store');
    Route::put('/monitoring/update/{id}', [MonitoringController::class, 'update'])->name('monitoring.update');
    Route::delete('/monitoring/destroy/{id}', [MonitoringController::class, 'destroy'])->name('monitoring.destroy');
    Route::put('/monitoring/settings/{kandang_id}', [MonitoringController::class, 'updateSettings'])->name('settings.update');
    // Route::post('/commands', [MonitoringController::class, 'storeCommand'])->name('commands.store');
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/create', [DeviceController::class, 'create'])->name('devices.create');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::get('/devices/{id}/edit', [DeviceController::class, 'edit'])->name('devices.edit');
    Route::put('/devices/{id}', [DeviceController::class, 'update'])->name('devices.update');
    Route::delete('/devices/{id}', [DeviceController::class, 'destroy'])->name('devices.destroy');
    Route::get('/activity_log', [ActivityLogController::class, 'index'])->name('activity_log');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    Route::put('/settings/{kandang_id}', [MonitoringController::class, 'update'])->name('settings.update');
    Route::get('/notifikasi', [DashboardController::class, 'notifikasi'])->name('notifikasi');
    Route::post('/notif-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notif.read')->middleware('auth');

    // Route::post('/commands', function (Request $request) {
    //     $request->validate([
    //         'device_id' => 'required',
    //         'command' => 'required'
    //     ]);

    //     Command::create([
    //         'device_id'    => $request->device_id,
    //         'command_type' => $request->command,
    //         'status'       => 'pending'
    //     ]);

    //     return back()->with('success', 'Perintah berhasil dikirim ke perangkat.');
    // })->name('commands.store');

    Route::post('/servo/open', function (Request $request) {

        MqttService::publish('kandang/servo', 'OPEN');

        Device::where('device_id', $request->device_id)
            ->update([
                'door_status' => 'TERBUKA'
            ]);

        return back()->with('success', 'Pintu dibuka');
    })->name('servo.open');

    Route::post('/servo/close', function (Request $request) {

        MqttService::publish('kandang/servo', 'CLOSE');

        Device::where('device_id', $request->device_id)
            ->update([
                'door_status' => 'TERTUTUP'
            ]);

        return back()->with('success', 'Pintu ditutup');
    })->name('servo.close');

    Route::post('/lamp/on', function (Request $request) {

        MqttService::publish('kandang/lamp', 'ON');

        Device::where('device_id', $request->device_id)
            ->update([
                'light_status' => 'HIDUP'
            ]);

        return back()->with('success', 'Lampu dinyalakan');
    })->name('lamp.on');

    Route::post('/lamp/off', function (Request $request) {

        MqttService::publish('kandang/lamp', 'OFF');

        Device::where('device_id', $request->device_id)
            ->update([
                'light_status' => 'MATI'
            ]);

        return back()->with('success', 'Lampu dimatikan');
    })->name('lamp.off');

    Route::get('/api/kandang', function () {
        return \App\Models\Kandang::select('id', 'current_chicken')->get();
    });
});

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->name('logout')
        ->middleware('auth:admin');

    Route::middleware(['auth:admin'])->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
        Route::resource('/user', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('/device', \App\Http\Controllers\Admin\DeviceController::class);
        Route::resource('/kandang', \App\Http\Controllers\Admin\KandangController::class);

        Route::middleware(['superadmin'])->group(function () {
            Route::resource('/admin', AdminController::class);
        });
    });
});
