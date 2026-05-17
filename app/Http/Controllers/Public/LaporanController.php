<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Suhu;
use App\Models\Ayam;
use App\Models\Kandang;
use App\Models\Deteksi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $daftarKandang = Kandang::where('user_id', $userId)
            ->latest()
            ->get();

        if ($daftarKandang->isEmpty()) {

            return view('Public.laporan.index', [
                'daftarKandang' => collect(),
                'kandangId' => null,
                'labels' => [],
                'chicken_data' => [],
                'temp_data' => [],
                'detection_data' => [],
                'current_chicken' => 0,
                'capacity' => 0,
                'last_temp' => 0,
                'avg_temp' => 0,
                'total_detection' => 0,
                'total_masuk' => 0,
                'total_keluar' => 0,
                'online_devices' => 0,
                'manual_count' => 0,
                'auto_count' => 0,
                'health_stats' => [
                    'excellent' => 0,
                    'degraded' => 0,
                    'critical' => 0,
                    'maintenance' => 0,
                ],
            ]);
        }

        $kandangId = $request->get(
            'kandang_id',
            $daftarKandang->first()->id
        );

        $kandang = Kandang::with('devices')
            ->where('user_id', $userId)
            ->where('id', $kandangId)
            ->firstOrFail();

        $current_chicken = $kandang->current_chicken ?? 0;
        $capacity = $kandang->capacity ?? 0;

        $last_temp = Suhu::where('kandang_id', $kandang->id)
            ->latest()
            ->value('temperature') ?? 0;

        $avg_temp = round(
            Suhu::where('kandang_id', $kandang->id)
                ->avg('temperature') ?? 0,
            1
        );

        $total_detection = Deteksi::where('kandang_id', $kandang->id)
            ->count();

        $total_masuk = Ayam::where('kandang_id', $kandang->id)
            ->where('direction', 'IN')
            ->count();

        $total_keluar = Ayam::where('kandang_id', $kandang->id)
            ->where('direction', 'OUT')
            ->count();

        $online_devices = Device::where('kandang_id', $kandang->id)
            ->where('connection_status', 'online')
            ->count();

        $labels = [];
        $chicken_data = [];
        $temp_data = [];
        $detection_data = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = Carbon::today()->subDays($i);

            $labels[] = $date->format('d M');

            $masuk = Ayam::where('kandang_id', $kandang->id)
                ->whereDate('created_at', '<=', $date)
                ->where('direction', 'IN')
                ->count();

            $keluar = Ayam::where('kandang_id', $kandang->id)
                ->whereDate('created_at', '<=', $date)
                ->where('direction', 'OUT')
                ->count();

            $chicken_data[] = max($masuk - $keluar, 0);

            $temp_data[] = round(
                Suhu::where('kandang_id', $kandang->id)
                    ->whereDate('created_at', $date)
                    ->avg('temperature') ?? 0,
                1
            );

            $detection_data[] = Deteksi::where('kandang_id', $kandang->id)
                ->whereDate('created_at', $date)
                ->count();
        }

        $manual_count = Ayam::where('kandang_id', $kandang->id)
            ->where('source', 'MANUAL')
            ->count();

        $manual_count = Ayam::where('kandang_id', $kandang->id)
            ->where('source', 'MANUAL')
            ->count();

        $auto_count = Ayam::where('kandang_id', $kandang->id)
            ->where('source', '!=', 'MANUAL')
            ->count();

        $health_stats = [
            'excellent' => Device::where('kandang_id', $kandang->id)
                ->where('health_status', 'EXCELLENT')
                ->count(),

            'degraded' => Device::where('kandang_id', $kandang->id)
                ->where('health_status', 'DEGRADED')
                ->count(),

            'critical' => Device::where('kandang_id', $kandang->id)
                ->where('health_status', 'CRITICAL')
                ->count(),

            'maintenance' => Device::where('kandang_id', $kandang->id)
                ->where('health_status', 'MAINTENANCE')
                ->count(),
        ];

        return view('Public.laporan.index', compact(
            'daftarKandang',
            'kandangId',
            'labels',
            'chicken_data',
            'temp_data',
            'detection_data',
            'current_chicken',
            'capacity',
            'last_temp',
            'avg_temp',
            'total_detection',
            'total_masuk',
            'total_keluar',
            'online_devices',
            'manual_count',
            'auto_count',
            'health_stats'
        ));
    }

    public function export(Request $request)
    {
        $userId = auth()->id();

        $format = $request->format;

        $kandang = Kandang::where('user_id', $userId)
            ->where('id', $request->kandang_id)
            ->firstOrFail();

        $query = Ayam::where('kandang_id', $kandang->id);

        if ($request->start_date && $request->end_date) {

            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $data = $query->latest()->get();

        if ($format == 'csv') {

            $filename = 'laporan_' . $kandang->code . '.csv';

            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
            ];

            $callback = function () use ($data) {

                $file = fopen('php://output', 'w');

                fputcsv($file, [
                    'ID',
                    'Direction',
                    'Source',
                    'Tanggal'
                ]);

                foreach ($data as $row) {

                    fputcsv($file, [
                        $row->id,
                        $row->direction,
                        $row->source,
                        $row->created_at
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($format == 'pdf') {

            $pdf = Pdf::loadView('Public.laporan.export_pdf', [
                'data' => $data,
                'kandang' => $kandang
            ]);

            return $pdf->download(
                'laporan_' . $kandang->code . '.pdf'
            );
        }

        return back();
    }
}
