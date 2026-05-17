@extends('layouts.public')
@section('title', 'Dashboard Overview')

@section('content')
    <div class="w-full mb-10 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mb-1">Dashboard Overview</h2>
            <p class="text-slate-500 text-sm font-medium">Monitoring sistem dan kondisi kandang secara real-time.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div
            class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 flex items-center space-x-4 hover:border-blue-200 transition-all group">
            <div
                class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                <i class="fas fa-thermometer-half text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Rerata Suhu</p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($avgTemperature, 1) }}°C</h2>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 flex items-center space-x-4 hover:border-emerald-200 transition-all group">
            <div
                class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                <i class="fas fa-microchip text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Device Online</p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">
                    {{ $onlineDevicesCount }}<span
                        class="text-sm text-slate-300 font-bold ml-1">/{{ $totalDevicesCount }}</span>
                </h2>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 flex items-center space-x-4 hover:border-indigo-200 transition-all group">
            <div
                class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                <i class="fas fa-home text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kandang Aktif</p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">
                    {{ $activeKandangCount }}<span
                        class="text-sm text-slate-300 font-bold ml-1">/{{ $totalKandangCount }}</span>
                </h2>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 flex items-center space-x-4 hover:border-orange-200 transition-all group">
            <div
                class="w-14 h-14 {{ $anyDoorOpen ? 'bg-orange-50 text-orange-500' : 'bg-slate-900 text-white' }} rounded-2xl flex items-center justify-center transition-all group-hover:scale-110">
                <i class="fas {{ $anyDoorOpen ? 'fa-door-open' : 'fa-shield-alt' }} text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pintu Terbuka</p>
                <h2 class="text-2xl font-black {{ $anyDoorOpen ? 'text-orange-500' : 'text-slate-800' }} tracking-tight">
                    {{ $openDoorsCount }}<span
                        class="text-sm text-slate-300 font-bold ml-1">/{{ $totalServoCount }}</span>
                </h2>
            </div>
        </div>
    </div>

    <div class="w-full mb-12">
        <div class="flex justify-between items-end mb-6">
            <div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Live Detection Feed</h3>
                <p class="text-slate-500 text-sm font-medium">Hasil tangkapan kamera dan proses deteksi AI YOLO.</p>
            </div>
            <a href="{{ route('activity_log') }}"
                class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 bg-blue-50 px-4 py-2 rounded-lg hover:bg-orange-500 hover:text-white transition-all">
                Lihat Semua History <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($latestDetections as $deteksi)
                <div
                    class="group bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl transition-all hover:-translate-y-2">
                    <div class="relative aspect-square overflow-hidden bg-slate-100">
                        @if ($deteksi->image)
                            <img src="{{ asset('storage/' . $deteksi->image) }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-slate-300">
                                <i class="fas fa-image text-4xl mb-2"></i>
                                <span class="text-[10px] font-bold uppercase tracking-widest">No Capture</span>
                            </div>
                        @endif

                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-xl shadow-sm">
                            <p class="text-[9px] font-black text-blue-600 uppercase tracking-tighter">
                                Confidence: {{ number_format(($deteksi->confidence ?? 0) * 100, 0) }}%
                            </p>
                        </div>

                        <div class="absolute bottom-4 right-4">
                            @if ($deteksi->is_valid)
                                <div
                                    class="w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                                    <i class="fas fa-check text-xs"></i>
                                </div>
                            @else
                                <div
                                    class="w-8 h-8 bg-rose-500 text-white rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                                    <i class="fas fa-exclamation text-xs"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="mb-4">
                            @if ($deteksi->objects)
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach ($deteksi->objects as $obj)
                                        <span
                                            class="text-[10px] font-black text-orange-500 uppercase tracking-widest bg-orange-50 px-2 py-1 rounded-md">
                                            {{ $obj['label'] }} ({{ number_format($obj['confidence'] * 100, 0) }}%)
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400">tidak_ada</span>
                            @endif
                            <h4 class="mt-2 font-bold text-slate-800 leading-tight">
                                {{ $deteksi->kandang->name ?? 'Kandang Tidak Diketahui' }}
                            </h4>
                            <p class="text-[11px] text-slate-400 font-medium mt-1">
                                Device: {{ $deteksi->device->device_name ?? $deteksi->device_id }}
                            </p>
                        </div>

                        <div class="flex items-center pt-4 border-t border-slate-50">
                            <i class="far fa-clock text-slate-300 mr-2 text-xs"></i>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                {{ $deteksi->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-[3rem] p-16 text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="fas fa-camera-retro text-slate-200 text-3xl"></i>
                    </div>
                    <h5 class="text-slate-800 font-bold tracking-tight">Belum Ada Deteksi AI</h5>
                    <p class="text-slate-400 text-sm mt-1">Data hasil capture YOLO akan muncul secara otomatis di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
            class="bg-[#002855] p-8 rounded-[2.5rem] text-white flex items-center justify-between group overflow-hidden relative shadow-lg shadow-blue-900/20">

            <i class="fas fa-tv absolute -right-4 -bottom-4 text-white/5 text-8xl -rotate-12 pointer-events-none"></i>

            <div class="relative z-10">
                <h4 class="text-lg font-bold mb-1">Pusat Monitoring</h4>
                <p class="text-blue-200/70 text-xs">Lihat kondisi seluruh kandang.</p>
            </div>

            <a href="{{ route('monitoring.index') }}"
                class="relative z-50 cursor-pointer bg-white text-[#002855] px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-orange-500 hover:text-white transition-all shadow-xl">
                Buka
            </a>
        </div>

        <div
            class="bg-slate-900 p-8 rounded-[2.5rem] text-white flex items-center justify-between group overflow-hidden relative shadow-lg shadow-slate-900/20">

            <i class="fas fa-history absolute -right-4 -bottom-4 text-white/5 text-8xl -rotate-12 pointer-events-none"></i>

            <div class="relative z-10">
                <h4 class="text-lg font-bold mb-1">Log Aktivitas</h4>
                <p class="text-slate-400 text-xs">Riwayat perubahan sistem.</p>
            </div>

            <a href="{{ route('activity_log') }}"
                class="relative z-50 cursor-pointer bg-white/10 border border-white/20 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-white hover:text-slate-900 transition-all">
                Riwayat
            </a>

        </div>
    </div>
@endsection
