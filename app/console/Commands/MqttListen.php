<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use App\Models\Suhu;
use App\Models\Kandang;
use App\Models\Ayam;
use App\Models\Device;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen MQTT';

    public function handle()
    {
        $mqtt = new MqttClient('127.0.0.1', 1883, 'laravel_subscriber');
        $mqtt->connect();

        echo "MQTT CONNECTED\n";

        $mqtt->subscribe('kandang/sensor', function ($topic, $message) {

            $data = json_decode($message, true);
            if (!$data) return;

            echo "RAW SENSOR: " . $message . "\n";

            $device = Device::where('device_id', $data['device_id'] ?? null)->first();
            if (!$device) return;

            // update status device (ESP32 / sensor apapun)
            $device->update([
                'connection_status' => 'online',
                'device_state' => $data['device_state'] ?? 'active',
                'health_status' => $data['health_status'] ?? 'EXCELLENT',
                'signal_strength' => $data['signal_strength'] ?? null,
                'last_updated' => now(),
            ]);

            echo "DEVICE UPDATED: " . $device->device_id . "\n";

            if ($device->status !== 'aktif') {
                echo "DEVICE NON-AKTIF -> SENSOR IGNORED\n";
                return;
            }

            if (isset($data['temperature'])) {
                Suhu::create([
                    'kandang_id' => $data['kandang_id'],
                    'device_id' => $device->id,
                    'temperature' => $data['temperature']
                ]);

                echo "SUHU SAVED\n";
            }
        }, 0);

        $mqtt->subscribe('kandang/ayam', function ($topic, $message) {

            $data = json_decode($message, true);
            if (!$data) return;

            $device = Device::where('device_id', $data['device_id'] ?? null)->first();
            if (!$device) return;

            if ($device->status !== 'aktif') {
                echo "DEVICE NON-AKTIF -> AYAM IGNORED\n";
                return;
            }

            $device->update([
                'last_updated' => now(),
                'connection_status' => 'online',
            ]);

            Ayam::create([
                'kandang_id' => $data['kandang_id'],
                'device_id' => $device->id,
                'direction' => $data['direction'],
                'source' => $data['source']
            ]);

            $kandang = Kandang::find($data['kandang_id']);

            if ($kandang) {
                if (trim($data['direction']) === 'IN') {
                    $kandang->current_chicken++;
                } elseif ($kandang->current_chicken > 0) {
                    $kandang->current_chicken--;
                }
                $kandang->save();
            }

            echo "AYAM SAVED\n";
        }, 0);

        $mqtt->subscribe('kandang/device/status', function ($topic, $message) {

            $data = json_decode($message, true);
            if (!$data) return;

            $device = Device::where('device_id', $data['device_id'] ?? null)->first();
            if (!$device) return;

            if ($device->status !== 'aktif') {
                echo "DEVICE NON-AKTIF -> HEARTBEAT IGNORED\n";
                return;
            }

            $device->update([
                'connection_status' => 'online',
                'signal_strength' => $data['signal_strength'] ?? null,
                'health_status' => $data['health_status'] ?? 'EXCELLENT',
                'device_state' => 'active',
                'last_updated' => now(),
            ]);

            echo "HEARTBEAT UPDATED\n";
        }, 0);

        $mqtt->subscribe('kandang/door/status', function ($topic, $message) {

            $data = json_decode($message, true);
            if (!$data) return;

            Device::where('device_id', $data['device_id'] ?? null)
                ->update([
                    'door_status' => $data['door_status'] ?? null,
                    'last_updated' => now(),
                ]);

            echo "DOOR UPDATED\n";
        }, 0);
    
        $mqtt->subscribe('kandang/light/status', function ($topic, $message) {

            $data = json_decode($message, true);
            if (!$data) return;

            Device::where('device_id', $data['device_id'] ?? null)
                ->update([
                    'light_status' => $data['light_status'] ?? null,
                    'last_updated' => now(),
                ]);

            echo "LIGHT UPDATED\n";
        }, 0);

        $mqtt->loop(true);
    }
}
