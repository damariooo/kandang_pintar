<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;

class MqttService
{
    public static function publish($topic, $message)
    {
        $server = '17081fa556494a258c016aeecad9a3c0.s1.eu.hivemq.cloud';
        $port = 8883;

        $clientId = 'laravel_' . uniqid();

        $mqtt = new MqttClient($server, $port, $clientId);

        $mqtt->connect();

        $mqtt->publish($topic, $message);

        $mqtt->disconnect();
    }
}