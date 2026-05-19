<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kandang;
use App\Models\Suhu;
use App\Models\Device;
use App\Models\Deteksi;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $kandangIds = Kandang::where('user_id', $userId)->pluck('id');

        $avgTemperature = Suhu::whereIn('kandang_id', $kandangIds)
            ->whereDate('created_at', now()->today())
            ->avg('temperature')
            ??
            Suhu::whereIn('kandang_id', $kandangIds)
            ->avg('temperature')
            ??
            0;

        $totalDevicesCount = Device::whereIn('kandang_id', $kandangIds)->count();
        
        $totalServoCount = Device::whereIn('kandang_id', $kandangIds)
            ->where('device_type', 'SERVO')
            ->count();

        $onlineDevicesCount = Device::whereIn('kandang_id', $kandangIds)
            ->where('status', 'online')
            ->count();

        $totalKandangCount = Kandang::where('user_id', $userId)->count();

        $activeKandangCount = Kandang::where('user_id', $userId)
            ->whereHas('devices', function ($q) {
                $q->where('status', 'online');
            })
            ->count();

        $openDoorsCount = Device::whereIn('kandang_id', $kandangIds)
            ->where('device_type', 'SERVO')
            ->where('door_status', 'TERBUKA')
            ->count();

        $anyDoorOpen = $openDoorsCount > 0;

        $latestDetections = Deteksi::with(['kandang', 'device'])
            ->whereIn('kandang_id', $kandangIds)
            ->latest()
            ->limit(4)
            ->get();

        return view('public.dashboard', compact(
            'avgTemperature',
            'totalDevicesCount',
            'onlineDevicesCount',
            'totalKandangCount',
            'activeKandangCount',
            'openDoorsCount',
            'totalServoCount',
            'anyDoorOpen',
            'latestDetections'
        ));
    }

    public function notifikasi()
    {
        return view('Public.notifikasi');
    }
}
