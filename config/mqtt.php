<?php

return [
    'host'     => env('MQTT_HOST'),
    'port'     => env('MQTT_PORT', 8883),
    'user'     => env('MQTT_USER'), 
    'password' => env('MQTT_PASSWORD'),
];