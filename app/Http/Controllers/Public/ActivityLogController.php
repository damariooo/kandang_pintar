<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DeviceSetting;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $systemLogs = DB::table('activity_logs')
            ->select(
                'created_at',
                DB::raw("'system' as category"),
                'action',
                'description',
                'status'
            );

        $detectionLogs = DB::table('deteksis')
            ->select(
                'created_at',
                DB::raw("'detection' as category"),

                DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(objects, '$[0].label')), 'tidak_ada') as action"),

                DB::raw("CONCAT(
            'Terdeteksi objek ',
            COALESCE(JSON_UNQUOTE(JSON_EXTRACT(objects, '$[0].label')), 'tidak_ada'),
            ' (Akurasi: ',
            ROUND(confidence * 100, 0),
            '%)'
        ) as description"),

                DB::raw("IF(is_valid = 1, 'success', 'warning') as status")
            );

        $movementLogs = DB::table('ayams')
            ->select(
                'created_at',
                DB::raw("'movement' as category"),
                DB::raw("direction as action"),
                DB::raw("CONCAT('Ayam terdeteksi ', direction, ' via ', source) as description"),
                DB::raw("IF(direction = 'IN', 'info', 'warning') as status")
            );

        $combinedQuery = $systemLogs->union($detectionLogs)->union($movementLogs);

        $query = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined_logs"))
            ->mergeBindings($combinedQuery);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                    ->orWhere('action', 'like', '%' . $request->search . '%');
            });
        }

        $logsData = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        $logs = $logsData->getCollection()->map(function ($item) {
            $item->created_at = Carbon::parse($item->created_at);
            return $item;
        });
        $logsData->setCollection($logs);

        return view('public.activity_log', ['logs' => $logsData]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'device_id' => 'required|string',
            'action' => 'required|string',
            'source' => 'required|in:manual,worker,api',
            'notes' => 'nullable|string'
        ]);

        $log = ActivityLog::create($data);

        return response()->json([
            'success' => true,
            'log' => $log
        ]);
    }

    public function runDoorTimers()
    {
        $settings = DeviceSetting::where('auto_mode', true)->get();
        $now = Carbon::now()->format('H:i');

        foreach ($settings as $setting) {
            $device = Device::where('kandang_id', $setting->kandang_id)
                ->where('device_type', 'actuator')
                ->first();

            if (!$device) continue;

            if ($setting->timer_open && $setting->timer_open == $now) {
                if ($device->door_status != 'TERBUKA') {
                    $device->update(['door_status' => 'TERBUKA']);

                    ActivityLog::create([
                        'device_id' => $device->device_id,
                        'action' => 'OPEN_DOOR',
                        'source' => 'worker',
                        'notes' => 'Timer otomatis buka pintu'
                    ]);
                }
            }

            if ($setting->timer_close && $setting->timer_close == $now) {
                if ($device->door_status != 'TERTUTUP') {
                    $device->update(['door_status' => 'TERTUTUP']);

                    ActivityLog::create([
                        'device_id' => $device->device_id,
                        'action' => 'CLOSE_DOOR',
                        'source' => 'worker',
                        'notes' => 'Timer otomatis tutup pintu'
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Timer dijalankan'
        ]);
    }
}
