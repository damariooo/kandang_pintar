<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use Carbon\Carbon;

class CheckDeviceStatus extends Command
{
    protected $signature = 'devices:check-status';
    protected $description = 'Check device online/offline status based on heartbeat interval';

    public function handle()
    {
        $devices = Device::all();

        foreach ($devices as $device) {

            if (!$device->last_updated) {
                $device->update([
                    'connection_status' => 'offline',
                    'device_state' => 'error', 
                    'health_status' => 'CRITICAL',
                    'signal_strength' => 0,
                ]);
                continue;
            }

            $minutes = now()->diffInMinutes($device->last_updated);

            if ($minutes >= 2) {
                $device->update([
                    'connection_status' => 'offline',
                    'device_state' => 'error',
                    'health_status' => 'CRITICAL',
                    'signal_strength' => 0,
                ]);
            } else {
                $device->update([
                    'connection_status' => 'online',
                    'device_state' => 'active',
                    'health_status' => 'EXCELLENT',
                ]);
            }
        }

        return 0;
    }
}
