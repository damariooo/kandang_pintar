<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\MqttService;
use App\Models\Device;
use App\Models\Kandang;

Route::get('/kandang', function () {

    $kandangs = Kandang::with('devices')
        ->get();

    return response()->json(

        $kandangs->map(function ($k) {

            $servo = $k->devices
                ->where('device_type', 'SERVO')
                ->first();

            $lamp = $k->devices
                ->where('device_type', 'LED')
                ->first();

            return [
                'id' => $k->id,
                'name' => $k->name,
                'current_chicken' => $k->current_chicken,

                'servo_status' => $servo->device_state ?? 'inactive',
                'light_status' => $lamp->device_state ?? 'inactive',
            ];
        })

    );
});

Route::post('/device/control', function (Request $request) {

    $request->validate([
        'device_id' => 'required|string',
        'type'      => 'required|string',
        'action'    => 'required|string',
    ]);

    $device = Device::with('kandang')
        ->where('device_id', $request->device_id)
        ->first();

    if (!$device) {
        return response()->json([
            'status' => false,
            'message' => 'Device tidak ditemukan'
        ], 404);
    }

    $type = strtoupper($request->type);
    $action = strtoupper($request->action);

    // FIX TOPIC
    if ($type == 'SERVO') {

        $topic = "kandang/{$device->kandang->code}/servo";
    } elseif ($type == 'LED') {

        $topic = "kandang/{$device->kandang->code}/led";
    } else {

        return response()->json([
            'status' => false,
            'message' => 'Type device tidak valid'
        ], 400);
    }

    MqttService::publish($topic, $action);

    return response()->json([
        'status' => true,
        'message' => 'Command dikirim ke device',
        'topic' => $topic,
        'data' => $action
    ]);
});
