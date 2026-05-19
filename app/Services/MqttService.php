<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    public static function publish($topic, $message)
    {
        $server = '17081fa556494a258c016aeecad9a3c0.s1.eu.hivemq.cloud';
        $port = 8883;

        $clientId = 'laravel_' . uniqid();

        $mqtt = new MqttClient($server, $port, $clientId);

        // 2. TAMBAHKAN CONFIG ENKRIPSI DAN LOGIN HIVEMQ DI SINI
        $settings = (new ConnectionSettings)
            ->setUseTls(true)                  // Mengaktifkan TLS (karena pakai port 8883)
            ->setTlsSelfSignedAllowed(true)    // Mengizinkan self-signed certificate
            ->setTlsVerifyPeer(false)          // Mem-bypass verifikasi ketat (mirip setInsecure di ESP32)
            ->setUsername('ayokawan')          // Username HiveMQ kamu
            ->setPassword('Ayokawan123');      // Password HiveMQ kamu

        // 3. MASUKKAN SETTINGAN KE DALAM METHOD CONNECT
        $mqtt->connect($settings);

        $mqtt->publish($topic, $message);

        $mqtt->disconnect();
    }
}