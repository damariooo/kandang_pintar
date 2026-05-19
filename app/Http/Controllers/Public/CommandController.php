<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Command;
use App\Models\ActivityLog;
use App\Models\Device;
use App\Services\MqttService; // <-- 1. WAJIB IMPORT SERVICE MQTT YANG SUDAH KITA BUAT

class CommandController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Mengambil data device berdasarkan device_id yang dikirim dari form dashboard
            $device = Device::where('device_id', $request->device_id)->firstOrFail();

            // 2. JALANKAN PERINTAH MQTT REAL-TIME KE HIVEMQ CLOUD
            // Topik dibuat dinamis, contoh: kandang/LAMP_1/command atau kandang/SERVO_1/command
            $topic = 'kandang/' . $device->device_id . '/command';
            
            // Message berisi perintah teks bersih dari form, contoh: 'LIGHT_ON', 'OPEN_DOOR'
            $message = $request->command; 

            // Eksekusi pengiriman data ke broker HiveMQ
            MqttService::publish($topic, $message);

            // 3. SELESAI KIRIM MQTT, LANJUTKAN SIMPAN LOG KE DATABASE
            Command::create([
                'device_id'    => $device->device_id, 
                'command_type' => $request->command, 
                'status'       => 'pending'
            ]);

            ActivityLog::create([
                'kandang_id'  => $device->kandang_id,
                'device_id'   => $device->device_id, 
                'category'    => 'device',
                'action'      => $request->command,
                'status'      => 'success',
                'description' => 'Perintah ' . $request->command . ' dikirim ke ' . $device->device_name
            ]);

            // Mengembalikan user ke halaman dashboard asal tanpa merubah tampilan menjadi JSON
            return back()->with('success', 'Perintah berhasil dikirim ke MQTT dan Database');

        } catch (\Exception $e) {
            // Jika ada gangguan koneksi ke HiveMQ atau database, eror ditangkap di sini
            return back()->with('error', 'Gagal mengirim perintah: ' . $e->getMessage());
        }
    }
}