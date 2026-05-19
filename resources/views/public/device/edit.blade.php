@extends('layouts.public')
@section('title', 'Edit Device')

@section('content')
    <style>
        input[type="date"]::-webkit-inner-spin-button,
        input[type="date"]::-webkit-calendar-picker-indicator {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            cursor: pointer;
            opacity: 0;
        }
    </style>

    <div class="w-full mb-6 flex items-center justify-between">
        <div class="flex items-center">
            <a href="{{ route('devices.index') }}"
                class="mr-4 bg-white text-slate-400 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm border border-slate-100 hover:text-orange-500 transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Device</h2>
        </div>
    </div>

    <div class="w-full bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50 bg-slate-50/30">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]">Informasi Device</h3>
        </div>

        @if ($errors->any())
            <div class="m-6 bg-red-50 text-red-600 p-4 rounded-xl border border-red-100">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li class="text-xs font-bold uppercase tracking-tight">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('devices.update', $device->id) }}" method="POST" enctype="multipart/form-data" class="p-8">
            @csrf
            @method('PUT')

            <input type="hidden" name="remove_image" id="remove-image-flag" value="0">

            <div class="flex flex-col lg:flex-row gap-12">

                <div class="w-full lg:w-72 flex flex-col">
                    <div class="relative group">
                        <div class="w-full aspect-square rounded-3xl bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden relative transition-all group-hover:border-orange-300">
                            
                            <img id="preview-img"
                                src="{{ $device->profile_image ? asset('storage/' . $device->profile_image) : 'https://ui-avatars.com/api/?name=Device&background=F1F5F9&color=CBD5E1' }}"
                                class="w-full h-full object-cover">

                            <div class="absolute inset-0 bg-slate-900/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                                <label for="profile_image" class="cursor-pointer bg-white text-slate-900 p-3 rounded-full shadow-xl hover:scale-110 transition-transform">
                                    <i class="fas fa-camera"></i>
                                </label>
                            </div>

                            <button type="button" id="btn-remove-image"
                                class="absolute top-3 right-3 bg-red-500 text-white w-9 h-9 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform {{ $device->profile_image ? '' : 'hidden' }}">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                        
                        <input type="file" name="profile_image" id="profile_image" class="hidden" accept="image/*">
                        <p class="text-center text-[10px] text-slate-400 mt-3 font-bold uppercase tracking-tighter">
                            *Klik ikon kamera untuk mengganti foto
                        </p>
                    </div>

                    <div class="mt-6 p-4 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col items-center justify-center">
                        <div class="bg-white p-2 rounded-lg shadow-sm border border-white">
                            {!! DNS1D::getBarcodeHTML($device->device_id, 'C128', 1.2, 33) !!}
                        </div>
                        <p class="text-[9px] font-bold mt-2 text-slate-400 tracking-[0.2em] uppercase">
                            {{ $device->device_id }}
                        </p>
                    </div>
                </div>

                <div class="flex-1 flex flex-col justify-between">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">

                        <div class="space-y-1 border-b border-slate-200 pb-2 focus-within:border-orange-500 transition-all">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nama Device</label>
                            <input type="text" name="device_name" value="{{ old('device_name', $device->device_name) }}"
                                class="w-full py-1 bg-transparent focus:outline-none text-lg text-slate-700 font-semibold uppercase"
                                placeholder="Nama Perangkat" required>
                        </div>

                        <div class="space-y-1 border-b border-slate-200 pb-2 bg-slate-50/50 px-2 rounded-t-lg transition-all">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Device ID (Locked)</label>
                            <div class="flex items-center justify-between">
                                <input type="text" value="{{ $device->device_id }}"
                                    class="w-full py-1 bg-transparent focus:outline-none text-lg text-slate-400 font-semibold uppercase cursor-not-allowed"
                                    readonly>
                                <i class="fas fa-lock text-slate-300"></i>
                            </div>
                        </div>

                        <div class="space-y-1 border-b border-slate-200 pb-2 focus-within:border-orange-500 transition-all">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tipe Device</label>
                            <select name="device_type" class="w-full py-1 bg-transparent focus:outline-none text-lg text-slate-700 font-semibold cursor-pointer">
                                <option value="">Pilih Tipe Perangkat</option>
                                <option value="gateway" {{ old('device_type', $device->device_type) == 'gateway' ? 'selected' : '' }}>Gateway</option>
                                <option value="sensor" {{ old('device_type', $device->device_type) == 'sensor' ? 'selected' : '' }}>Sensor</option>
                                <option value="actuator" {{ old('device_type', $device->device_type) == 'actuator' ? 'selected' : '' }}>Actuator</option>
                            </select>
                        </div>

                        <div class="space-y-1 border-b border-slate-200 pb-2 focus-within:border-orange-500 transition-all">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tipe Komponen</label>
                            <select name="component_type" class="w-full py-1 bg-transparent focus:outline-none text-lg text-slate-700 font-semibold cursor-pointer">
                                <option value="">Pilih Komponen</option>
                                <option value="esp32" {{ old('component_type', $device->component_type) == 'esp32' ? 'selected' : '' }}>ESP32</option>
                                <option value="dht22" {{ old('component_type', $device->component_type) == 'dht22' ? 'selected' : '' }}>DHT22 (Suhu/Kelembaban)</option>
                                <option value="ultrasonic" {{ old('component_type', $device->component_type) == 'ultrasonic' ? 'selected' : '' }}>Ultrasonic</option>
                                <option value="servo" {{ old('component_type', $device->component_type) == 'servo' ? 'selected' : '' }}>Servo</option>
                                <option value="led" {{ old('component_type', $device->component_type) == 'led' ? 'selected' : '' }}>LED</option>
                                <option value="buzzer" {{ old('component_type', $device->component_type) == 'buzzer' ? 'selected' : '' }}>Buzzer</option>
                            </select>
                        </div>

                        <div class="space-y-1 border-b border-slate-200 pb-2 focus-within:border-orange-500 transition-all md:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Lokasi (Kandang)</label>
                            <select name="kandang_id"
                                class="w-full py-1 bg-transparent focus:outline-none text-lg text-slate-700 font-semibold appearance-none cursor-pointer"
                                required>
                                @foreach ($kandangs as $kandang)
                                    <option value="{{ $kandang->id }}" {{ old('kandang_id', $device->kandang_id) == $kandang->id ? 'selected' : '' }}>
                                        {{ $kandang->name }} ({{ $kandang->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1 border-b border-slate-200 pb-2 focus-within:border-orange-500 transition-all relative">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal Instalasi</label>
                            <div class="flex items-center justify-between">
                                <input type="date" name="installation_date"
                                    value="{{ old('installation_date', $device->installation_date ? \Carbon\Carbon::parse($device->installation_date)->format('Y-m-d') : '') }}"
                                    class="w-full py-1 bg-transparent focus:outline-none text-lg text-slate-700 font-semibold cursor-pointer z-10">
                                <i class="far fa-calendar-alt text-slate-400 absolute right-0 bottom-3"></i>
                            </div>
                        </div>

                        <div class="space-y-1" x-data="{ active: {{ $device->status == 'aktif' ? 'true' : 'false' }} }">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aktifasi Status</label>
                            <div class="flex items-center py-2">
                                <input type="hidden" name="status" :value="active ? 'aktif' : 'non-aktif'">
                                <button type="button" @click="active = !active"
                                    :class="active ? 'bg-slate-900' : 'bg-slate-200'"
                                    class="w-10 h-5 rounded-full relative p-1 transition-colors duration-200">
                                    <div :class="active ? 'translate-x-5' : 'translate-x-0'"
                                        class="w-3 h-3 bg-white rounded-full shadow-sm transform transition-transform duration-200">
                                    </div>
                                </button>
                                <span class="ml-3 text-[10px] font-bold uppercase tracking-widest"
                                    :class="active ? 'text-emerald-600' : 'text-slate-400'"
                                    x-text="active ? 'Aktif' : 'Non-Aktif'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <a href="{{ route('devices.index') }}"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold py-4 px-8 rounded-2xl transition text-xs uppercase tracking-widest flex items-center">
                            Batal
                        </a>
                        <button type="submit"
                            class="bg-[#002855] hover:bg-orange-600 text-white font-bold py-4 px-12 rounded-2xl transition shadow-lg text-sm uppercase tracking-widest">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const imageInput = document.getElementById('profile_image');
        const previewImg = document.getElementById('preview-img');
        const btnRemove = document.getElementById('btn-remove-image');
        const removeFlag = document.getElementById('remove-image-flag');
        const defaultImg = 'https://ui-avatars.com/api/?name=Device&background=F1F5F9&color=CBD5E1';

        imageInput.onchange = evt => {
            const [file] = imageInput.files;
            if (file) {
                previewImg.src = URL.createObjectURL(file);
                btnRemove.classList.remove('hidden');
                removeFlag.value = "0";
            }
        }

        btnRemove.onclick = () => {
            previewImg.src = defaultImg;
            btnRemove.classList.add('hidden');
            imageInput.value = "";
            removeFlag.value = "1";
        }
    </script>
@endsection