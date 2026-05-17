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
        $mqtt = new MqttClient(
            '127.0.0.1',
            1883,
            'laravel_subscriber'
        );

        $mqtt->connect();

        echo "MQTT CONNECTED\n";

        $mqtt->subscribe('kandang/sensor', function ($topic, $message) {

            echo "\n=== DATA SUHU ===\n";
            echo $message . "\n";

            $data = json_decode($message, true);

            try {

                Suhu::create([
                    'kandang_id' => $data['kandang_id'],
                    'device_id' => $data['device_id'],
                    'temperature' => $data['temperature']
                ]);

                echo "BERHASIL INSERT SUHU\n";
            } catch (\Exception $e) {

                echo "ERROR SUHU:\n";
                echo $e->getMessage() . "\n";
            }
        }, 0);

        $mqtt->subscribe('kandang/ayam', function ($topic, $message) {

            echo "\n=== DATA AYAM ===\n";
            echo $message . "\n";

            $data = json_decode($message, true);

            try {

                Ayam::create([
                    'kandang_id' => $data['kandang_id'],
                    'device_id' => $data['device_id'],
                    'direction' => $data['direction'],
                    'source' => $data['source']
                ]);

                $kandang = Kandang::find($data['kandang_id']);

                if ($kandang) {

                    echo "KANDANG DITEMUKAN\n";

                    echo "CURRENT CHICKEN SEBELUM: ";
                    echo $kandang->current_chicken . "\n";

                    echo "DIRECTION:";
                    var_dump($data['direction']);

                    if (trim($data['direction']) === 'IN') {

                        $kandang->current_chicken =
                            ($kandang->current_chicken ?? 0) + 1;

                        $kandang->save();

                        echo "AYAM BERTAMBAH\n";
                    } elseif (trim($data['direction']) === 'OUT') {

                        if (($kandang->current_chicken ?? 0) > 0) {

                            $kandang->current_chicken =
                                $kandang->current_chicken - 1;

                            $kandang->save();

                            echo "AYAM BERKURANG\n";
                        }
                    }
                    echo "CURRENT CHICKEN SESUDAH: ";
                    echo $kandang->fresh()->current_chicken . "\n";
                } else {

                    echo "KANDANG TIDAK DITEMUKAN\n";
                }

                echo "BERHASIL INSERT AYAM\n";
            } catch (\Exception $e) {

                echo "ERROR AYAM:\n";
                echo $e->getMessage() . "\n";
            }
        }, 0);

        $mqtt->subscribe('kandang/device/status', function ($topic, $message) {

            echo "\n=== DEVICE STATUS ===\n";
            echo $message . "\n";

            $data = json_decode($message, true);

            try {

                $device = Device::where('device_id', $data['device_id'])->first();

                if ($device) {
                    $device->update([
                        'connection_status' => $data['connection_status'],
                        'signal_strength' => $data['signal_strength'],
                        'health_status' => $data['health_status'],
                        'last_updated' => now(),
                    ]);

                    echo "DEVICE UPDATED\n";
                }
            } catch (\Exception $e) {

                echo "ERROR DEVICE STATUS:\n";
                echo $e->getMessage() . "\n";
            }
        }, 0);
        $mqtt->loop(true);
    }
}
