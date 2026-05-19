    @extends('layouts.public')
    @section('title', 'Monitoring Real-time')
    <style>
        @keyframes floatUp {
            0% {
                opacity: 1;
                transform: translate(-50%, 0);
            }

            100% {
                opacity: 0;
                transform: translate(-50%, -20px);
            }
        }

        .animate-plus {
            animation: floatUp 0.8s ease forwards;
        }
    </style>
    @section('content')
        <div class="mb-10 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Monitoring Kandang Real-time</h2>
                <p class="text-slate-500 mt-1 text-sm font-medium">Data sensor terbaru dan kontrol IoT per unit kandang.</p>
            </div>
            <a href="{{ route('monitoring.create') }}"
                class="bg-[#002855] hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg text-sm uppercase tracking-widest">
                <i class="fas fa-plus mr-2"></i> Tambah Kandang
            </a>
        </div>

        @if (session('success'))
            <div
                class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center shadow-sm">
                <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
                <span class="text-xs font-black uppercase tracking-wider">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @forelse($kandangs as $k)
                @php
                    $latestSensor = optional($k->suhus)->first();
                    $servo = $k->devices->where('component_type', 'servo')->first();
                    $lamp = $k->devices->where('component_type', 'led')->first();
                    $setting = $k->setting;
                    $temp = optional($k->suhus->first())->temperature ?? 0;
                    $humidity = optional($k->suhus->first())->humidity ?? 0;
                @endphp

                <div
                    class="bg-white rounded-[2rem] shadow-sm overflow-hidden border border-slate-100 group hover:shadow-xl transition-all duration-300">
                    <div class="h-44 relative overflow-hidden">
                        <img src="{{ $k->image ? asset('storage/' . $k->image) : 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&q=80&w=400' }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            alt="{{ $k->name }}">
                        <div class="absolute top-4 left-4 z-20">
                            <div
                                class="px-3 py-1.5 rounded-xl shadow-sm flex items-center gap-2 backdrop-blur
        {{ $temp < 25 ? 'bg-blue-500/90 text-white' : '' }}
        {{ $temp >= 25 && $temp <= 30 ? 'bg-emerald-500/90 text-white' : '' }}
        {{ $temp > 30 ? 'bg-red-500/90 text-white' : '' }}">

                                <i
                                    class="fas
            {{ $temp < 25 ? 'fa-snowflake' : '' }}
            {{ $temp >= 25 && $temp <= 30 ? 'fa-check-circle' : '' }}
            {{ $temp > 30 ? 'fa-fire' : '' }} text-xs">
                                </i>

                                <span id="temp-{{ $k->id }}" class="text-[11px] font-black">
                                    {{ $temp }}°C
                                </span>
                            </div>
                            <div
                                class="px-3 py-1.5 rounded-xl shadow-sm flex items-center gap-2 backdrop-blur bg-slate-800/90 text-white">
                                <i class="fas fa-tint text-xs text-sky-400"></i>
                                <span id="humidity-{{ $k->id }}" class="text-[11px] font-black">
                                    {{ $humidity }}%
                                </span>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4 flex gap-2 z-20">
                            <a href="{{ route('monitoring.edit', $k->id) }}"
                                class="w-8 h-8 bg-white/90 backdrop-blur text-slate-600 rounded-lg hover:bg-orange-500 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <form action="{{ route('monitoring.destroy', $k->id) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus kandang ini?')" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 bg-white/90 backdrop-blur text-slate-600 rounded-lg hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm cursor-pointer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>

                        <div
                            class="absolute inset-0 bg-gradient-to-t from-[#002855]/90 via-transparent flex flex-col justify-end p-6">
                            <h4 class="text-white font-black text-lg tracking-tight uppercase">{{ $k->name }}</h4>
                            <p class="text-orange-400 text-[10px] font-black uppercase tracking-[0.2em]">
                                {{ $k->code }}
                            </p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-3">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Kapasitas
                                    Kandang</p>

                                <div id="progress-{{ $k->id }}"
                                    class="h-2 rounded-full 
{{ $k->current_chicken >= $k->capacity ? 'bg-red-500' : 'bg-emerald-500' }}"
                                    style="width: {{ $k->capacity > 0 ? ($k->current_chicken / $k->capacity) * 100 : 0 }}%">
                                </div>
                                <div class="flex justify-between mt-1 text-[10px] font-bold text-slate-500">
                                    <div class="relative">
                                        <span id="chicken-count-{{ $k->id }}">
                                            {{ $k->current_chicken }}
                                        </span> ekor

                                        <span id="chicken-anim-{{ $k->id }}"
                                            class="absolute left-1/2 -translate-x-1/2 text-green-500 text-sm font-bold pointer-events-none">
                                        </span>
                                    </div>
                                    <span>{{ $k->capacity }} ekor</span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="p-4 rounded-2xl flex items-center justify-between {{ $servo && $servo->door_status == 'TERBUKA' ? 'bg-emerald-50 border border-emerald-100' : 'bg-[#002855] border border-[#002855]' }} transition-all">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-9 h-9 flex items-center justify-center rounded-xl {{ $servo && $servo->door_status == 'TERBUKA' ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300' }}">
                                    <i
                                        class="fas {{ $servo && $servo->door_status == 'TERBUKA' ? 'fa-door-open' : 'fa-door-closed' }} text-sm"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-[8px] uppercase font-black {{ $servo && $servo->door_status == 'TERBUKA' ? 'text-emerald-600' : 'text-slate-400' }}">
                                        Pintu Utama</p>
                                    <p
                                        class="font-black tracking-widest text-[11px] {{ $servo && $servo->door_status == 'TERBUKA' ? 'text-emerald-700' : 'text-white' }}">
                                        <span id="servo-status-{{ $k->id }}">
                                            {{ $servo->door_status ?? 'OFFLINE' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            @if ($servo)
                                <form
                                    action="{{ $servo->door_status == 'TERBUKA' ? route('servo.close') : route('servo.open') }}"
                                    method="POST" class="m-0">

                                    @csrf

                                    <input type="hidden" name="device_id" value="{{ $servo->device_id }}">

                                    <button type="submit"
                                        class="px-4 py-2 rounded-lg text-[9px] font-black uppercase tracking-tighter transition-all shadow-sm {{ $servo->door_status == 'TERBUKA' ? 'bg-emerald-600 text-white' : 'bg-orange-600 text-white hover:bg-orange-700' }}">
                                        {{ $servo->door_status == 'TERBUKA' ? 'Tutup' : 'Buka' }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div
                            class="p-4 rounded-2xl bg-white flex items-center justify-between border border-slate-100 {{ $lamp && $lamp->light_status == 'HIDUP' ? 'ring-1 ring-orange-500 bg-orange-50/30' : '' }}">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-9 h-9 flex items-center justify-center rounded-xl {{ $lamp && $lamp->light_status == 'HIDUP' ? 'bg-orange-500 text-white' : 'bg-slate-100 text-slate-400' }}">
                                    <i class="fas fa-lightbulb text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-[8px] uppercase font-black text-slate-400 tracking-widest">Pemanas</p>
                                    <p class="font-black text-slate-800 text-[11px] tracking-widest">
                                        <span id="lamp-status-{{ $k->id }}">
                                            {{ $lamp->light_status ?? 'OFFLINE' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            @if ($lamp)
                                <form action="{{ $lamp->light_status == 'HIDUP' ? route('lamp.off') : route('lamp.on') }}"
                                    method="POST" class="m-0">

                                    @csrf

                                    <input type="hidden" name="device_id" value="{{ $lamp->device_id }}">

                                    <button id="servo-btn-{{ $k->id }}" type="submit"
                                        class="relative inline-flex items-center h-5 w-10 cursor-pointer rounded-full transition-colors {{ $lamp->light_status == 'HIDUP' ? 'bg-orange-500' : 'bg-slate-300' }}">
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 {{ $lamp->light_status == 'HIDUP' ? 'translate-x-5' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                            @if ($setting && $setting->is_set)
                                <div>
                                    <h5 class="text-[9px] font-black text-emerald-600 uppercase tracking-[0.1em] mb-1">
                                        <i class="fas fa-calendar-check mr-1"></i> Jadwal Aktif
                                    </h5>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-extrabold text-slate-700">
                                            {{ \Carbon\Carbon::parse($setting->timer_open)->format('H:i') }}
                                        </span>
                                        <span class="text-slate-300 text-xs">-</span>
                                        <span class="text-xs font-extrabold text-slate-700">
                                            {{ \Carbon\Carbon::parse($setting->timer_close)->format('H:i') }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div>
                                    <h5 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        Penjadwalan
                                    </h5>
                                    <div class="flex items-center text-slate-400 gap-1">
                                        <i class="fas fa-clock text-[10px]"></i>
                                        <p class="text-[10px] font-bold uppercase italic">Default Mode</p>
                                    </div>
                                </div>
                            @endif

                            <button type="button" onclick="openModal('modal-{{ $k->id }}')"
                                class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 text-slate-400 hover:text-[#002855] hover:bg-orange-50 hover:border-orange-200 transition-all flex items-center justify-center cursor-pointer group">
                                <i class="fas fa-cog text-sm group-hover:rotate-90 transition-transform"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="modal-{{ $k->id }}"
                    class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-[#002855]/60 backdrop-blur-sm">
                    <div
                        class="bg-white rounded-[2rem] w-full max-w-sm shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="text-sm font-black text-[#002855] uppercase tracking-widest">Settings:
                                {{ $k->name }}</h3>
                            <button type="button" onclick="closeModal('modal-{{ $k->id }}')"
                                class="text-slate-400 hover:text-rose-500 transition-colors p-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form action="{{ route('settings.update', $k->id) }}" method="POST" class="p-6 space-y-5">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Buka
                                        Pintu</label>
                                    <input type="time" name="timer_open"
                                        value="{{ $setting->timer_open ?? '06:00' }}"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-orange-500 outline-none">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tutup
                                        Pintu</label>
                                    <input type="time" name="timer_close"
                                        value="{{ $setting->timer_close ?? '18:00' }}"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-orange-500 outline-none">
                                </div>
                            </div>
                            <button id="lamp-btn-{{ $k->id }}" type="submit"
                                class="w-full py-4 bg-[#002855] hover:bg-orange-600 text-white text-[10px] font-black uppercase rounded-xl transition-all tracking-[0.2em] shadow-lg">
                                Update Schedule
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white p-16 rounded-[2rem] border border-dashed border-slate-200 text-center">
                    <i class="fas fa-layer-group text-4xl text-slate-200 mb-4"></i>
                    <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">Tidak ada Kandang </p>
                </div>
            @endforelse
        </div>

        <script>
            function openModal(id) {
                const modal = document.getElementById(id);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeModal(id) {
                const modal = document.getElementById(id);
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = 'auto';
                }
            }

            window.onclick = function(event) {
                if (event.target.id.startsWith('modal-')) {
                    closeModal(event.target.id);
                }
            }
        </script>
        <script>
            let previousCounts = {};

            function initData() {
                fetch('/api/kandang')
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(k => {
                            previousCounts[k.id] = k.current_chicken;
                        });
                    });
            }

            function fetchData() {
                fetch('/api/kandang')
                    .then(res => res.json())
                    .then(data => {

                        data.forEach(k => {

                            updateChicken(k.id, k.current_chicken);

                            let servoStatus = document.getElementById(
                                'servo-status-' + k.id
                            );

                            if (servoStatus && k.servo_status) {
                                servoStatus.innerText = k.servo_status;
                            }

                            let lampStatus = document.getElementById(
                                'lamp-status-' + k.id
                            );

                            if (lampStatus && k.light_status) {
                                lampStatus.innerText = k.light_status;
                            }

                        });

                    });
            }

            function updateChicken(id, newValue) {
                let oldValue = previousCounts[id] ?? newValue;

                if (newValue > oldValue) {
                    showAnim(id, "+" + (newValue - oldValue), "text-green-500");
                } else if (newValue < oldValue) {
                    showAnim(id, "-" + (oldValue - newValue), "text-red-500");
                }

                previousCounts[id] = newValue;

                let countEl = document.getElementById('chicken-count-' + id);
                if (countEl) countEl.innerText = newValue;
            }

            function showAnim(id, text, colorClass) {
                let el = document.getElementById('chicken-anim-' + id);
                if (!el) return;

                el.innerText = text;
                el.className = "absolute left-1/2 -translate-x-1/2 font-bold " + colorClass + " animate-plus";

                setTimeout(() => {
                    el.innerText = '';
                }, 800);
            }

            initData();
            setInterval(fetchData, 2000);
        </script>
        <script>
            window.Echo.channel('kandang-monitor')
                .listen('.sensor.updated', (e) => {

                    console.log("Realtime:", e);

                    let tempEl = document.getElementById(
                        'temp-' + e.data.kandang_id
                    );

                    if (tempEl) {
                        tempEl.innerText = e.data.temperature + "°C";
                    }
                    let humidityEl = document.getElementById('humidity-' + e.data.kandang_id);
                    if (humidityEl && e.data.humidity) {
                        humidityEl.innerText = e.data.humidity + "%";
                    }
                });
        </script>
    @endsection
