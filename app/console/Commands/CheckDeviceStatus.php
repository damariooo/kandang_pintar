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

                $device->update([
                    'connection_status' => 'offline',
                    'health_status' => 'CRITICAL'
                ]);
            }
        }

        $this->info('Device status checked');
    }
}