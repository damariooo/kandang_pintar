<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kandang;
use App\Models\DeviceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MonitoringController extends Controller
{
    public function index()
    {
        $kandangs = Kandang::with([
            'devices',
            'setting',
            'suhus' => function ($query) {
                $query->latest()->limit(1);
            }
        ])
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

        return view('public.monitoring.index', compact('kandangs'));
    }

    public function create()
    {
        return view('public.monitoring.create');
    }

    public function edit($id)
    {
        $kandang = Kandang::where('user_id', auth()->id())
            ->findOrFail($id);
        return view('public.monitoring.edit', compact('kandang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|unique:kandangs,code',
            'capacity'        => 'required|integer|min:1',
            'current_chicken' => 'required|integer|min:0|lte:capacity',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'current_chicken.lte' => 'Jumlah ayam tidak boleh melebihi kapasitas kandang!'
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('kandang', 'public');
        }

        $kandang = Kandang::create($data);

        $kandang->devices()->create([
            'device_id'         => $kandang->code . '-SERVO',
            'device_name'       => 'Servo Otomatis',
            'device_type'       => 'SERVO',
            'status'            => 'aktif',
            'connection_status' => 'offline',
            'door_status'       => 'TERTUTUP',
        ]);

        $kandang->devices()->create([
            'device_id'         => $kandang->code . '-LED',
            'device_name'       => 'Lampu Pemanas',
            'device_type'       => 'LED',
            'status'            => 'aktif',
            'connection_status' => 'offline',
            'light_status'      => 'MATI',
        ]);

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Kandang berhasil dibuat!');
    }

    public function edit($id)
    {
        $kandang = Kandang::where('user_id', auth()->id())
            ->findOrFail($id);

        return view('Public.monitoring.edit', compact('kandang'));
    }

    public function update(Request $request, $id)
    {
        $kandang = Kandang::where('user_id', auth()->id())
            ->findOrFail($id);

        $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|unique:kandangs,code,' . $id,
            'capacity'        => 'required|integer|min:1',
            'current_chicken' => 'required|integer|min:0|lte:capacity',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {

            if ($kandang->image) {
                Storage::disk('public')->delete($kandang->image);
            }

            $data['image'] = $request->file('image')
                ->store('kandang', 'public');

        } elseif ($request->remove_image == "1") {

            if ($kandang->image) {
                Storage::disk('public')->delete($kandang->image);
            }

            $data['image'] = null;
        }

        $kandang->update($data);

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Profil kandang berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $kandang = Kandang::where('user_id', auth()->id())
            ->findOrFail($id);

        if ($kandang->image) {
            Storage::disk('public')->delete($kandang->image);
        }

        $kandang->delete();

        return back()
            ->with('success', 'Kandang berhasil dihapus!');
    }

    public function updateSettings(Request $request, $kandang_id)
    {
        $kandang = Kandang::where('user_id', auth()->id())
            ->findOrFail($kandang_id);

        $request->validate([
            'timer_open'  => 'required',
            'timer_close' => 'required',
        ]);

        DeviceSetting::updateOrCreate(
            [
                'kandang_id' => $kandang->id
            ],
            [
                'timer_open'  => $request->timer_open,
                'timer_close' => $request->timer_close,
                'is_set'      => true,
            ]
        );

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Jadwal otomatis berhasil diaktifkan!');
    }
}