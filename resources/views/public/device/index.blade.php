@extends('layouts.public')
@section('title', 'Daftar Hardware')

@section('content')
    @if (session('success'))
        <div id="alert-success"
            class="mb-6 mx-auto w-full bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm animate-fade-in-down">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-lg"></i>
                <span class="text-sm font-bold uppercase tracking-wider">{{ session('success') }}</span>
            </div>
            <button onclick="document.getElementById('alert-success').remove()"
                class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="w-full mb-8 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Device Manajemen</h2>
            <p class="text-slate-500 text-sm mt-1">Daftar perangkat IoT dan komponen yang terhubung ke sistem.</p>
        </div>

        <a href="{{ route('devices.create') }}"
            class="bg-[#002855] hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg text-sm uppercase tracking-widest">
            <i class="fas fa-plus mr-2"></i> Tambah Device
        </a>
    </div>

    <div class="w-full bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                Daftar Perangkat & Komponen
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-slate-50">
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Info Device</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tipe & Komponen</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">ID & Barcode</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Lokasi / Kandang</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kondisi & Status</th>
                        <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">
                    @forelse ($devices as $device)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center space-x-4">
                                    <div class="relative w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 flex items-center justify-center border">
                                        @if ($device->profile_image)
                                            <img src="{{ asset('storage/' . $device->profile_image) }}" class="w-full h-full object-cover">
                                        @else
                                            {{-- Icon otomatis berubah berdasarkan device_type --}}
                                            @if($device->device_type == 'gateway')
                                                <i class="fas fa-server text-2xl text-slate-400"></i>
                                            @elseif($device->device_type == 'sensor')
                                                <i class="fas fa-broadcast-tower text-2xl text-slate-400"></i>
                                            @else
                                                <i class="fas fa-bolt text-2xl text-slate-400"></i>
                                            @endif
                                        @endif
                                        <span class="absolute bottom-1 right-1 w-3 h-3 rounded-full border-2 border-white
                                            {{ $device->connection_status == 'online' ? 'bg-emerald-500' : 'bg-slate-400' }}">
                                        </span>
                                    </div>

                                    <div>
                                        <p class="text-sm font-bold text-slate-800">
                                            {{ $device->device_name ?? 'Unnamed Device' }}
                                        </p>

                                        <div class="flex items-center gap-2 mt-0.5">
                                            @php
                                                $signal = $device->signal_strength ?? 0;
                                                $signalColor = $device->connection_status == 'offline' ? 'text-slate-300' : 
                                                    ($signal > 70 ? 'text-emerald-500' : ($signal > 30 ? 'text-amber-500' : 'text-rose-500'));
                                            @endphp
                                            <i class="fas fa-wifi text-[10px] {{ $signalColor }}"></i>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">
                                                {{ $device->connection_status == 'online' ? $signal . '% Sinyal' : 'Offline' }}
                                            </span>
                                        </div>

                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            Aktif: {{ $device->installation_date ? \Carbon\Carbon::parse($device->installation_date)->format('d M Y') : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1">
                                    <span class="px-2 py-0.5 w-fit text-[10px] font-bold rounded-md uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $device->device_type ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-500">
                                        Chipset: <span class="text-slate-700 font-bold uppercase">{{ $device->component_type ?? '-' }}</span>
                                    </span>
                                </div>
                            </td>

                            <td class="px-8 py-5">
                                <div class="bg-white p-2 border rounded-lg shadow-sm w-fit flex flex-col items-center">
                                    <div class="barcode-container">
                                        {!! DNS1D::getBarcodeHTML($device->device_id, 'C128', 1.1, 28) !!}
                                    </div>
                                    <p class="text-[9px] font-bold mt-2 text-slate-500 tracking-widest uppercase">
                                        {{ $device->device_id }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-8 py-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-600">
                                        {{ $device->kandang->name ?? 'Unassigned' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider">
                                        Kode: {{ $device->kandang->code ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1.5">
                                    {{-- Health Status Badge --}}
                                    @php
                                        $healthConfig = [
                                            'EXCELLENT' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
                                            'DEGRADED' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
                                            'CRITICAL' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-200'],
                                            'MAINTENANCE' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200'],
                                        ];
                                        $h = $healthConfig[$device->health_status] ?? $healthConfig['MAINTENANCE'];
                                        
                                        $stateColor = $device->device_state == 'error' ? 'text-rose-500' : 
                                            ($device->device_state == 'maintenance' ? 'text-amber-500' : 'text-emerald-500');
                                    @endphp
                                    
                                    <div class="flex items-center gap-1">
                                        <span class="px-2.5 py-0.5 text-[9px] font-black rounded-lg border uppercase {{ $h['bg'] }} {{ $h['text'] }} {{ $h['border'] }}">
                                            {{ $device->health_status }}
                                        </span>
                                        {{-- Device State (Active/Idle/Error) --}}
                                        <span class="text-[10px] font-bold capitalize {{ $stateColor }}">
                                            ({{ $device->device_state }})
                                        </span>
                                    </div>

                                    {{-- Kondisi Spesifik Sensor/Actuator Berdasarkan Data Baru --}}
                                    @if($device->component_type == 'servo' || $device->door_status)
                                        <span class="text-[10px] text-slate-500">
                                            <i class="fas fa-door-open mr-1"></i> Pintu: <strong class="{{ $device->door_status == 'TERBUKA' ? 'text-amber-600' : 'text-slate-700' }}">{{ $device->door_status ?? 'N/A' }}</strong>
                                        </span>
                                    @endif

                                    @if(in_array($device->component_type, ['led', 'buzzer']) || $device->light_status)
                                        <span class="text-[10px] text-slate-500">
                                            <i class="fas fa-lightbulb mr-1"></i> Lampu: <strong class="{{ $device->light_status == 'HIDUP' ? 'text-amber-500' : 'text-slate-400' }}">{{ $device->light_status ?? 'N/A' }}</strong>
                                        </span>
                                    @endif
                                    
                                    <span class="text-[9px] text-slate-400">
                                        Last Seen: {{ $device->last_seen ? \Carbon\Carbon::parse($device->last_seen)->diffForHumans() : 'Belum aktif' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-8 py-5 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('devices.edit', $device->id) }}"
                                        class="w-8 h-8 flex items-center justify-center hover:text-orange-500 transition-colors">
                                        <i class="fas fa-edit text-slate-400 hover:text-orange-500"></i>
                                    </a>

                                    <form action="{{ route('devices.destroy', $device->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus perangkat ini?')">
                                        @csrf @method('DELETE')
                                        <button class="w-8 h-8 flex items-center justify-center hover:text-rose-500 transition-colors">
                                            <i class="fas fa-trash text-slate-400 hover:text-rose-500"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-microchip text-slate-200 text-2xl"></i>
                                    </div>
                                    <p class="text-slate-400 font-bold text-sm uppercase tracking-widest">
                                        Device tidak ditemukan
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection