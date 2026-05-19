<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeviceSetting;
use App\Models\Device;
use App\Models\Kandang; 
use App\Services\MqttService;
use Carbon\Carbon;

class CheckDeviceSchedule extends Command
{
    protected $signature = 'schedule:device';
    protected $description = 'Check device timer and send command to specific chicken coop';

    public function handle()
    {
        $now = Carbon::now()->format('H:i');
        echo "CHECK TIME: " . $now . "\n";

        $settings = DeviceSetting::where('is_set', 1)
            ->where('auto_mode', 1)
            ->get();

        foreach ($settings as $setting) {
            echo "----------------------------------------\n";
            echo "SETTING DITEMUKAN UNTUK KANDANG ID: " . $setting->kandang_id . "\n";

            $openTime  = Carbon::parse($setting->timer_open)->format('H:i');
            $closeTime = Carbon::parse($setting->timer_close)->format('H:i');

            echo "OPEN TIME: " . $openTime . " | CLOSE TIME: " . $closeTime . "\n";

            $kandang = Kandang::find($setting->kandang_id);
            if (!$kandang || empty($kandang->code)) {
                echo "KANDANG ATAU KODE KANDANG TIDAK DITEMUKAN\n";
                continue;
            }

            $servo = Device::where('kandang_id', $setting->kandang_id)
                ->where('device_type', 'LIKE', 'SERVO%')
                ->first();

            if (!$servo) {
                echo "SERVO TIDAK DITEMUKAN\n";
                continue;
            }

            echo "SERVO DITEMUKAN: " . $servo->device_id . "\n";
            echo "STATUS SAAT INI DI DB: " . $servo->door_status . "\n";

            $topic = "kandang/" . trim($kandang->code) . "/servo";

            if ($now >= $openTime && $now < $closeTime && $servo->door_status == 'TERTUTUP') {
                
                MqttService::publish($topic, 'OPEN');
                echo "PERINTAH 'OPEN' DIKIRIM KE TOPIK: " . $topic . "\n";

            }

            if ($now >= $closeTime && $servo->door_status == 'TERBUKA') {
                
                MqttService::publish($topic, 'CLOSE');
                echo "PERINTAH 'CLOSE' DIKIRIM KE TOPIK: " . $topic . "\n";

            }
        }
    }
}