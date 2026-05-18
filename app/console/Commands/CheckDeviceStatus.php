<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use Carbon\Carbon;

class CheckDeviceStatus extends Command
{
    protected $signature = 'devices:check-status';
    protected $description = 'Check device online/offline';

    public function handle()
    {
        $devices = Device::all();

        foreach ($devices as $device) {

            if (!$device->last_updated) {
                continue;
            }

            $minutes = Carbon::parse($device->last_updated)
                ->diffInMinutes(now());

            if ($minutes >= 2) {

                if ($device->connection_status !== 'offline') {

                    $device->update([
                        'connection_status' => 'offline',
                        'device_state' => 'inactive',
                        'health_status' => 'CRITICAL',
                        'signal_strength' => $data['signal_strength'] ?? null,
                        'last_updated' => now(),
                    ]);

                    $this->info("{$device->device_id} -> OFFLINE ({$minutes} min)");
                }
            } else {

                $this->info("{$device->device_id} -> STILL ACTIVE");
            }
        }

        $this->info('Device check completed');
    }
}
