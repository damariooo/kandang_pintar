@extends('layouts.public')
@section('title', 'Log Aktivitas Sistem')

@section('content')
    <div x-data="{ showFilter: false }">
        <div class="w-full mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Log Aktivitas</h2>
                <p class="text-slate-500 text-sm mt-1">Rekam jejak real-time dari sensor, AI detection, dan sistem otomatis.
                </p>
            </div>
            <button @click="showFilter = !showFilter"
                class="bg-[#002855] hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg text-sm uppercase tracking-widest flex items-center">
                <i class="fas fa-filter mr-2"></i>
                <span x-text="showFilter ? 'Tutup Filter' : 'Filter Data'"></span>
            </button>
        </div>

        <div x-show="showFilter" x-transition x-cloak
            class="w-full bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm mb-6">
            <form action="{{ route('activity_log') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cari Kata
                        Kunci</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari aksi atau deskripsi..."
                        class="w-full bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label
                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kategori</label>
                    <select name="category"
                        class="w-full bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-orange-500">
                        <option value="">Semua Kategori</option>
                        <option value="system" {{ request('category') == 'system' ? 'selected' : '' }}>System & Device
                        </option>
                        <option value="detection" {{ request('category') == 'detection' ? 'selected' : '' }}>AI Detection
                        </option>
                        <option value="movement" {{ request('category') == 'movement' ? 'selected' : '' }}>Pergerakan Ayam
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status</label>
                    <select name="status"
                        class="w-full bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-orange-500">
                        <option value="">Semua Status</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="warning" {{ request('status') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="danger" {{ request('status') == 'danger' ? 'selected' : '' }}>Danger</option>
                        <option value="info" {{ request('status') == 'info' ? 'selected' : '' }}>Info</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 bg-orange-500 text-white py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-orange-600 transition-all">
                        Terapkan
                    </button>
                    <a href="{{ route('activity_log') }}"
                        class="px-4 py-3 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 transition-all">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="w-full bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Riwayat Aktivitas Terintegrasi
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-slate-50">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Waktu
                            </th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kategori
                            </th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Aksi</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Deskripsi
                            </th>
                            <th
                                class="px-8 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-slate-700">{{ $log->created_at->format('H:i:s') }}</span>
                                        <span
                                            class="text-[10px] text-slate-400 font-medium">{{ $log->created_at->format('d M Y') }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    @php
                                        $catColors = [
                                            'system' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                            'detection' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'movement' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        ];
                                        $colorClass =
                                            $catColors[$log->category] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                                    @endphp
                                    <span
                                        class="px-3 py-1 text-[9px] font-black rounded-lg border uppercase tracking-wider {{ $colorClass }}">
                                        {{ $log->category }}
                                    </span>
                                </td>

                                <td class="px-6 py-5">
                                    <span class="text-sm font-bold text-slate-800 uppercase tracking-tight">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <td class="px-6 py-5">
                                    <p class="text-sm text-slate-500 max-w-sm leading-relaxed">
                                        {{ $log->description }}
                                    </p>
                                </td>

                                <td class="px-8 py-5 text-right">
                                    @php
                                        $statusConfig = [
                                            'success' => ['text' => 'text-emerald-600', 'bg' => 'bg-emerald-500'],
                                            'danger' => ['text' => 'text-rose-600', 'bg' => 'bg-rose-500'],
                                            'warning' => ['text' => 'text-amber-600', 'bg' => 'bg-amber-500'],
                                            'info' => ['text' => 'text-blue-600', 'bg' => 'bg-blue-500'],
                                        ];
                                        $st = $statusConfig[$log->status] ?? [
                                            'text' => 'text-slate-400',
                                            'bg' => 'bg-slate-400',
                                        ];
                                    @endphp
                                    <div class="flex items-center justify-end gap-3">
                                        <span class="text-[10px] font-black uppercase tracking-widest {{ $st['text'] }}">
                                            {{ $log->status }}
                                        </span>
                                        <div class="relative flex h-2 w-2">
                                            @if ($log->status == 'danger' || $log->status == 'warning')
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $st['bg'] }} opacity-20"></span>
                                            @endif
                                            <span
                                                class="relative inline-flex rounded-full h-2 w-2 {{ $st['bg'] }}"></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-history text-slate-200 text-4xl mb-4"></i>
                                        <p class="text-slate-400 font-bold text-sm uppercase tracking-widest">Tidak ada
                                            aktivitas ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="px-8 py-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
