<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;
use App\Models\Suhu;
use App\Models\Kandang;
use App\Models\Ayam;
use App\Models\Device;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen MQTT with Auto-Reconnect and exact payload matching';

    public function handle()
    {
        $server = '127.0.0.1';
        $port = 1883;
        $clientId = 'laravel_subscriber';

        while (true) {
            try {
                $this->info("Mencoba menyambung ke Broker MQTT...");
                $mqtt = new MqttClient($server, $port, $clientId);
                $mqtt->connect();

                $this->info("MQTT CONNECTED - Mendengarkan data kandang...");

                $mqtt->subscribe('kandang/+/status', function ($topic, $message) {
                    $topicParts = explode('/', $topic);
                    $kandangCode = $topicParts[1] ?? null;

                    $status = trim(strtolower($message)); 

                    if ($kandangCode) {
                        $device_ids = [
                            $kandangCode . "-ESP32",
                            $kandangCode . "-DHT22",
                            $kandangCode . "-SERVO",
                            $kandangCode . "-LED",
                            $kandangCode . "-ULTRASONIC1",
                            $kandangCode . "-ULTRASONIC2"
                        ];

                        Device::whereIn('device_id', $device_ids)->update([
                            'connection_status' => $status,
                            'device_state' => ($status === 'online') ? 'active' : 'inactive',
                            'health_status' => ($status === 'online') ? 'EXCELLENT' : 'CRITICAL',
                            'last_updated' => now(),
                        ]);

                        echo "LAST WILL/STATUS UPDATED FOR KANDANG {$kandangCode}: {$status}\n";
                    }
                }, 1);

                $mqtt->subscribe('kandang/sensor', function ($topic, $message) {
                    $data = json_decode($message, true);
                    if (!$data) return;

                    echo "RAW SENSOR: " . $message . "\n";

                    $esp32Id = $data['esp32']['device_id'] ?? null;
                    $device = Device::where('device_id', $esp32Id)->first();
                    
                    if (!$device) return;

                    $device->update([
                        'connection_status' => 'online',
                        'device_state' => $data['esp32']['device_state'] ?? 'active',
                        'health_status' => $data['esp32']['health_status'] ?? 'EXCELLENT',
                        'signal_strength' => $data['esp32']['signal_strength'] ?? null,
                        'last_updated' => now(),
                    ]);
                    
                    if ($device->status !== 'aktif') return;

                    if (isset($data['temperature'])) {
                        $dhtCode = str_replace("-ESP32", "-DHT22", $esp32Id);
                        $dhtDevice = Device::where('device_id', $dhtCode)->first();

                        Suhu::create([
                            'kandang_id' => $device->kandang_id ?? 1,
                            'device_id' => $dhtDevice ? $dhtDevice->id : $device->id,
                            'temperature' => $data['temperature']
                        ]);
                        echo "SUHU SAVED\n";
                        echo "SIGNAL: " . ($data['esp32']['signal_strength'] ?? 'NULL') . "\n";
                    }
                }, 0);

                $mqtt->subscribe('kandang/ayam', function ($topic, $message) {
                    $data = json_decode($message, true);
                    if (!$data) return;

                    $device = Device::where('device_id', $data['device_id'] ?? null)->first();
                    if (!$device) return;

                    if ($device->status !== 'aktif') return;

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
                    echo "AYAM COUNT UPDATED ({$data['direction']})\n";
                }, 1);

                $mqtt->subscribe('kandang/device/status', function ($topic, $message) {
                    $data = json_decode($message, true);
                    if (!$data) return;

                    echo "COMPONENTS HEARTBEAT RECEIVED\n";
                    $components = ['esp32', 'dht22', 'servo', 'led', 'ultrasonic1', 'ultrasonic2'];

                    foreach ($components as $comp) {
                        if (isset($data[$comp])) {
                            $compData = $data[$comp];
                            
                            $updatePayload = [
                                'connection_status' => 'online',
                                'device_state'      => $compData['device_state'] ?? 'active',
                                'health_status'     => $compData['health_status'] ?? 'EXCELLENT',
                                'signal_strength'   => $compData['signal_strength'] ?? null,
                                'last_updated'      => now(),
                            ];

                            if ($comp === 'servo' && isset($compData['state'])) {
                                $updatePayload['door_status'] = ($compData['state'] == 'OPEN') ? 'TERBUKA' : 'TERTUTUP';
                            }
                            if ($comp === 'led' && isset($compData['state'])) {
                                $updatePayload['light_status'] = ($compData['state'] == 'ON') ? 'HIDUP' : 'MATI';
                            }

                            Device::where('device_id', $compData['device_id'])->update($updatePayload);
                        }
                    }
                }, 0);

                $mqtt->subscribe('kandang/door/status', function ($topic, $message) {
                    $data = json_decode($message, true);
                    if (!$data) return;

                    Device::where('device_id', $data['device_id'] ?? null)->update([
                        'door_status' => $data['door_status'] ?? null,
                        'connection_status' => 'online',
                        'last_updated' => now(),
                    ]);
                    echo "CONFIRMED: DOOR STATUS UPDATED IN DATABASE\n";
                }, 1);
            
                $mqtt->subscribe('kandang/light/status', function ($topic, $message) {
                    $data = json_decode($message, true);
                    if (!$data) return;

                    Device::where('device_id', $data['device_id'] ?? null)->update([
                        'light_status' => $data['light_status'] ?? null,
                        'connection_status' => 'online',
                        'last_updated' => now(),
                    ]);
                    echo "CONFIRMED: LIGHT STATUS UPDATED IN DATABASE\n";
                }, 1);

                $mqtt->loop(true);

            } catch (MqttClientException | \Exception $e) {
                $this->error("Koneksi Error/Terputus: " . $e->getMessage());
                $this->info("Menunggu 5 detik sebelum menyambung ulang...");
                sleep(5);
            }
        }
    }
}