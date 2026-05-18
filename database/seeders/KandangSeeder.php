<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kandang;
use App\Models\Device;

class KandangSeeder extends Seeder
{
    public function run(): void
    {
        $user1 = User::first();
        $user2 = User::skip(1)->first();

        if (!$user1) {
            $this->command->info('User belum ada, jalankan UserSeeder dulu!');
            return;
        }

        $kandang1 = Kandang::create([
            'user_id' => $user1->id,
            'name' => 'Kandang 1',
            'code' => 'KDG001',
            'capacity' => 100,
            'timer_open' => '06:00:00',
            'timer_close' => '18:00:00',
        ]);

        $devices1 = [
            [
                'device_id' => 'KDG001-ESP32',
                'device_name' => 'ESP32 Controller K1',
                'device_type' => 'gateway',
                'component_type' => 'esp32',
            ],
            [
                'device_id' => 'KDG001-DHT22',
                'device_name' => 'Sensor Suhu K1',
                'device_type' => 'sensor',
                'component_type' => 'dht22',
            ],
            [
                'device_id' => 'KDG001-SERVO',
                'device_name' => 'Servo Pintu K1',
                'device_type' => 'actuator',
                'component_type' => 'servo',
                'door_status' => 'TERTUTUP',
            ],
            [
                'device_id' => 'KDG001-LED',
                'device_name' => 'Lampu K1',
                'device_type' => 'actuator',
                'component_type' => 'led',
                'light_status' => 'MATI',
            ],
            [
                'device_id' => 'KDG001-ULTRASONIC1',
                'device_name' => 'Ultrasonic 1 K1',
                'device_type' => 'sensor',
                'component_type' => 'ultrasonic',
            ],
            [
                'device_id' => 'KDG001-ULTRASONIC2',
                'device_name' => 'Ultrasonic 2 K1',
                'device_type' => 'sensor',
                'component_type' => 'ultrasonic',
            ],
        ];

        foreach ($devices1 as $device) {
            Device::create([
                'kandang_id' => $kandang1->id,
                'device_id' => $device['device_id'],
                'device_name' => $device['device_name'],
                'device_type' => $device['device_type'],
                'component_type' => $device['component_type'],
                'profile_image' => null,
                'status' => 'non-aktif',
                'connection_status' => 'offline',
                'device_state' => 'maintenance',
                'door_status' => $device['door_status'] ?? null,
                'light_status' => $device['light_status'] ?? null,
                'health_status' => 'MAINTENANCE',
                'signal_strength' => null,
                'installation_date' => now(),
                'last_updated' => now(),
            ]);
        }

        $kandang2 = Kandang::create([
            'user_id' => $user2 ? $user2->id : $user1->id,
            'name' => 'Kandang 2',
            'code' => 'KDG002',
            'capacity' => 120,
            'timer_open' => '06:00:00',
            'timer_close' => '18:00:00',
        ]);

        foreach ($devices1 as $device) {
            Device::create([
                'kandang_id' => $kandang2->id,
                'device_id' => str_replace('KDG001', 'KDG002', $device['device_id']),
                'device_name' => str_replace('K1', 'K2', $device['device_name']),
                'device_type' => $device['device_type'],
                'component_type' => $device['component_type'],
                'profile_image' => null,
                'status' => 'non-aktif',
                'connection_status' => 'offline',
                'device_state' => 'maintenance',
                'door_status' => $device['door_status'] ?? null,
                'light_status' => $device['light_status'] ?? null,
                'health_status' => 'MAINTENANCE',
                'signal_strength' => null,
                'installation_date' => now(),
                'last_updated' => now(),
            ]);
        }
    }
}